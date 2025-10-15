<?php
/**
 * Plugin Name: AI Multilingual Chat
 * Plugin URI: https://web-proekt.com
 * Description: Многоязычный чат с автопереводом через AI
 * Version: 1.1.0
 * Author: Oleg Filin
 * Text Domain: ai-multilingual-chat
 */

if (!defined('ABSPATH')) exit;

define('AIC_VERSION', '1.1.0');
define('AIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIC_PLUGIN_FILE', __FILE__);

class AI_Multilingual_Chat {
    
    private static $instance = null;
    private $table_conversations;
    private $table_messages;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->table_conversations = $wpdb->prefix . 'ai_chat_conversations';
        $this->table_messages = $wpdb->prefix . 'ai_chat_messages';
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_footer', array($this, 'render_chat_widget'));
        
        add_action('wp_ajax_aic_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_nopriv_aic_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_aic_get_messages', array($this, 'ajax_get_messages'));
        add_action('wp_ajax_nopriv_aic_get_messages', array($this, 'ajax_get_messages'));
        add_action('wp_ajax_aic_start_conversation', array($this, 'ajax_start_conversation'));
        add_action('wp_ajax_nopriv_aic_start_conversation', array($this, 'ajax_start_conversation'));
        
        add_action('wp_ajax_aic_admin_get_conversations', array($this, 'ajax_admin_get_conversations'));
        add_action('wp_ajax_aic_admin_get_messages', array($this, 'ajax_admin_get_messages'));
        add_action('wp_ajax_aic_admin_send_message', array($this, 'ajax_admin_send_message'));
        add_action('wp_ajax_aic_admin_close_conversation', array($this, 'ajax_admin_close_conversation'));
        
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    public function activate() {
        $this->create_tables();
        $this->update_tables();
        $this->set_default_options();
        $this->log('Плагин активирован');
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        $this->log('Плагин деактивирован');
    }
    
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $sql_conversations = "CREATE TABLE IF NOT EXISTS {$this->table_conversations} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            user_name varchar(255) DEFAULT NULL,
            user_email varchar(255) DEFAULT NULL,
            user_language varchar(10) DEFAULT 'en',
            admin_language varchar(10) DEFAULT 'ru',
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY status (status)
        ) $charset_collate;";
        
        $sql_messages = "CREATE TABLE IF NOT EXISTS {$this->table_messages} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            conversation_id bigint(20) UNSIGNED NOT NULL,
            sender_type varchar(20) NOT NULL,
            message_text text NOT NULL,
            translated_text text DEFAULT NULL,
            original_language varchar(10) DEFAULT NULL,
            target_language varchar(10) DEFAULT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY conversation_id (conversation_id),
            KEY sender_type (sender_type),
            KEY is_read (is_read)
        ) $charset_collate;";
        
        dbDelta($sql_conversations);
        dbDelta($sql_messages);
        
        $this->log('Таблицы созданы/обновлены');
    }
    
    private function update_tables() {
        global $wpdb;
        
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$this->table_messages} LIKE 'target_language'"
        );
        
        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE {$this->table_messages} 
                ADD COLUMN target_language varchar(10) DEFAULT NULL AFTER original_language"
            );
            $this->log('Добавлена колонка target_language');
        }
    }
    
    private function set_default_options() {
        $defaults = array(
            'aic_ai_provider' => 'openai',
            'aic_ai_api_key' => '',
            'aic_admin_language' => 'ru',
            'aic_enable_translation' => '1',
            'aic_mobile_api_key' => $this->generate_api_key(),
            'aic_chat_widget_position' => 'bottom-right',
            'aic_chat_widget_color' => '#667eea',
            'aic_enable_email_notifications' => '0',
            'aic_notification_email' => get_option('admin_email'),
            'aic_welcome_message' => 'Здравствуйте! Чем могу помочь?',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    private function generate_api_key() {
        return 'aic_' . bin2hex(random_bytes(32));
    }
    
    public function add_admin_menu() {
        add_menu_page('AI Chat', 'AI Chat', 'manage_options', 'ai-multilingual-chat', array($this, 'render_admin_page'), 'dashicons-format-chat', 30);
        add_submenu_page('ai-multilingual-chat', 'Настройки', 'Настройки', 'manage_options', 'ai-chat-settings', array($this, 'render_settings_page'));
        add_submenu_page('ai-multilingual-chat', 'Статистика', 'Статистика', 'manage_options', 'ai-chat-stats', array($this, 'render_stats_page'));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ai-multilingual-chat') === false && strpos($hook, 'ai-chat-settings') === false && strpos($hook, 'ai-chat-stats') === false) {
            return;
        }
        
        wp_enqueue_style('aic-admin-style', AIC_PLUGIN_URL . 'admin-style.css', array(), AIC_VERSION);
        wp_enqueue_script('aic-admin-script', AIC_PLUGIN_URL . 'admin-script.js', array('jquery'), AIC_VERSION, true);
        
        wp_localize_script('aic-admin-script', 'aicAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aic_admin_nonce')
        ));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('aic-frontend-style', AIC_PLUGIN_URL . 'frontend-style.css', array(), AIC_VERSION);
        wp_enqueue_script('aic-frontend-script', AIC_PLUGIN_URL . 'frontend-script.js', array('jquery'), AIC_VERSION, true);
        
        wp_localize_script('aic-frontend-script', 'aicFrontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aic_frontend_nonce'),
            'user_language' => $this->get_user_language(),
            'welcome_message' => get_option('aic_welcome_message', 'Здравствуйте!')
        ));
    }
    
    private function get_user_language() {
        $locale = get_locale();
        $language = substr($locale, 0, 2);
        $supported = array('en', 'ru', 'uk', 'de', 'fr', 'es', 'it', 'pt', 'zh', 'ja');
        return in_array($language, $supported) ? $language : 'en';
    }
    
    public function render_chat_widget() {
        if (is_admin()) return;
        include AIC_PLUGIN_DIR . 'templates/chat-widget.php';
    }
    
    public function render_admin_page() {
        include AIC_PLUGIN_DIR . 'templates/admin-chat.php';
    }
    
    public function render_settings_page() {
        if (isset($_POST['aic_save_settings']) && check_admin_referer('aic_settings_nonce')) {
            $this->save_settings($_POST);
            echo '<div class="notice notice-success is-dismissible"><p><strong>Настройки сохранены!</strong></p></div>';
        }
        include AIC_PLUGIN_DIR . 'templates/settings.php';
    }
    
    private function save_settings($post_data) {
        $settings = array('aic_ai_provider', 'aic_ai_api_key', 'aic_admin_language', 'aic_mobile_api_key', 'aic_chat_widget_position', 'aic_chat_widget_color', 'aic_notification_email', 'aic_welcome_message');
        
        foreach ($settings as $setting) {
            if (isset($post_data[$setting])) {
                update_option($setting, sanitize_text_field($post_data[$setting]));
            }
        }
        
        update_option('aic_enable_translation', isset($post_data['aic_enable_translation']) ? '1' : '0');
        update_option('aic_enable_email_notifications', isset($post_data['aic_enable_email_notifications']) ? '1' : '0');
        
        $this->log('Настройки обновлены');
    }
    
    public function render_stats_page() {
        global $wpdb;
        
        $stats = array(
            'total_conversations' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_conversations}"),
            'active_conversations' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_conversations} WHERE status = 'active'"),
            'total_messages' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages}"),
            'unread_messages' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages} WHERE sender_type = 'user' AND is_read = 0"),
            'today_conversations' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_conversations} WHERE DATE(created_at) = CURDATE()"),
            'today_messages' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages} WHERE DATE(created_at) = CURDATE()")
        );
        
        $languages = $wpdb->get_results("SELECT user_language, COUNT(*) as count FROM {$this->table_conversations} GROUP BY user_language ORDER BY count DESC");
        $daily_stats = $wpdb->get_results("SELECT DATE(created_at) as date, COUNT(*) as count FROM {$this->table_conversations} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date DESC");
        
        include AIC_PLUGIN_DIR . 'templates/stats.php';
    }
    
    public function admin_notices() {
        if (!current_user_can('manage_options')) return;
        
        global $wpdb;
        $unread = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages} WHERE sender_type = 'user' AND is_read = 0");
        
        if ($unread > 0) {
            echo '<div class="notice notice-info"><p><strong>AI Chat:</strong> У вас ' . $unread . ' непрочитанных сообщений. <a href="' . admin_url('admin.php?page=ai-multilingual-chat') . '">Открыть чат</a></p></div>';
        }
    }
    
    public function ajax_start_conversation() {
        check_ajax_referer('aic_frontend_nonce', 'nonce');
        
        global $wpdb;
        
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $user_name = isset($_POST['user_name']) ? sanitize_text_field($_POST['user_name']) : '';
        $user_language = isset($_POST['user_language']) ? sanitize_text_field($_POST['user_language']) : '';
        
        if (empty($session_id) || empty($user_name)) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE session_id = %s", $session_id));
        
        if ($conversation) {
            $result = $wpdb->update(
                $this->table_conversations, 
                array('user_name' => $user_name, 'user_language' => $user_language, 'status' => 'active'), 
                array('id' => $conversation->id), 
                array('%s', '%s', '%s'), 
                array('%d')
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
                return;
            }
            
            $conversation_id = $conversation->id;
        } else {
            $result = $wpdb->insert(
                $this->table_conversations, 
                array(
                    'session_id' => $session_id, 
                    'user_name' => $user_name, 
                    'user_language' => $user_language, 
                    'admin_language' => get_option('aic_admin_language', 'ru'), 
                    'status' => 'active'
                ), 
                array('%s', '%s', '%s', '%s', '%s')
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
                return;
            }
            
            $conversation_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array('conversation_id' => $conversation_id));
    }
    
    public function ajax_send_message() {
        check_ajax_referer('aic_frontend_nonce', 'nonce');
        
        global $wpdb;
        
        // Use wp_kses with no allowed tags to strip HTML but preserve Unicode and line breaks
        $message = isset($_POST['message']) ? wp_kses(wp_unslash($_POST['message']), array()) : '';
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $user_language = isset($_POST['user_language']) ? sanitize_text_field($_POST['user_language']) : 'en';
        
        if (empty($message)) {
            wp_send_json_error(array('message' => 'Empty message'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE session_id = %s", $session_id));
        
        if (!$conversation) {
            $user_name = isset($_POST['user_name']) ? sanitize_text_field($_POST['user_name']) : 'Гость';
            
            $wpdb->insert($this->table_conversations, array(
                'session_id' => $session_id,
                'user_name' => $user_name,
                'user_language' => $user_language,
                'admin_language' => get_option('aic_admin_language', 'ru'),
                'status' => 'active'
            ), array('%s', '%s', '%s', '%s', '%s'));
            
            $conversation_id = $wpdb->insert_id;
            $this->log("Создан разговор #{$conversation_id}");
        } else {
            $conversation_id = $conversation->id;
        }
        
        $translated_text = null;
        $admin_language = get_option('aic_admin_language', 'ru');
        
        if (get_option('aic_enable_translation', '1') === '1' && $user_language !== $admin_language) {
            $translated_text = $this->translate_message($message, $user_language, $admin_language);
        }
        
        $result = $wpdb->insert($this->table_messages, array(
            'conversation_id' => $conversation_id,
            'sender_type' => 'user',
            'message_text' => $message,
            'translated_text' => $translated_text,
            'original_language' => $user_language,
            'target_language' => $admin_language,
            'is_read' => 0
        ), array('%d', '%s', '%s', '%s', '%s', '%s', '%d'));
        
        if ($result === false) {
            $this->log('Ошибка сохранения: ' . $wpdb->last_error, 'error');
            wp_send_json_error(array('message' => 'Database error'));
            return;
        }
        
        $message_id = $wpdb->insert_id;
        
        $update_result = $wpdb->update(
            $this->table_conversations, 
            array('updated_at' => current_time('mysql')), 
            array('id' => $conversation_id), 
            array('%s'), 
            array('%d')
        );
        
        if ($update_result === false) {
            $this->log('Ошибка обновления времени разговора: ' . $wpdb->last_error, 'error');
        }
        
        $this->send_admin_notification($conversation_id, 'new_message', $message);
        
        wp_send_json_success(array('message_id' => $message_id, 'conversation_id' => $conversation_id));
    }
    
    public function ajax_get_messages() {
        check_ajax_referer('aic_frontend_nonce', 'nonce');
        
        global $wpdb;
        
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $last_message_id = isset($_POST['last_message_id']) ? intval($_POST['last_message_id']) : 0;
        
        if (empty($session_id)) {
            wp_send_json_error(array('message' => 'Missing session_id'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE session_id = %s", $session_id));
        
        if (!$conversation) {
            wp_send_json_success(array('messages' => array()));
            return;
        }
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT id, sender_type, message_text, translated_text, created_at 
            FROM {$this->table_messages} 
            WHERE conversation_id = %d AND id > %d AND message_text IS NOT NULL 
            ORDER BY created_at ASC",
            $conversation->id, $last_message_id
        ));
        
        if ($messages === null) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        $formatted = array();
        foreach ($messages as $msg) {
            $formatted[] = array(
                'id' => intval($msg->id),
                'sender_type' => $msg->sender_type,
                'message_text' => $msg->message_text,
                'translated_text' => $msg->translated_text,
                'created_at' => $msg->created_at,
                'time' => date('H:i', strtotime($msg->created_at))
            );
        }
        
        wp_send_json_success(array('messages' => $formatted, 'conversation_id' => $conversation->id));
    }
    
    public function ajax_admin_get_conversations() {
        check_ajax_referer('aic_admin_nonce', 'nonce');
        
        global $wpdb;
        
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
        
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, 
            (SELECT COUNT(*) FROM {$this->table_messages} m WHERE m.conversation_id = c.id AND m.sender_type = 'user' AND m.is_read = 0) as unread_count,
            (SELECT message_text FROM {$this->table_messages} m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message
            FROM {$this->table_conversations} c
            WHERE c.status = %s
            ORDER BY c.updated_at DESC
            LIMIT 100",
            $status
        ));
        
        if ($conversations === null) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        $formatted = array();
        foreach ($conversations as $conv) {
            $formatted[] = array(
                'id' => intval($conv->id),
                'user_name' => $conv->user_name ?: 'Гость #' . $conv->id,
                'user_language' => $conv->user_language,
                'status' => $conv->status,
                'unread_count' => intval($conv->unread_count),
                'last_message' => $conv->last_message,
                'created_at' => $conv->created_at,
                'updated_at' => $conv->updated_at
            );
        }
        
        wp_send_json_success(array('conversations' => $formatted));
    }
    
    public function ajax_admin_get_messages() {
        check_ajax_referer('aic_admin_nonce', 'nonce');
        
        global $wpdb;
        
        $conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
        $last_message_id = isset($_POST['last_message_id']) ? intval($_POST['last_message_id']) : 0;
        
        if ($conversation_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid conversation_id'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE id = %d", $conversation_id));
        
        if (!$conversation) {
            wp_send_json_error(array('message' => 'Conversation not found'));
            return;
        }
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_messages} 
            WHERE conversation_id = %d AND id > %d AND message_text IS NOT NULL 
            ORDER BY created_at ASC",
            $conversation_id, $last_message_id
        ));
        
        if ($messages === null) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        if ($last_message_id === 0) {
            $update_result = $wpdb->update(
                $this->table_messages, 
                array('is_read' => 1), 
                array('conversation_id' => $conversation_id, 'sender_type' => 'user'), 
                array('%d'), 
                array('%d', '%s')
            );
            
            if ($update_result === false) {
                $this->log('Ошибка обновления статуса прочтения: ' . $wpdb->last_error, 'error');
            }
        }
        
        $formatted = array();
        foreach ($messages as $msg) {
            $formatted[] = array(
                'id' => intval($msg->id),
                'sender_type' => $msg->sender_type,
                'message_text' => $msg->message_text,
                'translated_text' => $msg->translated_text,
                'created_at' => $msg->created_at,
                'time' => date('H:i', strtotime($msg->created_at))
            );
        }
        
        wp_send_json_success(array(
            'messages' => $formatted,
            'conversation' => array(
                'id' => intval($conversation->id),
                'user_name' => $conversation->user_name,
                'user_language' => $conversation->user_language,
                'status' => $conversation->status
            )
        ));
    }
    
    public function ajax_admin_send_message() {
        check_ajax_referer('aic_admin_nonce', 'nonce');
        
        global $wpdb;
        
        $conversation_id = intval($_POST['conversation_id']);
        // Use wp_kses with no allowed tags to strip HTML but preserve Unicode and line breaks
        $message = wp_kses(wp_unslash($_POST['message']), array());
        
        if (empty($message)) {
            wp_send_json_error(array('message' => 'Empty message'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE id = %d", $conversation_id));
        
        if (!$conversation) {
            wp_send_json_error(array('message' => 'Conversation not found'));
            return;
        }
        
        $translated_text = null;
        $admin_language = get_option('aic_admin_language', 'ru');
        $user_language = $conversation->user_language;
        
        if (get_option('aic_enable_translation', '1') === '1' && $admin_language !== $user_language) {
            $translated_text = $this->translate_message($message, $admin_language, $user_language);
        }
        
        $result = $wpdb->insert($this->table_messages, array(
            'conversation_id' => $conversation_id,
            'sender_type' => 'admin',
            'message_text' => $message,
            'translated_text' => $translated_text,
            'original_language' => $admin_language,
            'target_language' => $user_language,
            'is_read' => 1
        ), array('%d', '%s', '%s', '%s', '%s', '%s', '%d'));
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Database error'));
            return;
        }
        
        $message_id = $wpdb->insert_id;
        
        $update_result = $wpdb->update(
            $this->table_conversations, 
            array('updated_at' => current_time('mysql')), 
            array('id' => $conversation_id), 
            array('%s'), 
            array('%d')
        );
        
        if ($update_result === false) {
            $this->log('Ошибка обновления времени разговора: ' . $wpdb->last_error, 'error');
        }
        
        wp_send_json_success(array('message_id' => $message_id));
    }
    
    public function ajax_admin_close_conversation() {
        check_ajax_referer('aic_admin_nonce', 'nonce');
        
        global $wpdb;
        
        $conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
        
        if ($conversation_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid conversation_id'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE id = %d", $conversation_id));
        
        if (!$conversation) {
            wp_send_json_error(array('message' => 'Conversation not found'));
            return;
        }
        
        $result = $wpdb->update(
            $this->table_conversations, 
            array('status' => 'closed'), 
            array('id' => $conversation_id), 
            array('%s'), 
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        wp_send_json_success(array('message' => 'Conversation closed'));
    }
    
    public function register_rest_routes() {
        register_rest_route('ai-chat/v1', '/conversations', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_conversations'),
            'permission_callback' => array($this, 'rest_permission_check')
        ));
        
        register_rest_route('ai-chat/v1', '/messages/(?P<conversation_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_messages'),
            'permission_callback' => array($this, 'rest_permission_check')
        ));
        
        register_rest_route('ai-chat/v1', '/send', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_send_message'),
            'permission_callback' => array($this, 'rest_permission_check')
        ));
    }
    
    public function rest_permission_check($request) {
        $api_key = $request->get_header('X-API-Key');
        $saved_key = get_option('aic_mobile_api_key', '');
        
        if (empty($saved_key) || $api_key !== $saved_key) {
            return new WP_Error('invalid_api_key', 'Invalid API key', array('status' => 403));
        }
        
        return true;
    }
    
    public function rest_get_conversations($request) {
        global $wpdb;
        
        $conversations = $wpdb->get_results("SELECT * FROM {$this->table_conversations} WHERE status = 'active' ORDER BY updated_at DESC LIMIT 100");
        
        return rest_ensure_response($conversations);
    }
    
    public function rest_get_messages($request) {
        global $wpdb;
        
        $conversation_id = intval($request['conversation_id']);
        
        $messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_messages} WHERE conversation_id = %d ORDER BY created_at ASC", $conversation_id));
        
        return rest_ensure_response($messages);
    }
    
    public function rest_send_message($request) {
        global $wpdb;
        
        $conversation_id = intval($request->get_param('conversation_id'));
        $message = wp_kses($request->get_param('message'), array());
        $sender_type = sanitize_text_field($request->get_param('sender_type'));
        
        $wpdb->insert($this->table_messages, array(
            'conversation_id' => $conversation_id,
            'sender_type' => $sender_type,
            'message_text' => $message,
            'is_read' => 0
        ), array('%d', '%s', '%s', '%d'));
        
        return rest_ensure_response(array('success' => true, 'message_id' => $wpdb->insert_id));
    }
    
        private function translate_message($text, $from_lang, $to_lang) {
        $api_key = get_option('aic_ai_api_key', '');
        
        if (empty($api_key)) {
            $this->log('API ключ не настроен, перевод пропущен', 'warning');
            return null;
        }
        
        $provider = get_option('aic_ai_provider', 'openai');
        
        try {
            switch ($provider) {
                case 'openai':
                    return $this->translate_openai($text, $from_lang, $to_lang, $api_key);
                    
                case 'anthropic':
                    return $this->translate_anthropic($text, $from_lang, $to_lang, $api_key);
                    
                case 'google':
                    return $this->translate_google($text, $from_lang, $to_lang, $api_key);
                    
                default:
                    $this->log('Неизвестный провайдер: ' . $provider, 'error');
                    return null;
            }
        } catch (Exception $e) {
            $this->log('Ошибка перевода: ' . $e->getMessage(), 'error');
            return null;
        }
    }
    
    private function translate_openai($text, $from_lang, $to_lang, $api_key) {
        $language_names = array(
            'en' => 'English', 'ru' => 'Russian', 'uk' => 'Ukrainian',
            'de' => 'German', 'fr' => 'French', 'es' => 'Spanish',
            'it' => 'Italian', 'pt' => 'Portuguese', 'zh' => 'Chinese', 'ja' => 'Japanese'
        );
        
        $prompt = sprintf("Translate from %s to %s. Return only the translation:\n\n%s", 
            $language_names[$from_lang] ?? $from_lang,
            $language_names[$to_lang] ?? $to_lang,
            $text
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array('role' => 'system', 'content' => 'You are a translator. Return only the translation.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'temperature' => 0.3,
                'max_tokens' => 1000
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('OpenAI API error: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            throw new Exception('OpenAI API error: ' . $body['error']['message']);
        }
        
        if (isset($body['choices'][0]['message']['content'])) {
            $translated = trim($body['choices'][0]['message']['content']);
            $this->log("Перевод OpenAI: {$from_lang} -> {$to_lang}");
            return $translated;
        }
        
        throw new Exception('Invalid OpenAI response');
    }
    
    private function translate_anthropic($text, $from_lang, $to_lang, $api_key) {
        $language_names = array(
            'en' => 'English', 'ru' => 'Russian', 'uk' => 'Ukrainian',
            'de' => 'German', 'fr' => 'French', 'es' => 'Spanish'
        );
        
        $prompt = sprintf("Translate from %s to %s:\n\n%s", 
            $language_names[$from_lang] ?? $from_lang,
            $language_names[$to_lang] ?? $to_lang,
            $text
        );
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'headers' => array(
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 1024,
                'messages' => array(array('role' => 'user', 'content' => $prompt))
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Anthropic error: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['content'][0]['text'])) {
            return trim($body['content'][0]['text']);
        }
        
        throw new Exception('Invalid Anthropic response');
    }
    
    private function translate_google($text, $from_lang, $to_lang, $api_key) {
        $language_names = array(
            'en' => 'English', 'ru' => 'Russian', 'uk' => 'Ukrainian',
            'de' => 'German', 'fr' => 'French', 'es' => 'Spanish'
        );
        
        $prompt = sprintf("Translate from %s to %s:\n\n%s", 
            $language_names[$from_lang] ?? $from_lang,
            $language_names[$to_lang] ?? $to_lang,
            $text
        );
        
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'contents' => array(array('parts' => array(array('text' => $prompt)))),
                'generationConfig' => array('temperature' => 0.3, 'maxOutputTokens' => 1000)
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Google AI error: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($body['candidates'][0]['content']['parts'][0]['text']);
        }
        
        throw new Exception('Invalid Google AI response');
    }
    
    private function send_admin_notification($conversation_id, $type, $message_text = '') {
        if (get_option('aic_enable_email_notifications', '0') !== '1') {
            return;
        }
        
        global $wpdb;
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE id = %d", $conversation_id));
        
        if (!$conversation) return;
        
        $to = get_option('aic_notification_email', get_option('admin_email'));
        $site_name = get_bloginfo('name');
        
        if ($type === 'new_message') {
            $subject = "[{$site_name}] Новое сообщение в чате";
            $message = sprintf("Новое сообщение от %s:\n\n%s\n\nОткрыть: %s",
                $conversation->user_name ?: 'Гость',
                mb_substr($message_text, 0, 100),
                admin_url('admin.php?page=ai-multilingual-chat')
            );
            wp_mail($to, $subject, $message);
        }
    }
    
    public function get_dashboard_stats() {
        global $wpdb;
        
        return array(
        'total_conversations' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_conversations}"),
        'active_conversations' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_conversations} WHERE status = 'active'"),
        'unread_messages' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages} WHERE sender_type = 'user' AND is_read = 0"),
        'today_messages' => $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages} WHERE DATE(created_at) = CURDATE()")
    );
}
    
    private function log($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[AI Chat] [%s] %s', strtoupper($level), $message));
        }
    }
}

// Инициализация
function aic_get_instance() {
    return AI_Multilingual_Chat::get_instance();
}

add_action('plugins_loaded', 'aic_get_instance');

register_activation_hook(__FILE__, function() {
    $plugin = AI_Multilingual_Chat::get_instance();
    $plugin->activate();
});

register_deactivation_hook(__FILE__, function() {
    $plugin = AI_Multilingual_Chat::get_instance();
    $plugin->deactivate();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=ai-chat-settings') . '">Настройки</a>';
    array_unshift($links, $settings_link);
    return $links;
});

add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget('aic_dashboard_widget', 'AI Chat - Статистика', function() {
        $plugin = AI_Multilingual_Chat::get_instance();
        $stats = $plugin->get_dashboard_stats();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
            <div style="text-align: center; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                <div style="font-size: 32px; font-weight: bold; color: #2271b1;">
                    <?php echo $stats['active_conversations']; ?>
                </div>
                <div style="color: #646970; font-size: 13px;">Активных диалогов</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                <div style="font-size: 32px; font-weight: bold; color: #d63638;">
                    <?php echo $stats['unread_messages']; ?>
                </div>
                <div style="color: #646970; font-size: 13px;">Непрочитанных</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                <div style="font-size: 32px; font-weight: bold; color: #00a32a;">
                    <?php echo $stats['total_conversations']; ?>
                </div>
                <div style="color: #646970; font-size: 13px;">Всего диалогов</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #f0f0f1; border-radius: 4px;">
                <div style="font-size: 32px; font-weight: bold; color: #996800;">
                    <?php echo $stats['today_messages']; ?>
                </div>
                <div style="color: #646970; font-size: 13px;">Сообщений сегодня</div>
            </div>
        </div>
        <div style="margin-top: 15px; text-align: center;">
            <a href="<?php echo admin_url('admin.php?page=ai-multilingual-chat'); ?>" class="button button-primary">
                Открыть чат
            </a>
        </div>
        <?php
    });
});



add_action('admin_menu', function() {
    global $menu, $wpdb;
    $table_messages = $wpdb->prefix . 'ai_chat_messages';
    $unread = $wpdb->get_var("SELECT COUNT(*) FROM {$table_messages} WHERE sender_type = 'user' AND is_read = 0");
    
    if ($unread > 0) {
        foreach ($menu as $key => $item) {
            if ($item[2] === 'ai-multilingual-chat') {
                $menu[$key][0] .= sprintf(' <span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $unread, $unread);
                break;
            }
        }
    }
}, 999);

// AJAX для генерации нового API ключа
add_action('wp_ajax_aic_generate_api_key', function() {
    check_ajax_referer('aic_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    $new_key = 'aic_' . bin2hex(random_bytes(32));
    $result = update_option('aic_mobile_api_key', $new_key);
    
    if ($result === false) {
        wp_send_json_error(array('message' => 'Failed to save API key'));
        return;
    }
    
    wp_send_json_success(array('api_key' => $new_key));
});

