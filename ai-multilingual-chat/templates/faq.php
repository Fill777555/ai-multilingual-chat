<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin FAQ page — DB-backed version.
 * Table: {$wpdb->prefix}ai_chat_faq
 *
 * Исправления:
 * - Исправлено имя таблицы на ai_chat_faq (как в схеме плагина).
 * - Исправлен slug меню на 'ai-chat-faq' для корректных редиректов.
 * - Убраны вызовы $wpdb->prepare без плейсхолдеров.
 * - Добавлена проверка прав current_user_can('manage_options').
 * - Добавлена проверка и админ-уведомление при ошибках $wpdb.
 * - Безопасные редиректы, короткие сообщения об ошибках.
 */

/* ======= Таблица / миграция ======= */
function aic_get_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'ai_chat_faq';  // Исправлено: ai_chat_faq вместо aic_faqs
}

function aic_ensure_table_exists() {
    global $wpdb;
    $table = aic_get_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        question text NOT NULL,
        answer longtext NOT NULL,
        keywords text NOT NULL,
        language varchar(10) NOT NULL DEFAULT 'ru',
        is_active tinyint(1) NOT NULL DEFAULT 1,
        created_at datetime NOT NULL,
        updated_at datetime DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    if (!function_exists('dbDelta')) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }
    dbDelta($sql);
}

/* Migrate from option 'aic_faqs' if exists and table empty */
function aic_maybe_migrate_options_to_table() {
    global $wpdb;
    $table = aic_get_table_name();

    // Ensure table exists first
    aic_ensure_table_exists();

    // Получаем количество записей безопасно (без prepare)
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

    if ($count === 0) {
        $opt = get_option('aic_faqs', array());
        if (!empty($opt) && is_array($opt)) {
            foreach ($opt as $item) {
                $faq = is_object($item) ? $item : (object)$item;
                $wpdb->insert(
                    $table,
                    array(
                        'question'   => isset($faq->question) ? $faq->question : '',
                        'answer'     => isset($faq->answer) ? $faq->answer : '',
                        'keywords'   => isset($faq->keywords) ? $faq->keywords : '',
                        'language'   => isset($faq->language) ? $faq->language : 'ru',
                        'is_active'  => !empty($faq->is_active) ? 1 : 0,
                        'created_at' => isset($faq->created_at) ? $faq->created_at : current_time('mysql'),
                    ),
                    array('%s','%s','%s','%s','%d','%s')
                );
                if ($wpdb->last_error) {
                    // Запись ошибки в лог для отладки
                    error_log('aic_migrate insert error: ' . $wpdb->last_error);
                }
            }
        }
    }
}

/* ======= CRUD через $wpdb ======= */
function aic_get_faqs() {
    global $wpdb;
    $table = aic_get_table_name();
    aic_maybe_migrate_options_to_table();
    // Возвращаем результаты как объекты
    $results = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");
    if ($wpdb->last_error) {
        // Логируем ошибку (и сохраняем временно в transient для отображения пользователю)
        error_log('aic_get_faqs SQL error: ' . $wpdb->last_error);
        set_transient('aic_last_db_error', $wpdb->last_error, 30);
        return array();
    }
    return $results;
}

function aic_insert_faq($data = array()) {
    global $wpdb;
    $table = aic_get_table_name();
    $inserted = $wpdb->insert(
        $table,
        array(
            'question'   => $data['question'],
            'answer'     => $data['answer'],
            'keywords'   => $data['keywords'],
            'language'   => $data['language'],
            'is_active'  => !empty($data['is_active']) ? 1 : 0,
            'created_at' => current_time('mysql'),
        ),
        array('%s','%s','%s','%s','%d','%s')
    );
    if ($wpdb->last_error) {
        error_log('aic_insert_faq error: ' . $wpdb->last_error);
        set_transient('aic_last_db_error', $wpdb->last_error, 30);
    }
    return $inserted !== false;
}

function aic_delete_faq_by_id($id) {
    global $wpdb;
    $table = aic_get_table_name();
    $res = $wpdb->delete($table, array('id' => intval($id)), array('%d'));
    if ($wpdb->last_error) {
        error_log('aic_delete_faq_by_id error: ' . $wpdb->last_error);
        set_transient('aic_last_db_error', $wpdb->last_error, 30);
    }
    return (bool) $res;
}

function aic_toggle_faq_by_id($id) {
    global $wpdb;
    $table = aic_get_table_name();
    $faq = $wpdb->get_row($wpdb->prepare("SELECT id, is_active FROM {$table} WHERE id = %d", intval($id)));
    if ($wpdb->last_error) {
        error_log('aic_toggle select error: ' . $wpdb->last_error);
        set_transient('aic_last_db_error', $wpdb->last_error, 30);
        return false;
    }
    if (!$faq) return false;
    $new = $faq->is_active ? 0 : 1;
    $res = $wpdb->update(
        $table,
        array('is_active' => $new, 'updated_at' => current_time('mysql')),
        array('id' => intval($id)),
        array('%d','%s'),
        array('%d')
    );
    if ($wpdb->last_error) {
        error_log('aic_toggle update error: ' . $wpdb->last_error);
        set_transient('aic_last_db_error', $wpdb->last_error, 30);
        return false;
    }
    return $res !== false;
}

/* ======= Обработка POST (add / delete / toggle) ======= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // capability check
    if (!current_user_can('manage_options')) {
        wp_die(__('Access denied.', 'ai-multilingual-chat'), 403);
    }

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'aic_faq_nonce')) {
        wp_die(__('Security check failed.', 'ai-multilingual-chat'), 403);
    }

    // Note: Add and Delete are now handled via AJAX
    // This POST handler is kept for backward compatibility only
    
    // Add
    if (isset($_POST['aic_add_faq'])) {
        $question = isset($_POST['question']) ? sanitize_text_field(wp_unslash($_POST['question'])) : '';
        $answer   = isset($_POST['answer']) ? wp_kses_post(wp_unslash($_POST['answer'])) : '';
        $keywords = isset($_POST['keywords']) ? sanitize_text_field(wp_unslash($_POST['keywords'])) : '';
        $language = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : 'ru';
        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;

        if ($question === '' || $answer === '' || $keywords === '') {
            $redirect = add_query_arg('aic_msg', 'empty', menu_page_url('ai-chat-faq', false));  // Исправлено: ai-chat-faq
            wp_safe_redirect(esc_url_raw($redirect));
            exit;
        }

        $ok = aic_insert_faq(array(
            'question'  => $question,
            'answer'    => $answer,
            'keywords'  => $keywords,
            'language'  => $language,
            'is_active' => $is_active,
        ));

        $redirect = add_query_arg('aic_msg', $ok ? 'added' : 'error', menu_page_url('ai-chat-faq', false));  // Исправлено: ai-chat-faq
        wp_safe_redirect(esc_url_raw($redirect));
        exit;
    }

    // Delete
    if (isset($_POST['aic_delete_faq']) && isset($_POST['faq_id'])) {
        $faq_id = sanitize_text_field(wp_unslash($_POST['faq_id']));
        $ok = aic_delete_faq_by_id($faq_id);
        $redirect = add_query_arg('aic_msg', $ok ? 'deleted' : 'error', menu_page_url('ai-chat-faq', false));  // Исправлено: ai-chat-faq
        wp_safe_redirect(esc_url_raw($redirect));
        exit;
    }

    // Toggle
    if (isset($_POST['aic_toggle_faq']) && isset($_POST['faq_id'])) {
        $faq_id = sanitize_text_field(wp_unslash($_POST['faq_id']));
        $ok = aic_toggle_faq_by_id($faq_id);
        $redirect = add_query_arg('aic_msg', $ok ? 'toggled' : 'error', menu_page_url('ai-chat-faq', false));  // Исправлено: ai-chat-faq
        wp_safe_redirect(esc_url_raw($redirect));
        exit;
    }
}

/* ======= Загрузка данных для отображения ======= */
$faqs = aic_get_faqs();
$aic_msg = isset($_GET['aic_msg']) ? sanitize_text_field(wp_unslash($_GET['aic_msg'])) : '';
?>
<div class="wrap">
    <h1><?php esc_html_e('FAQ - Auto Replies', 'ai-multilingual-chat'); ?></h1>

    <?php
    // If there was a DB error - show notification with short text and link to log
    $db_err = get_transient('aic_last_db_error');
    if ($db_err) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php esc_html_e('Database error when working with FAQ. Check wp-content/debug.log for details.', 'ai-multilingual-chat'); ?></p>
        </div>
        <?php
        delete_transient('aic_last_db_error');
    }
    ?>

    <?php if ($aic_msg): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                switch ($aic_msg) {
                    case 'added': _e('FAQ added.', 'ai-multilingual-chat'); break;
                    case 'deleted': _e('FAQ deleted.', 'ai-multilingual-chat'); break;
                    case 'toggled': _e('FAQ status updated.', 'ai-multilingual-chat'); break;
                    case 'empty': _e('Please fill in all required fields.', 'ai-multilingual-chat'); break;
                    case 'error': _e('An error occurred while working with the database.', 'ai-multilingual-chat'); break;
                    default: echo esc_html($aic_msg);
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <p><?php esc_html_e('Configure automatic replies for frequently asked questions. The system will automatically respond to users if their message contains specified keywords.', 'ai-multilingual-chat'); ?></p>

    <div style="background: var(--aic-tab); padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2><?php esc_html_e('Add New FAQ', 'ai-multilingual-chat'); ?></h2>

        <form method="post" action="" id="aic-add-faq-form">
            <?php wp_nonce_field('aic_faq_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="question"><?php esc_html_e('Question', 'ai-multilingual-chat'); ?></label></th>
                    <td>
                        <input type="text" name="question" id="question" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Example question for reference', 'ai-multilingual-chat'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="answer"><?php esc_html_e('Answer', 'ai-multilingual-chat'); ?></label></th>
                    <td>
                        <textarea name="answer" id="answer" rows="5" class="large-text" required></textarea>
                        <p class="description"><?php esc_html_e('Automatic reply that will be sent to user', 'ai-multilingual-chat'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="keywords"><?php esc_html_e('Keywords', 'ai-multilingual-chat'); ?></label></th>
                    <td>
                        <input type="text" name="keywords" id="keywords" class="large-text" required>
                        <p class="description"><?php esc_html_e('Keywords separated by comma (example: contact,phone,reach)', 'ai-multilingual-chat'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="language"><?php esc_html_e('Language', 'ai-multilingual-chat'); ?></label></th>
                    <td>
                        <select name="language" id="language" class="regular-text">
                            <option value="ru"><?php echo esc_html__('Russian', 'ai-multilingual-chat'); ?></option>
                            <option value="en">English</option>
                            <option value="uk"><?php echo esc_html__('Ukrainian', 'ai-multilingual-chat'); ?></option>
                            <option value="de"><?php echo esc_html__('German', 'ai-multilingual-chat'); ?></option>
                            <option value="fr"><?php echo esc_html__('French', 'ai-multilingual-chat'); ?></option>
                            <option value="es"><?php echo esc_html__('Spanish', 'ai-multilingual-chat'); ?></option>
                            <option value="it"><?php echo esc_html__('Italian', 'ai-multilingual-chat'); ?></option>
                            <option value="pt"><?php echo esc_html__('Portuguese', 'ai-multilingual-chat'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Active', 'ai-multilingual-chat'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <?php esc_html_e('Enable this FAQ (will be used for auto-reply)', 'ai-multilingual-chat'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="aic_add_faq" id="aic-add-faq-btn" class="aic-btn primary" value="<?php esc_attr_e('Add FAQ', 'ai-multilingual-chat'); ?>">
            </p>
        </form>
    </div>

    <div style="background: var(--aic-tab); padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2><?php esc_html_e('Existing FAQs', 'ai-multilingual-chat'); ?></h2>

        <?php if (empty($faqs)): ?>
            <p><?php esc_html_e('No FAQs created. Add the first one!', 'ai-multilingual-chat'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 20%;"><?php esc_html_e('Question', 'ai-multilingual-chat'); ?></th>
                        <th style="width: 30%;"><?php esc_html_e('Answer', 'ai-multilingual-chat'); ?></th>
                        <th style="width: 25%;"><?php esc_html_e('Keywords', 'ai-multilingual-chat'); ?></th>
                        <th style="width: 5%;"><?php esc_html_e('Language', 'ai-multilingual-chat'); ?></th>
                        <th style="width: 10%;"><?php esc_html_e('Status', 'ai-multilingual-chat'); ?></th>
                        <th style="width: 15%;"><?php esc_html_e('Actions', 'ai-multilingual-chat'); ?></th>
                    </tr>
                </thead>
                <tbody id="aic-faq-list">
                    <?php foreach ($faqs as $faq): ?>
                        <tr data-faq-id="<?php echo esc_attr($faq->id); ?>">
                            <td><?php echo esc_html($faq->question); ?></td>
                            <td>
                                <?php
                                $answer = wp_strip_all_tags($faq->answer);
                                echo strlen($answer) > 100 ? esc_html(substr($answer, 0, 100) . '...') : esc_html($answer);
                                ?>
                            </td>
                            <td><?php echo esc_html($faq->keywords); ?></td>
                            <td><?php echo esc_html($faq->language); ?></td>
                            <td>
                                <?php if (!empty($faq->is_active)): ?>
                                    <span style="color: green;">✓ <?php esc_html_e('Active', 'ai-multilingual-chat'); ?></span>
                                <?php else: ?>
                                    <span style="color: red;">✗ <?php esc_html_e('Inactive', 'ai-multilingual-chat'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Toggle button (AJAX) -->
                                <button type="button" 
                                        class="aic-btn primary aic-faq-toggle" 
                                        data-faq-id="<?php echo esc_attr($faq->id); ?>"
                                        data-is-active="<?php echo esc_attr($faq->is_active); ?>">
                                    <?php echo !empty($faq->is_active) ? esc_html__('Disable', 'ai-multilingual-chat') : esc_html__('Enable', 'ai-multilingual-chat'); ?>
                                </button>

                                <!-- Delete button (AJAX) -->
                                <button type="button" 
                                        class="aic-btn primary aic-faq-delete" 
                                        data-faq-id="<?php echo esc_attr($faq->id); ?>"
                                        style="margin-left:8px;">
                                    <?php esc_html_e('Delete', 'ai-multilingual-chat'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle FAQ add with AJAX
    $('#aic-add-faq-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $('#aic-add-faq-btn');
        var originalText = $button.val();
        
        // Validate fields
        var question = $('#question').val().trim();
        var answer = $('#answer').val().trim();
        var keywords = $('#keywords').val().trim();
        
        if (!question || !answer || !keywords) {
            var $notice = $('<div class="notice notice-error is-dismissible"><p><?php echo esc_js(__('Please fill in all required fields.', 'ai-multilingual-chat')); ?></p></div>');
            $('.wrap h1').after($notice);
            setTimeout(function() {
                $notice.fadeOut(function() { $(this).remove(); });
            }, 3000);
            return;
        }
        
        // Disable button during request
        $button.prop('disabled', true).val('<?php echo esc_js(__('Adding...', 'ai-multilingual-chat')); ?>');
        
        $.ajax({
            url: aicAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'aic_add_faq',
                nonce: aicAdmin.nonce,
                question: question,
                answer: answer,
                keywords: keywords,
                language: $('#language').val(),
                is_active: $('#is_active').is(':checked') ? '1' : '0'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var $notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                    $('.wrap h1').after($notice);
                    
                    // Auto-dismiss notice after 3 seconds
                    setTimeout(function() {
                        $notice.fadeOut(function() { $(this).remove(); });
                    }, 3000);
                    
                    // Clear form
                    $form[0].reset();
                    
                    // Add new FAQ to the list
                    if (response.data.faq) {
                        var faq = response.data.faq;
                        var answerPreview = faq.answer.replace(/<[^>]*>/g, '');
                        if (answerPreview.length > 100) {
                            answerPreview = answerPreview.substring(0, 100) + '...';
                        }
                        
                        var statusHtml = faq.is_active == 1 
                            ? '<span style="color: green;">✓ <?php echo esc_js(__('Active', 'ai-multilingual-chat')); ?></span>'
                            : '<span style="color: red;">✗ <?php echo esc_js(__('Inactive', 'ai-multilingual-chat')); ?></span>';
                        
                        var toggleText = faq.is_active == 1 
                            ? '<?php echo esc_js(__('Disable', 'ai-multilingual-chat')); ?>'
                            : '<?php echo esc_js(__('Enable', 'ai-multilingual-chat')); ?>';
                        
                        var newRow = $('<tr data-faq-id="' + faq.id + '">' +
                            '<td>' + $('<div>').text(faq.question).html() + '</td>' +
                            '<td>' + $('<div>').text(answerPreview).html() + '</td>' +
                            '<td>' + $('<div>').text(faq.keywords).html() + '</td>' +
                            '<td>' + $('<div>').text(faq.language).html() + '</td>' +
                            '<td>' + statusHtml + '</td>' +
                            '<td>' +
                                '<button type="button" class="aic-btn primary aic-faq-toggle" data-faq-id="' + faq.id + '" data-is-active="' + faq.is_active + '">' +
                                    toggleText +
                                '</button>' +
                                '<button type="button" class="aic-btn primary aic-faq-delete" data-faq-id="' + faq.id + '" style="margin-left:8px;">' +
                                    '<?php echo esc_js(__('Delete', 'ai-multilingual-chat')); ?>' +
                                '</button>' +
                            '</td>' +
                        '</tr>');
                        
                        // If list was empty, replace the "No FAQs" message
                        var $tbody = $('#aic-faq-list');
                        if ($tbody.length === 0) {
                            // Reload page if table doesn't exist (no FAQs were present)
                            location.reload();
                        } else {
                            $tbody.prepend(newRow);
                        }
                    }
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Unknown error', 'ai-multilingual-chat')); ?>';
                    alert('<?php echo esc_js(__('Error', 'ai-multilingual-chat')); ?>: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('FAQ add error:', error);
                var errorMsg = '<?php echo esc_js(__('Connection error with server', 'ai-multilingual-chat')); ?>';
                
                if (xhr.status === 403) {
                    errorMsg = '<?php echo esc_js(__('Security check failed. Please refresh the page.', 'ai-multilingual-chat')); ?>';
                } else if (status === 'timeout') {
                    errorMsg = '<?php echo esc_js(__('Request timeout', 'ai-multilingual-chat')); ?>';
                }
                
                alert('<?php echo esc_js(__('Error', 'ai-multilingual-chat')); ?>: ' + errorMsg);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).val(originalText);
            }
        });
    });
    
    // Handle FAQ delete with AJAX
    $(document).on('click', '.aic-faq-delete', function() {
        if (!confirm('<?php echo esc_js(__('Delete this FAQ?', 'ai-multilingual-chat')); ?>')) {
            return;
        }
        
        var $button = $(this);
        var faqId = $button.data('faq-id');
        var $row = $button.closest('tr');
        
        // Disable button during request
        $button.prop('disabled', true).text('<?php echo esc_js(__('Deleting...', 'ai-multilingual-chat')); ?>');
        
        $.ajax({
            url: aicAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'aic_delete_faq',
                nonce: aicAdmin.nonce,
                faq_id: faqId
            },
            success: function(response) {
                if (response.success) {
                    // Remove row with fade effect
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Check if list is now empty
                        if ($('#aic-faq-list tr').length === 0) {
                            location.reload();
                        }
                    });
                    
                    // Show success message
                    var $notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                    $('.wrap h1').after($notice);
                    
                    // Auto-dismiss notice after 3 seconds
                    setTimeout(function() {
                        $notice.fadeOut(function() { $(this).remove(); });
                    }, 3000);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Unknown error', 'ai-multilingual-chat')); ?>';
                    alert('<?php echo esc_js(__('Error', 'ai-multilingual-chat')); ?>: ' + errorMsg);
                    $button.prop('disabled', false).text('<?php echo esc_js(__('Delete', 'ai-multilingual-chat')); ?>');
                }
            },
            error: function(xhr, status, error) {
                console.error('FAQ delete error:', error);
                var errorMsg = '<?php echo esc_js(__('Connection error with server', 'ai-multilingual-chat')); ?>';
                
                if (xhr.status === 403) {
                    errorMsg = '<?php echo esc_js(__('Security check failed. Please refresh the page.', 'ai-multilingual-chat')); ?>';
                } else if (status === 'timeout') {
                    errorMsg = '<?php echo esc_js(__('Request timeout', 'ai-multilingual-chat')); ?>';
                }
                
                alert('<?php echo esc_js(__('Error', 'ai-multilingual-chat')); ?>: ' + errorMsg);
                $button.prop('disabled', false).text('<?php echo esc_js(__('Delete', 'ai-multilingual-chat')); ?>');
            }
        });
    });
    
    // Handle FAQ toggle with AJAX
    $(document).on('click', '.aic-faq-toggle', function() {
        var $button = $(this);
        var faqId = $button.data('faq-id');
        var isActive = $button.data('is-active');
        var $row = $button.closest('tr');
        var $statusCell = $row.find('td:nth-child(5)'); // Status column
        
        // Disable button during request
        $button.prop('disabled', true).text('<?php echo esc_js(__('Updating...', 'ai-multilingual-chat')); ?>');
        
        $.ajax({
            url: aicAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'aic_toggle_faq',
                nonce: aicAdmin.nonce,
                faq_id: faqId
            },
            success: function(response) {
                if (response.success) {
                    var newState = response.data.is_active;
                    
                    // Update button state
                    $button.data('is-active', newState);
                    $button.text(newState ? '<?php echo esc_js(__('Disable', 'ai-multilingual-chat')); ?>' : '<?php echo esc_js(__('Enable', 'ai-multilingual-chat')); ?>');
                    
                    // Update status display
                    if (newState) {
                        $statusCell.html('<span style="color: green;">✓ <?php echo esc_js(__('Active', 'ai-multilingual-chat')); ?></span>');
                    } else {
                        $statusCell.html('<span style="color: red;">✗ <?php echo esc_js(__('Inactive', 'ai-multilingual-chat')); ?></span>');
                    }
                    
                    // Show success message
                    var $notice = $('<div class="notice notice-success is-dismissible"><p><?php echo esc_js(__('FAQ status updated successfully!', 'ai-multilingual-chat')); ?></p></div>');
                    $('.wrap h1').after($notice);
                    
                    // Auto-dismiss notice after 3 seconds
                    setTimeout(function() {
                        $notice.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php echo esc_js(__('Unknown error', 'ai-multilingual-chat')); ?>';
                    alert('<?php echo esc_js(__('Error', 'ai-multilingual-chat')); ?>: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('FAQ toggle error:', error);
                var errorMsg = '<?php echo esc_js(__('Connection error with server', 'ai-multilingual-chat')); ?>';
                
                if (xhr.status === 403) {
                    errorMsg = '<?php echo esc_js(__('Security check failed. Please refresh the page.', 'ai-multilingual-chat')); ?>';
                } else if (status === 'timeout') {
                    errorMsg = '<?php echo esc_js(__('Request timeout', 'ai-multilingual-chat')); ?>';
                }
                
                alert('<?php echo esc_js(__('Error', 'ai-multilingual-chat')); ?>: ' + errorMsg);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false);
            }
        });
    });
    
    // Handle dismissible notices
    $(document).on('click', '.notice.is-dismissible .notice-dismiss', function() {
        $(this).closest('.notice').fadeOut(function() {
            $(this).remove();
        });
    });
});
</script>
