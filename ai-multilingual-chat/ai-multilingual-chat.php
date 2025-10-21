<?php
/**
 * Plugin Name: AI Multilingual Chat
 * Plugin URI: https://web-proekt.com
 * Description: Многоязычный чат с автопереводом через AI
 * Version: 2.0.8
 * Author: Oleg Filin
 * Text Domain: ai-multilingual-chat
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('AIC_VERSION', '2.0.8');
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
        add_action('plugins_loaded', array($this, 'load_textdomain'));
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
        add_action('wp_ajax_aic_admin_typing', array($this, 'ajax_admin_typing'));
        
        add_action('wp_ajax_aic_user_typing', array($this, 'ajax_user_typing'));
        add_action('wp_ajax_nopriv_aic_user_typing', array($this, 'ajax_user_typing'));
        
        add_action('wp_ajax_aic_export_conversation', array($this, 'ajax_export_conversation'));
        add_action('wp_ajax_aic_toggle_faq', array($this, 'ajax_toggle_faq'));
        
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('ai-multilingual-chat', false, dirname(plugin_basename(AIC_PLUGIN_FILE)) . '/languages');
    }
    
    public function activate() {
        $this->create_tables();
        $this->update_tables();
        $this->set_default_options();
        $this->log(__('Plugin activated', 'ai-multilingual-chat'));
        flush_rewrite_rules();
    }
    
    public static function activate_plugin() {
        global $wpdb;
        
        // Set up table names
        $table_conversations = $wpdb->prefix . 'ai_chat_conversations';
        $table_messages = $wpdb->prefix . 'ai_chat_messages';
        $table_cache = $wpdb->prefix . 'ai_chat_translation_cache';
        $table_faq = $wpdb->prefix . 'ai_chat_faq';
        
        // Create tables
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Conversations table
        $sql_conversations = "CREATE TABLE IF NOT EXISTS {$table_conversations} (
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
            user_typing tinyint(1) DEFAULT 0,
            admin_typing tinyint(1) DEFAULT 0,
            user_typing_at datetime DEFAULT NULL,
            admin_typing_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY status (status),
            KEY idx_conv_status_updated (status, updated_at)
        ) $charset_collate;";
        
        dbDelta($sql_conversations);
        
        // Messages table
        $sql_messages = "CREATE TABLE IF NOT EXISTS {$table_messages} (
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
            KEY is_read (is_read),
            KEY idx_msg_conv_created (conversation_id, created_at),
            KEY idx_msg_is_read (is_read, sender_type)
        ) $charset_collate;";
        
        dbDelta($sql_messages);
        
        // Translation cache table
        $sql_cache = "CREATE TABLE IF NOT EXISTS {$table_cache} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_text text NOT NULL,
            source_language varchar(10) NOT NULL,
            target_language varchar(10) NOT NULL,
            translated_text text NOT NULL,
            text_hash varchar(64) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY text_hash (text_hash),
            KEY languages (source_language, target_language)
        ) $charset_collate;";
        
        dbDelta($sql_cache);
        
        // FAQ table
        $sql_faq = "CREATE TABLE IF NOT EXISTS {$table_faq} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            question text NOT NULL,
            answer text NOT NULL,
            keywords text DEFAULT NULL,
            language varchar(10) DEFAULT 'ru',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_active (is_active),
            KEY language (language)
        ) $charset_collate;";
        
        dbDelta($sql_faq);
        
        // Add default FAQs if table is empty
        $faq_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_faq}");
        if ($faq_count == 0) {
            $default_faqs = array(
                array(
                    'question' => 'How can I contact you?',
                    'answer' => 'You can write to us here in the chat, and we will reply as soon as possible.',
                    'keywords' => 'contact,phone,email,reach',
                    'language' => 'en'
                ),
                array(
                    'question' => 'What are your business hours?',
                    'answer' => 'We work daily from 9:00 AM to 6:00 PM.',
                    'keywords' => 'schedule,time,work,hours',
                    'language' => 'en'
                )
            );
            
            foreach ($default_faqs as $faq) {
                $wpdb->insert($table_faq, $faq, array('%s', '%s', '%s', '%s'));
            }
        }
        
        // Set default options
        $defaults = array(
            'aic_ai_provider' => 'openai',
            'aic_ai_api_key' => '',
            'aic_admin_language' => 'ru',
            'aic_enable_translation' => '1',
            'aic_mobile_api_key' => 'aic_' . bin2hex(random_bytes(32)),
            'aic_chat_widget_position' => 'bottom-right',
            'aic_chat_widget_color' => '#667eea',
            'aic_enable_email_notifications' => '0',
            'aic_notification_email' => get_option('admin_email'),
            'aic_welcome_message' => 'Здравствуйте! Чем могу помочь?',
            'aic_enable_emoji_picker' => '1',
            'aic_enable_dark_theme' => '0',
            'aic_enable_sound_notifications' => '1',
            'aic_client_notification_sound' => 'default',
            'aic_theme_mode' => 'auto',
            'aic_admin_avatar' => '',
            'aic_widget_border_radius' => '12',
            'aic_widget_font_size' => '14',
            'aic_widget_padding' => '20',
            'aic_widget_custom_css' => '',
            'aic_widget_bg_color' => '#1c2126',
            'aic_chat_button_color' => '#667eea',
            'aic_header_bg_color' => '#667eea',
            'aic_user_msg_bg_color' => '#667eea',
            'aic_admin_msg_bg_color' => '#ffffff',
            'aic_user_msg_text_color' => '#ffffff',
            'aic_admin_msg_text_color' => '#333333',
            'aic_send_button_color' => '#667eea',
            'aic_input_border_color' => '#dddddd',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AI Chat] [INFO] Plugin activated');
        }
    }
    
    public function deactivate() {
        $this->log(__('Plugin deactivated', 'ai-multilingual-chat'));
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
        
        $this->log(__('Tables created/updated', 'ai-multilingual-chat'));
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
            $this->log(__('Added target_language column', 'ai-multilingual-chat'));
        }
        
        // Add typing indicator columns to conversations table
        $typing_column = $wpdb->get_results(
            "SHOW COLUMNS FROM {$this->table_conversations} LIKE 'user_typing'"
        );
        
        if (empty($typing_column)) {
            $wpdb->query(
                "ALTER TABLE {$this->table_conversations} 
                ADD COLUMN user_typing tinyint(1) DEFAULT 0,
                ADD COLUMN admin_typing tinyint(1) DEFAULT 0,
                ADD COLUMN user_typing_at datetime DEFAULT NULL,
                ADD COLUMN admin_typing_at datetime DEFAULT NULL"
            );
            $this->log(__('Added typing indicator columns', 'ai-multilingual-chat'));
        }
        
        // Create translation cache table
        $cache_table = $wpdb->prefix . 'ai_chat_translation_cache';
        $cache_exists = $wpdb->get_var("SHOW TABLES LIKE '{$cache_table}'");
        
        if (!$cache_exists) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$cache_table} (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                source_text text NOT NULL,
                source_language varchar(10) NOT NULL,
                target_language varchar(10) NOT NULL,
                translated_text text NOT NULL,
                text_hash varchar(64) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY text_hash (text_hash),
                KEY languages (source_language, target_language)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            $this->log(__('Created translation cache table', 'ai-multilingual-chat'));
        }
        
        // Create FAQ table
        $faq_table = $wpdb->prefix . 'ai_chat_faq';
        $faq_exists = $wpdb->get_var("SHOW TABLES LIKE '{$faq_table}'");
        
        if (!$faq_exists) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$faq_table} (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                question text NOT NULL,
                answer text NOT NULL,
                keywords text DEFAULT NULL,
                language varchar(10) DEFAULT 'ru',
                is_active tinyint(1) DEFAULT 1,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY is_active (is_active),
                KEY language (language)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            $this->log(__('Created FAQ table', 'ai-multilingual-chat'));
            
            // Add default FAQs
            $default_faqs = array(
                array(
                    'question' => __('How can I contact you?', 'ai-multilingual-chat'),
                    'answer' => __('You can write to us here in the chat, and we will reply as soon as possible.', 'ai-multilingual-chat'),
                    'keywords' => 'contact,phone,email,reach',
                    'language' => 'en'
                ),
                array(
                    'question' => __('What are your business hours?', 'ai-multilingual-chat'),
                    'answer' => __('We work daily from 9:00 AM to 6:00 PM.', 'ai-multilingual-chat'),
                    'keywords' => 'schedule,time,work,hours',
                    'language' => 'en'
                )
            );
            
            foreach ($default_faqs as $faq) {
                $wpdb->insert($faq_table, $faq, array('%s', '%s', '%s', '%s'));
            }
        }
        
        // Add additional indexes for performance
        $indexes = array(
            "CREATE INDEX idx_conv_status_updated ON {$this->table_conversations}(status, updated_at)",
            "CREATE INDEX idx_msg_conv_created ON {$this->table_messages}(conversation_id, created_at)",
            "CREATE INDEX idx_msg_is_read ON {$this->table_messages}(is_read, sender_type)"
        );
        
        foreach ($indexes as $index_sql) {
            $wpdb->query($index_sql);
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
            'aic_enable_emoji_picker' => '1',
            'aic_enable_dark_theme' => '0',
            'aic_enable_sound_notifications' => '1',
            'aic_client_notification_sound' => 'default',
            'aic_theme_mode' => 'auto',
            'aic_admin_avatar' => '',
            'aic_widget_border_radius' => '12',
            'aic_widget_font_size' => '14',
            'aic_widget_padding' => '20',
            'aic_widget_custom_css' => '',
            'aic_widget_bg_color' => '#1c2126',
            'aic_chat_button_color' => '#667eea',
            'aic_header_bg_color' => '#667eea',
            'aic_user_msg_bg_color' => '#667eea',
            'aic_admin_msg_bg_color' => '#ffffff',
            'aic_user_msg_text_color' => '#ffffff',
            'aic_admin_msg_text_color' => '#333333',
            'aic_send_button_color' => '#667eea',
            'aic_input_border_color' => '#dddddd',
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
        add_menu_page(__('AI Chat', 'ai-multilingual-chat'), __('AI Chat', 'ai-multilingual-chat'), 'manage_options', 'ai-multilingual-chat', array($this, 'render_admin_page'), 'dashicons-format-chat', 30);
        add_submenu_page('ai-multilingual-chat', __('Conversation Management', 'ai-multilingual-chat'), __('Conversation Management', 'ai-multilingual-chat'), 'manage_options', 'ai-multilingual-chat', array($this, 'render_admin_page'));
        add_submenu_page('ai-multilingual-chat', __('Settings', 'ai-multilingual-chat'), __('Settings', 'ai-multilingual-chat'), 'manage_options', 'ai-chat-settings', array($this, 'render_settings_page'));
        add_submenu_page('ai-multilingual-chat', __('Statistics', 'ai-multilingual-chat'), __('Statistics', 'ai-multilingual-chat'), 'manage_options', 'ai-chat-stats', array($this, 'render_stats_page'));
        add_submenu_page('ai-multilingual-chat', __('FAQ', 'ai-multilingual-chat'), __('FAQ', 'ai-multilingual-chat'), 'manage_options', 'ai-chat-faq', array($this, 'render_faq_page'));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ai-multilingual-chat') === false && strpos($hook, 'ai-chat-settings') === false && strpos($hook, 'ai-chat-stats') === false && strpos($hook, 'ai-chat-faq') === false) {
            return;
        }
        
        // Enqueue WordPress media uploader for settings page
        if (strpos($hook, 'ai-chat-settings') !== false) {
            wp_enqueue_media();
        }
        
        wp_enqueue_style('aic-admin-style', AIC_PLUGIN_URL . 'admin-style.css', array(), AIC_VERSION);
        wp_enqueue_script('aic-admin-script', AIC_PLUGIN_URL . 'admin-script.js', array('jquery'), AIC_VERSION, true);
        
        // Enqueue theme toggle script
        wp_enqueue_script('aic-theme-toggle', AIC_PLUGIN_URL . 'assets/theme-toggle.js', array(), AIC_VERSION, false);
        
        // Enqueue emoji picker if enabled
        if (get_option('aic_enable_emoji_picker', '1') === '1') {
            wp_enqueue_style('aic-emoji-picker', AIC_PLUGIN_URL . 'emoji-picker.css', array(), AIC_VERSION);
            wp_enqueue_script('aic-emoji-picker', AIC_PLUGIN_URL . 'emoji-picker.js', array('jquery'), AIC_VERSION, true);
        }
        
        // Enqueue dark theme if enabled (legacy support)
        if (get_option('aic_enable_dark_theme', '0') === '1') {
            wp_enqueue_style('aic-dark-theme', AIC_PLUGIN_URL . 'dark-theme.css', array('aic-admin-style'), AIC_VERSION);
            add_filter('admin_body_class', function($classes) {
                return $classes . ' aic-dark-theme';
            });
        }
        
        wp_localize_script('aic-admin-script', 'aicAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aic_admin_nonce'),
            'enable_emoji' => get_option('aic_enable_emoji_picker', '1'),
            'enable_sound' => get_option('aic_enable_sound_notifications', '1'),
            'enable_dark_theme' => get_option('aic_enable_dark_theme', '0'),
            'theme_mode' => get_option('aic_theme_mode', 'auto'),
            'sound_base_url' => plugins_url('sounds/', __FILE__),
            'sound_choice' => get_option('aic_admin_notification_sound', 'default'),
            'admin_avatar' => get_option('aic_admin_avatar', ''),
            'available_sounds' => array(
                'default' => __('Default', 'ai-multilingual-chat'),
                'bell' => __('Bell', 'ai-multilingual-chat'),
                'ding' => __('Ding', 'ai-multilingual-chat'),
                'chime' => __('Chime', 'ai-multilingual-chat'),
                'soft' => __('Soft', 'ai-multilingual-chat')
            ),
            'user_language' => $this->get_user_language()
        ));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('aic-frontend-style', AIC_PLUGIN_URL . 'frontend-style.css', array(), AIC_VERSION);
        
        // Enqueue i18n first
        wp_enqueue_script('aic-i18n', AIC_PLUGIN_URL . 'i18n.js', array('jquery'), AIC_VERSION, true);
        wp_enqueue_script('aic-frontend-script', AIC_PLUGIN_URL . 'frontend-script.js', array('jquery', 'aic-i18n'), AIC_VERSION, true);
        
        // Enqueue emoji picker if enabled
        if (get_option('aic_enable_emoji_picker', '1') === '1') {
            wp_enqueue_style('aic-emoji-picker', AIC_PLUGIN_URL . 'emoji-picker.css', array(), AIC_VERSION);
            wp_enqueue_script('aic-emoji-picker', AIC_PLUGIN_URL . 'emoji-picker.js', array('jquery'), AIC_VERSION, true);
        }
        
        // Enqueue dark theme if enabled
        if (get_option('aic_enable_dark_theme', '0') === '1') {
            wp_enqueue_style('aic-dark-theme', AIC_PLUGIN_URL . 'dark-theme.css', array('aic-frontend-style'), AIC_VERSION);
            add_filter('body_class', function($classes) {
                $classes[] = 'aic-dark-theme';
                return $classes;
            });
        }
        
        wp_localize_script('aic-frontend-script', 'aicFrontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aic_frontend_nonce'),
            'user_language' => $this->get_user_language(),
            'welcome_message' => get_option('aic_welcome_message', __('Hello!', 'ai-multilingual-chat')),
            'enable_emoji' => get_option('aic_enable_emoji_picker', '1'),
            'enable_dark_theme' => get_option('aic_enable_dark_theme', '0'),
            'enable_sound' => get_option('aic_enable_sound_notifications', '1'),
            'sound_base_url' => plugins_url('sounds/', __FILE__),
            'sound_choice' => get_option('aic_client_notification_sound', 'default'),
            'admin_avatar' => get_option('aic_admin_avatar', ''),
            'available_sounds' => array(
                'default' => __('Default', 'ai-multilingual-chat'),
                'bell' => __('Bell', 'ai-multilingual-chat'),
                'ding' => __('Ding', 'ai-multilingual-chat'),
                'chime' => __('Chime', 'ai-multilingual-chat'),
                'soft' => __('Soft', 'ai-multilingual-chat')
            )
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
            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Settings saved!', 'ai-multilingual-chat') . '</strong></p></div>';
        }
        include AIC_PLUGIN_DIR . 'templates/settings.php';
    }
    
    private function save_settings($post_data) {
        $settings = array('aic_ai_provider', 'aic_ai_api_key', 'aic_admin_language', 'aic_mobile_api_key', 'aic_chat_widget_position', 'aic_chat_widget_color', 'aic_notification_email', 'aic_welcome_message', 'aic_admin_notification_sound', 'aic_client_notification_sound', 'aic_theme_mode', 'aic_admin_avatar', 'aic_widget_border_radius', 'aic_widget_font_size', 'aic_widget_padding', 'aic_widget_bg_color', 'aic_chat_button_color', 'aic_header_bg_color', 'aic_header_text_color', 'aic_header_status_color', 'aic_header_icons_color', 'aic_header_close_color', 'aic_user_msg_bg_color', 'aic_admin_msg_bg_color', 'aic_user_msg_text_color', 'aic_admin_msg_text_color', 'aic_send_button_color', 'aic_input_border_color');
        
        foreach ($settings as $setting) {
            if (isset($post_data[$setting])) {
                update_option($setting, sanitize_text_field($post_data[$setting]));
            }
        }
        
        // Handle custom CSS separately (needs sanitization for textarea)
        if (isset($post_data['aic_widget_custom_css'])) {
            update_option('aic_widget_custom_css', wp_strip_all_tags($post_data['aic_widget_custom_css']));
        }
        
        update_option('aic_enable_translation', isset($post_data['aic_enable_translation']) ? '1' : '0');
        update_option('aic_enable_email_notifications', isset($post_data['aic_enable_email_notifications']) ? '1' : '0');
        update_option('aic_enable_emoji_picker', isset($post_data['aic_enable_emoji_picker']) ? '1' : '0');
        update_option('aic_enable_dark_theme', isset($post_data['aic_enable_dark_theme']) ? '1' : '0');
        update_option('aic_enable_sound_notifications', isset($post_data['aic_enable_sound_notifications']) ? '1' : '0');
        
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
    
    public function render_faq_page() {
        global $wpdb;
        $faq_table = $wpdb->prefix . 'ai_chat_faq';
        
        // Handle form submissions
        if (isset($_POST['aic_add_faq']) && check_admin_referer('aic_faq_nonce')) {
            $wpdb->insert($faq_table, array(
                'question' => sanitize_text_field($_POST['question']),
                'answer' => sanitize_textarea_field($_POST['answer']),
                'keywords' => sanitize_text_field($_POST['keywords']),
                'language' => sanitize_text_field($_POST['language']),
                'is_active' => 1
            ), array('%s', '%s', '%s', '%s', '%d'));
            
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('FAQ added!', 'ai-multilingual-chat') . '</p></div>';
        }
        
        if (isset($_POST['aic_delete_faq']) && check_admin_referer('aic_faq_nonce')) {
            $faq_id = intval($_POST['faq_id']);
            $wpdb->delete($faq_table, array('id' => $faq_id), array('%d'));
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('FAQ deleted!', 'ai-multilingual-chat') . '</p></div>';
        }
        
        $faqs = $wpdb->get_results("SELECT * FROM {$faq_table} ORDER BY created_at DESC");
        
        include AIC_PLUGIN_DIR . 'templates/faq.php';
    }
    
    public function admin_notices() {
        if (!current_user_can('manage_options')) return;
        
        global $wpdb;
        $unread = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_messages} WHERE sender_type = 'user' AND is_read = 0");
        
        if ($unread > 0) {
            /* translators: %1$d: number of unread messages */
            $message = sprintf(_n('You have %d unread message.', 'You have %d unread messages.', $unread, 'ai-multilingual-chat'), $unread);
            echo '<div class="notice notice-info"><p><strong>' . esc_html__('AI Chat:', 'ai-multilingual-chat') . '</strong> ' . esc_html($message) . ' <a href="' . esc_url(admin_url('admin.php?page=ai-multilingual-chat')) . '">' . esc_html__('Open chat', 'ai-multilingual-chat') . '</a></p></div>';
        }
    }
    
    public function ajax_start_conversation() {
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_frontend_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
        global $wpdb;
        
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        $user_name = isset($_POST['user_name']) ? sanitize_text_field($_POST['user_name']) : '';
        $user_language = isset($_POST['user_language']) ? sanitize_text_field($_POST['user_language']) : '';
        
        if (empty($session_id) || empty($user_name)) {
            wp_send_json_error(array('message' => 'Missing required parameters'));
            return;
        }
        
        $conversation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_conversations} WHERE session_id = %s", $session_id));
        
        // Get user_id if logged in
        $user_id = is_user_logged_in() ? get_current_user_id() : null;
        
        if ($conversation) {
            $update_data = array(
                'user_name' => $user_name, 
                'user_language' => $user_language, 
                'status' => 'active'
            );
            
            if ($user_id) {
                $update_data['user_id'] = $user_id;
            }
            
            $result = $wpdb->update(
                $this->table_conversations, 
                $update_data,
                array('id' => $conversation->id), 
                $user_id ? array('%s', '%s', '%s', '%d') : array('%s', '%s', '%s'), 
                array('%d')
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
                return;
            }
            
            $conversation_id = $conversation->id;
        } else {
            $insert_data = array(
                'session_id' => $session_id, 
                'user_name' => $user_name, 
                'user_language' => $user_language, 
                'admin_language' => get_option('aic_admin_language', 'ru'), 
                'status' => 'active'
            );
            
            if ($user_id) {
                $insert_data['user_id'] = $user_id;
            }
            
            $result = $wpdb->insert(
                $this->table_conversations, 
                $insert_data,
                $user_id ? array('%s', '%s', '%s', '%s', '%s', '%d') : array('%s', '%s', '%s', '%s', '%s')
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
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_frontend_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
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
        
        // Check for FAQ auto-reply
        $auto_reply = $this->check_faq_auto_reply($message, $user_language);
        if ($auto_reply) {
            // Insert auto-reply message
            $wpdb->insert($this->table_messages, array(
                'conversation_id' => $conversation_id,
                'sender_type' => 'admin',
                'message_text' => $auto_reply,
                'translated_text' => null,
                'original_language' => $user_language,
                'target_language' => $user_language,
                'is_read' => 0
            ), array('%d', '%s', '%s', '%s', '%s', '%s', '%d'));
        }
        
        wp_send_json_success(array('message_id' => $message_id, 'conversation_id' => $conversation_id));
    }
    
    private function check_faq_auto_reply($message, $language) {
        global $wpdb;
        $faq_table = $wpdb->prefix . 'ai_chat_faq';
        
        // Get active FAQs for this language
        $faqs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$faq_table} WHERE is_active = 1 AND language = %s",
            $language
        ));
        
        if (empty($faqs)) {
            return null;
        }
        
        $message_lower = mb_strtolower($message);
        
        foreach ($faqs as $faq) {
            $keywords = explode(',', $faq->keywords);
            foreach ($keywords as $keyword) {
                $keyword = trim(mb_strtolower($keyword));
                if (strpos($message_lower, $keyword) !== false) {
                    return $faq->answer;
                }
            }
        }
        
        return null;
    }
    
    public function ajax_get_messages() {
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_frontend_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
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
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
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
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
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
                'status' => $conversation->status,
                'user_typing' => intval($conversation->user_typing),
                'user_typing_at' => $conversation->user_typing_at
            )
        ));
    }
    
    public function ajax_admin_send_message() {
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
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
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
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
    
    public function ajax_admin_typing() {
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
        global $wpdb;
        
        $conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
        $is_typing = isset($_POST['is_typing']) ? intval($_POST['is_typing']) : 0;
        
        if ($conversation_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid conversation_id'));
            return;
        }
        
        $wpdb->update(
            $this->table_conversations,
            array(
                'admin_typing' => $is_typing,
                'admin_typing_at' => current_time('mysql')
            ),
            array('id' => $conversation_id),
            array('%d', '%s'),
            array('%d')
        );
        
        wp_send_json_success();
    }
    
    public function ajax_user_typing() {
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_frontend_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
        global $wpdb;
        
        $conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
        $is_typing = isset($_POST['is_typing']) ? intval($_POST['is_typing']) : 0;
        
        if ($conversation_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid conversation_id'));
            return;
        }
        
        $wpdb->update(
            $this->table_conversations,
            array(
                'user_typing' => $is_typing,
                'user_typing_at' => current_time('mysql')
            ),
            array('id' => $conversation_id),
            array('%d', '%s'),
            array('%d')
        );
        
        wp_send_json_success();
    }
    
    public function ajax_export_conversation() {
        // Verify nonce, but don't die on failure - return error instead
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            $this->log('Export failed: Nonce verification failed', 'error');
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
        global $wpdb;
        
        // Enhanced logging for debugging
        $this->log('Export conversation request received. POST data: ' . json_encode($_POST), 'info');
        
        $conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
        
        // Detailed validation logging
        if (!isset($_POST['conversation_id'])) {
            $this->log('Export failed: conversation_id parameter is missing from POST data', 'error');
            wp_send_json_error(array('message' => 'Отсутствует параметр conversation_id'));
            return;
        }
        
        if ($conversation_id <= 0) {
            $this->log("Export failed: Invalid conversation ID received: '{$_POST['conversation_id']}' (parsed as {$conversation_id})", 'error');
            wp_send_json_error(array('message' => 'Неверный ID диалога'));
            return;
        }
        
        $this->log("Export: Processing conversation ID {$conversation_id}", 'info');
        
        $conversation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_conversations} WHERE id = %d", 
            $conversation_id
        ));
        
        if (!$conversation) {
            $this->log("Export failed: Conversation {$conversation_id} not found", 'error');
            wp_send_json_error(array('message' => 'Диалог не найден'));
            return;
        }
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_messages} 
            WHERE conversation_id = %d 
            ORDER BY created_at ASC",
            $conversation_id
        ));
        
        if ($messages === null) {
            $this->log("Export failed: Database error - " . $wpdb->last_error, 'error');
            wp_send_json_error(array('message' => 'Ошибка базы данных'));
            return;
        }
        
        if (empty($messages)) {
            $this->log("Export warning: No messages in conversation {$conversation_id}", 'warning');
            wp_send_json_error(array('message' => 'В диалоге нет сообщений'));
            return;
        }
        
        // Generate CSV content with proper UTF-8 encoding and escaping
        // Add UTF-8 BOM (Byte Order Mark) to ensure proper encoding of Cyrillic characters
        $csv_output = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv_output .= "Дата,Время,Отправитель,Сообщение,Перевод\n";
        
        foreach ($messages as $msg) {
            $date = date('Y-m-d', strtotime($msg->created_at));
            $time = date('H:i:s', strtotime($msg->created_at));
            $sender = $msg->sender_type === 'admin' ? 'Администратор' : ($conversation->user_name ?: 'Гость');
            
            // Properly escape CSV fields
            $message = str_replace('"', '""', $msg->message_text ?: '');
            $translation = $msg->translated_text ? str_replace('"', '""', $msg->translated_text) : '';
            
            $csv_output .= "\"{$date}\",\"{$time}\",\"{$sender}\",\"{$message}\",\"{$translation}\"\n";
        }
        
        $encoded_csv = base64_encode($csv_output);
        if ($encoded_csv === false) {
            $this->log('Export failed: Could not encode CSV', 'error');
            wp_send_json_error(array('message' => 'Ошибка кодирования CSV'));
            return;
        }
        
        $filename = "conversation_{$conversation_id}_" . date('Y-m-d_His') . ".csv";
        
        $this->log("Export successful: {$filename} (" . count($messages) . " messages)", 'info');
        
        // Return CSV as base64 for download
        wp_send_json_success(array(
            'csv' => $encoded_csv,
            'filename' => $filename,
            'message_count' => count($messages)
        ));
    }
    
    public function ajax_toggle_faq() {
        // Verify nonce
        if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
            return;
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $faq_table = $wpdb->prefix . 'ai_chat_faq';
        
        $faq_id = isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0;
        
        if ($faq_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid FAQ ID'));
            return;
        }
        
        // Get current FAQ state
        $faq = $wpdb->get_row($wpdb->prepare("SELECT id, is_active FROM {$faq_table} WHERE id = %d", $faq_id));
        
        if (!$faq) {
            wp_send_json_error(array('message' => 'FAQ not found'));
            return;
        }
        
        // Toggle the is_active state
        $new_state = $faq->is_active ? 0 : 1;
        
        $result = $wpdb->update(
            $faq_table,
            array(
                'is_active' => $new_state,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $faq_id),
            array('%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        wp_send_json_success(array(
            'message' => 'FAQ status updated',
            'is_active' => $new_state
        ));
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
        
        // Conversation history for logged-in users
        register_rest_route('ai-chat/v1', '/user/history', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_user_history'),
            'permission_callback' => '__return_true'
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
    
    public function rest_get_user_history($request) {
        global $wpdb;
        
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', 'Необходима авторизация', array('status' => 401));
        }
        
        $user_id = get_current_user_id();
        
        $conversations = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, 
            (SELECT COUNT(*) FROM {$this->table_messages} WHERE conversation_id = c.id) as message_count
            FROM {$this->table_conversations} c 
            WHERE c.user_id = %d 
            ORDER BY c.updated_at DESC",
            $user_id
        ));
        
        return rest_ensure_response($conversations);
    }
    
        private function translate_message($text, $from_lang, $to_lang) {
        // Skip translation if text contains API key patterns
        if ($this->contains_api_key($text)) {
            $this->log('Пропуск перевода: обнаружен API ключ в тексте', 'warning');
            return null;
        }
        
        // Check cache first
        $cached_translation = $this->get_cached_translation($text, $from_lang, $to_lang);
        if ($cached_translation !== null) {
            $this->log('Использован кэшированный перевод');
            return $cached_translation;
        }
        
        $api_key = get_option('aic_ai_api_key', '');
        
        if (empty($api_key)) {
            $this->log('API ключ не настроен, перевод пропущен', 'warning');
            return null;
        }
        
        $provider = get_option('aic_ai_provider', 'openai');
        
        try {
            $translation = null;
            switch ($provider) {
                case 'openai':
                    $translation = $this->translate_openai($text, $from_lang, $to_lang, $api_key);
                    break;
                    
                case 'anthropic':
                    $translation = $this->translate_anthropic($text, $from_lang, $to_lang, $api_key);
                    break;
                    
                case 'google':
                    $translation = $this->translate_google($text, $from_lang, $to_lang, $api_key);
                    break;
                    
                default:
                    $this->log('Неизвестный провайдер: ' . $provider, 'error');
                    return null;
            }
            
            // Cache the translation
            if ($translation !== null) {
                $this->cache_translation($text, $from_lang, $to_lang, $translation);
            }
            
            return $translation;
        } catch (Exception $e) {
            $this->log('Ошибка перевода: ' . $e->getMessage(), 'error');
            return null;
        }
    }
    
    private function get_cached_translation($text, $from_lang, $to_lang) {
        global $wpdb;
        $cache_table = $wpdb->prefix . 'ai_chat_translation_cache';
        
        $text_hash = hash('sha256', $text);
        
        $cached = $wpdb->get_var($wpdb->prepare(
            "SELECT translated_text FROM {$cache_table} 
            WHERE text_hash = %s AND source_language = %s AND target_language = %s",
            $text_hash, $from_lang, $to_lang
        ));
        
        return $cached;
    }
    
    private function cache_translation($text, $from_lang, $to_lang, $translation) {
        global $wpdb;
        $cache_table = $wpdb->prefix . 'ai_chat_translation_cache';
        
        $text_hash = hash('sha256', $text);
        
        $wpdb->insert(
            $cache_table,
            array(
                'source_text' => $text,
                'source_language' => $from_lang,
                'target_language' => $to_lang,
                'translated_text' => $translation,
                'text_hash' => $text_hash
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }
    
    private function contains_api_key($text) {
        // Patterns for common API key formats
        $api_key_patterns = array(
            '/sk-[a-zA-Z0-9]{32,}/',           // OpenAI keys (sk-...)
            '/aic_[a-zA-Z0-9]{20,}/',          // Plugin mobile API keys (aic_...)
            '/AIzaSy[a-zA-Z0-9_-]{33}/',       // Google API keys
            '/[a-zA-Z0-9]{32,64}/',            // Generic long alphanumeric strings (potential keys)
            '/Bearer\s+[a-zA-Z0-9._-]+/i',     // Bearer tokens
            '/api[_-]?key[:\s=]+[a-zA-Z0-9]+/i', // Explicit API key mentions
        );
        
        foreach ($api_key_patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
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
            /* translators: %s: site name */
            $subject = sprintf(__('[%s] New chat message', 'ai-multilingual-chat'), $site_name);
            /* translators: 1: user name, 2: message excerpt, 3: admin URL */
            $message = sprintf(
                __("New message from %1\$s:\n\n%2\$s\n\nOpen: %3\$s", 'ai-multilingual-chat'),
                $conversation->user_name ?: __('Guest', 'ai-multilingual-chat'),
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

register_activation_hook(__FILE__, array('AI_Multilingual_Chat', 'activate_plugin'));

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
    // Verify nonce, but don't die on failure - return error instead
    if (!check_ajax_referer('aic_admin_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
        return;
    }
    
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

