<?php
if (!defined('ABSPATH')) exit;

$api_key = get_option('aic_ai_api_key', '');
$provider = get_option('aic_ai_provider', 'openai');
$admin_lang = get_option('aic_admin_language', 'ru');
$enable_translation = get_option('aic_enable_translation', '1');
$mobile_api_key = get_option('aic_mobile_api_key', '');
$widget_position = get_option('aic_chat_widget_position', 'bottom-right');
$widget_color = get_option('aic_chat_widget_color', '#667eea');
$enable_emoji = get_option('aic_enable_emoji_picker', '1');
$enable_dark_theme = get_option('aic_enable_dark_theme', '0');
$enable_sound = get_option('aic_enable_sound_notifications', '1');
?>

<div class="wrap">
    <h1>Настройки AI Multilingual Chat</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('aic_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_ai_provider">AI Провайдер</label>
                </th>
                <td>
                    <select name="aic_ai_provider" id="aic_ai_provider" class="regular-text">
                        <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI (GPT-3.5/GPT-4)</option>
                        <option value="anthropic" <?php selected($provider, 'anthropic'); ?>>Anthropic Claude</option>
                        <option value="google" <?php selected($provider, 'google'); ?>>Google AI (Gemini)</option>
                    </select>
                    <p class="description">Выберите провайдера для перевода сообщений</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_ai_api_key">API ключ AI</label>
                </th>
                <td>
                    <input type="text" 
                           name="aic_ai_api_key" 
                           id="aic_ai_api_key" 
                           value="<?php echo esc_attr($api_key); ?>" 
                           class="regular-text">
                    <p class="description">
                        Получить ключ: 
                        <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a> | 
                        <a href="https://console.anthropic.com/" target="_blank">Anthropic</a> | 
                        <a href="https://makersuite.google.com/" target="_blank">Google</a>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_language">Язык администратора</label>
                </th>
                <td>
                    <select name="aic_admin_language" id="aic_admin_language" class="regular-text">
                        <option value="ru" <?php selected($admin_lang, 'ru'); ?>>Русский</option>
                        <option value="en" <?php selected($admin_lang, 'en'); ?>>English</option>
                        <option value="uk" <?php selected($admin_lang, 'uk'); ?>>Українська</option>
                        <option value="de" <?php selected($admin_lang, 'de'); ?>>Deutsch</option>
                        <option value="fr" <?php selected($admin_lang, 'fr'); ?>>Français</option>
                        <option value="es" <?php selected($admin_lang, 'es'); ?>>Español</option>
                        <option value="it" <?php selected($admin_lang, 'it'); ?>>Italiano</option>
                        <option value="pt" <?php selected($admin_lang, 'pt'); ?>>Português</option>
                        <option value="zh" <?php selected($admin_lang, 'zh'); ?>>中文</option>
                        <option value="ja" <?php selected($admin_lang, 'ja'); ?>>日本語</option>
                    </select>
                    <p class="description">Язык, на котором вы общаетесь с клиентами</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_translation">Автоматический перевод</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_translation" 
                               id="aic_enable_translation" 
                               value="1" 
                               <?php checked($enable_translation, '1'); ?>>
                        Включить автоматический перевод через AI
                    </label>
                    <p class="description">Если отключено, сообщения не будут переводиться</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_mobile_api_key">API ключ для мобильного приложения</label>
                </th>
                <td>
                    <input type="text" 
                           name="aic_mobile_api_key" 
                           id="aic_mobile_api_key" 
                           value="<?php echo esc_attr($mobile_api_key); ?>" 
                           class="regular-text"
                           readonly>
                    <button type="button" id="aic_generate_api_key" class="aic-button">
                        <span class="dashicons dashicons-update"></span> Сгенерировать
                    </button>
                    <button type="button" id="aic_copy_api_key" class="aic-button">
                        <span class="dashicons dashicons-admin-page"></span> Копировать
                    </button>
                    <p class="description">Используется для доступа через REST API</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_chat_widget_position">Позиция виджета чата</label>
                </th>
                <td>
                    <select name="aic_chat_widget_position" id="aic_chat_widget_position" class="regular-text">
                        <option value="bottom-right" <?php selected($widget_position, 'bottom-right'); ?>>Справа снизу</option>
                        <option value="bottom-left" <?php selected($widget_position, 'bottom-left'); ?>>Слева снизу</option>
                        <option value="top-right" <?php selected($widget_position, 'top-right'); ?>>Справа сверху</option>
                        <option value="top-left" <?php selected($widget_position, 'top-left'); ?>>Слева сверху</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_chat_widget_color">Цвет виджета</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_chat_widget_color" 
                           id="aic_chat_widget_color" 
                           value="<?php echo esc_attr($widget_color); ?>">
                    <p class="description">Основной цвет кнопки и элементов чата</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_emoji_picker">Emoji picker</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_emoji_picker" 
                               id="aic_enable_emoji_picker" 
                               value="1" 
                               <?php checked($enable_emoji, '1'); ?>>
                        Включить выбор эмодзи в чате
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_dark_theme">Темная тема</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_dark_theme" 
                               id="aic_enable_dark_theme" 
                               value="1" 
                               <?php checked($enable_dark_theme, '1'); ?>>
                        Включить темную тему для чата
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_theme_mode">Тема оформления админки</label>
                </th>
                <td>
                    <?php
                    $theme_mode = get_option('aic_theme_mode', 'auto');
                    ?>
                    <select name="aic_theme_mode" id="aic_theme_mode" style="min-width:140px;">
                        <option value="light" <?php selected($theme_mode, 'light'); ?>>Светлая</option>
                        <option value="dark"  <?php selected($theme_mode, 'dark'); ?>>Тёмная</option>
                        <option value="auto"  <?php selected($theme_mode, 'auto'); ?>>Авто (по системе)</option>
                    </select>
                    <p class="description">Выберите тему для админ-панели плагина. "Авто" использует системную тему вашего устройства.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_sound_notifications">Звуковые уведомления</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_sound_notifications" 
                               id="aic_enable_sound_notifications" 
                               value="1" 
                               <?php checked($enable_sound, '1'); ?>>
                        Включить звуковые уведомления в админке и для клиентов
                    </label>
                    <p class="description">
                        Администраторы слышат звук при новых сообщениях от клиентов, клиенты — при ответах администратора. 
                        Клиенты могут отключить звук для себя через кнопку в чате.
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_notification_sound">Мелодия оповещения</label>
                </th>
                <td>
                    <select id="aic_admin_notification_sound" name="aic_admin_notification_sound">
                        <?php
                        $current_sound = get_option('aic_admin_notification_sound', 'default');
                        $sounds = array(
                            'default' => 'По умолчанию',
                            'bell' => 'Колокольчик',
                            'ding' => 'Динь',
                            'chime' => 'Перезвон',
                            'soft' => 'Мягкий звук'
                        );
                        foreach ($sounds as $key => $label) {
                            $selected = ($current_sound === $key) ? 'selected' : '';
                            echo "<option value=\"{$key}\" {$selected}>{$label}</option>";
                        }
                        ?>
                    </select>
                    <button type="button" class="aic-button" id="aic_preview_admin_sound" style="margin-left: 10px;">
                        <span class="dashicons dashicons-controls-volumeon"></span> Прослушать
                    </button>
                    <p class="description">Выберите мелодию для звуковых уведомлений в админ-панели</p>
                    
                    <script>
                    jQuery(document).ready(function($) {
                        $('#aic_preview_admin_sound').on('click', function() {
                            var soundKey = $('#aic_admin_notification_sound').val();
                            var soundUrl = '<?php echo plugins_url('sounds/', dirname(__FILE__)); ?>' + 'notification-' + soundKey + '.mp3';
                            var audio = new Audio(soundUrl);
                            audio.play().catch(function(e) {
                                console.log('Could not play sound:', e);
                            });
                        });
                        
                        $('#aic_preview_client_sound').on('click', function() {
                            var soundKey = $('#aic_client_notification_sound').val();
                            var soundUrl = '<?php echo plugins_url('sounds/', dirname(__FILE__)); ?>' + 'notification-' + soundKey + '.mp3';
                            var audio = new Audio(soundUrl);
                            audio.play().catch(function(e) {
                                console.log('Could not play sound:', e);
                            });
                        });
                    });
                    </script>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_client_notification_sound">Мелодия оповещения для клиентов</label>
                </th>
                <td>
                    <select id="aic_client_notification_sound" name="aic_client_notification_sound">
                        <?php
                        $current_client_sound = get_option('aic_client_notification_sound', 'default');
                        $sounds = array(
                            'default' => 'По умолчанию',
                            'bell' => 'Колокольчик',
                            'ding' => 'Динь',
                            'chime' => 'Перезвон',
                            'soft' => 'Мягкий звук'
                        );
                        foreach ($sounds as $key => $label) {
                            $selected = ($current_client_sound === $key) ? 'selected' : '';
                            echo "<option value=\"{$key}\" {$selected}>{$label}</option>";
                        }
                        ?>
                    </select>
                    <button type="button" class="aic-button" id="aic_preview_client_sound" style="margin-left: 10px;">
                        <span class="dashicons dashicons-controls-volumeon"></span> Прослушать
                    </button>
                    <p class="description">Выберите мелодию для звуковых уведомлений клиентов в чате</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_avatar">Аватар администратора</label>
                </th>
                <td>
                    <?php
                    $admin_avatar = get_option('aic_admin_avatar', '');
                    ?>
                    <div style="margin-bottom: 10px;">
                        <?php if (!empty($admin_avatar)): ?>
                            <img id="aic_admin_avatar_preview" src="<?php echo esc_url($admin_avatar); ?>" style="max-width: 100px; max-height: 100px; border-radius: 50%; display: block; margin-bottom: 10px;">
                        <?php else: ?>
                            <img id="aic_admin_avatar_preview" src="" style="max-width: 100px; max-height: 100px; border-radius: 50%; display: none; margin-bottom: 10px;">
                        <?php endif; ?>
                    </div>
                    <input type="hidden" 
                           name="aic_admin_avatar" 
                           id="aic_admin_avatar" 
                           value="<?php echo esc_attr($admin_avatar); ?>">
                    <button type="button" id="aic_upload_avatar" class="aic-button">
                        <span class="dashicons dashicons-upload"></span> Загрузить изображение
                    </button>
                    <button type="button" id="aic_remove_avatar" class="aic-button" <?php echo empty($admin_avatar) ? 'style="display:none;"' : ''; ?>>
                        <span class="dashicons dashicons-no"></span> Удалить
                    </button>
                    <p class="description">Аватар будет отображаться рядом с сообщениями администратора в чате. Рекомендуемый размер: 100x100 пикселей</p>
                </td>
            </tr>
        </table>
        
        <hr>
        
        <h2>REST API для мобильного приложения</h2>
        
        <p>Используйте эти endpoints для интеграции с мобильным приложением:</p>
        
        <table class="widefat" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Метод</th>
                    <th>Endpoint</th>
                    <th>Описание</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>GET</code></td>
                    <td><code><?php echo rest_url('ai-chat/v1/conversations'); ?></code></td>
                    <td>Получить список разговоров</td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code><?php echo rest_url('ai-chat/v1/messages/{id}'); ?></code></td>
                    <td>Получить сообщения разговора</td>
                </tr>
                <tr>
                    <td><code>POST</code></td>
                    <td><code><?php echo rest_url('ai-chat/v1/send'); ?></code></td>
                    <td>Отправить сообщение</td>
                </tr>
            </tbody>
        </table>
        
        <p style="margin-top: 15px;">
            <strong>Важно:</strong> Все запросы должны содержать заголовок <code>X-API-Key</code> с API ключом.
        </p>
        
        <?php submit_button('Сохранить настройки', 'primary large', 'aic_save_settings'); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Генерация API ключа
    $('#aic_generate_api_key').on('click', function() {
        const key = 'aic_' + Math.random().toString(36).substring(2, 15) + 
                    Math.random().toString(36).substring(2, 15) +
                    Math.random().toString(36).substring(2, 15);
        $('#aic_mobile_api_key').val(key).prop('readonly', false);
    });
    
    // Копирование API ключа
    $('#aic_copy_api_key').on('click', function() {
        const apiKey = $('#aic_mobile_api_key').val();
        if (!apiKey) {
            alert('Сначала сгенерируйте API ключ');
            return;
        }
        
        navigator.clipboard.writeText(apiKey).then(function() {
            const btn = $('#aic_copy_api_key');
            const originalText = btn.html();
            btn.html('<span class="dashicons dashicons-yes"></span> Скопировано!');
            setTimeout(function() {
                btn.html(originalText);
            }, 2000);
        });
    });
    
    // Admin avatar upload using WordPress media library
    var avatarUploader;
    
    $('#aic_upload_avatar').on('click', function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (avatarUploader) {
            avatarUploader.open();
            return;
        }
        
        // Create the media uploader
        avatarUploader = wp.media({
            title: 'Выберите аватар администратора',
            button: {
                text: 'Использовать это изображение'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });
        
        // When an image is selected, run a callback
        avatarUploader.on('select', function() {
            var attachment = avatarUploader.state().get('selection').first().toJSON();
            $('#aic_admin_avatar').val(attachment.url);
            $('#aic_admin_avatar_preview').attr('src', attachment.url).show();
            $('#aic_remove_avatar').show();
        });
        
        avatarUploader.open();
    });
    
    // Remove avatar
    $('#aic_remove_avatar').on('click', function(e) {
        e.preventDefault();
        $('#aic_admin_avatar').val('');
        $('#aic_admin_avatar_preview').attr('src', '').hide();
        $(this).hide();
    });
});
</script>

<style>
.form-table th {
    width: 250px;
}

.comment-ays, .feature-filter, .popular-tags, .stuffbox, .widgets-holder-wrap, .wp-editor-container, p.popular-tags, table.widefat {
    background: var(--aic-bg);
}

.widefat code {
    background: var(--aic-accent);
    color: var(--aic-text-primary);
    font-size: 1.3em;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
}
/* light theme specific hover states */
[data-theme="light"] .widefat code {
  color: #fff;
}

.widefat tfoot tr td, .widefat tfoot tr th, .widefat thead tr td, .widefat thead tr th{
    color: var(--aic-text-primary);
}
.widefat td, .widefat th {
    color: color: var(--aic-text-primary);
}

input.readonly, input[readonly], textarea.readonly, textarea[readonly] {
    background-color: var(--aic-accent);
    color: var(--aic-text-primary);
}

.wp-core-ui select {
    font-size: 14px;
    line-height: 2;
    color: var(--aic-text-primary);
    border-color: var(--aic-accent);
    box-shadow: none;
    border-radius: 3px;
    padding: 0 24px 0 8px;
    min-height: 30px;
    max-width: 25rem;
    -webkit-appearance: none;
    background: var(--aic-bg) url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E) no-repeat right 5px top 55%;
    background-size: 16px 16px;
    cursor: pointer;
    vertical-align: middle;
}

input[type=text] {
    color: var(--aic-text-primary);
    background: var(--aic-bg);
    border-color: var(--aic-accent);
}
.aic-button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    vertical-align: middle;
}

.aic-button {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid var(--aic-accent);
  background: var(--aic-accent);
  color: var(--aic-text-primary);
  cursor: pointer;
  transition: background var(--aic-transition), transform var(--aic-transition), border var(--aic-transition);
  font-size: 13px;
  font-weight: 500;
}

/* light theme specific hover states */
[data-theme="light"] .aic-button {
  color: #fff;
}

.aic-button:hover {
  background: rgba(124, 92, 255, 0.06);
  transform: translateY(-1px);
}

.aic-button.primary {
  background: var(--aic-accent);
  color: #fff;
  box-shadow: var(--aic-shadow-sm);
}

.aic-button.primary:hover {
  background: var(--aic-accent);
  opacity: 0.9;
}

.aic-button[aria-pressed="true"] {
  background: var(--aic-accent);
  color: #fff;
  border-color: var(--aic-accent);
}

.aic-button:focus {
  outline: 2px solid rgba(124, 92, 255, 0.2);
  outline-offset: 2px;
}

</style>
