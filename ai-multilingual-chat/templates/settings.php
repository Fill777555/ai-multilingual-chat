<?php
if (!defined('ABSPATH')) exit;

// Global settings
$api_key = get_option('aic_ai_api_key', '');
$provider = get_option('aic_ai_provider', 'openai');
$admin_lang = get_option('aic_admin_language', 'ru');
$enable_translation = get_option('aic_enable_translation', '1');
$mobile_api_key = get_option('aic_mobile_api_key', '');
$widget_position = get_option('aic_chat_widget_position', 'bottom-right');
$widget_color = get_option('aic_chat_widget_color', '#18adfe');
$enable_emoji = get_option('aic_enable_emoji_picker', '1');
$enable_dark_theme = get_option('aic_enable_dark_theme', '0');
$enable_sound = get_option('aic_enable_sound_notifications', '1');

// Color settings
$widget_bg_color = get_option('aic_widget_bg_color', '#1c2126');
$chat_button_color = get_option('aic_chat_button_color', '#18adfe');
$header_bg_color = get_option('aic_header_bg_color', '#18adfe');
$user_msg_bg_color = get_option('aic_user_msg_bg_color', '#18adfe');
$admin_msg_bg_color = get_option('aic_admin_msg_bg_color', '#ffffff');
$user_msg_text_color = get_option('aic_user_msg_text_color', '#ffffff');
$admin_msg_text_color = get_option('aic_admin_msg_text_color', '#333333');
$send_button_color = get_option('aic_send_button_color', '#18adfe');
$input_border_color = get_option('aic_input_border_color', '#dddddd');

// Header color settings
$header_text_color = get_option('aic_header_text_color', '#ffffff');
$header_status_color = get_option('aic_header_status_color', '#ffffff');
$header_icons_color = get_option('aic_header_icons_color', '#ffffff');
$header_close_color = get_option('aic_header_close_color', '#ffffff');

// Design settings
$widget_border_radius = get_option('aic_widget_border_radius', '12');
$widget_font_size = get_option('aic_widget_font_size', '14');
$widget_padding = get_option('aic_widget_padding', '20');
$widget_custom_css = get_option('aic_widget_custom_css', '');
?>

<div class="wrap">
    <h1><?php echo esc_html__('AI Multilingual Chat Settings', 'ai-multilingual-chat'); ?></h1>
    
    <!-- Tab Navigation -->
    <h2 class="nav-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general"><?php echo esc_html__('General Settings', 'ai-multilingual-chat'); ?></a>
        <a href="#frontend-design" class="nav-tab" data-tab="frontend-design"><?php echo esc_html__('Widget Design', 'ai-multilingual-chat'); ?></a>
        <a href="#api" class="nav-tab" data-tab="api"><?php echo esc_html__('REST API', 'ai-multilingual-chat'); ?></a>
    </h2>
    
    <form method="post" action="">
        <?php wp_nonce_field('aic_settings_nonce'); ?>
        
        <!-- General Settings Tab -->
        <div id="tab-general" class="aic-settings-tab aic-settings-tab-active">
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_ai_provider"><?php echo esc_html__('AI Provider', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <select name="aic_ai_provider" id="aic_ai_provider" class="regular-text">
                        <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI (GPT-3.5/GPT-4)</option>
                        <option value="anthropic" <?php selected($provider, 'anthropic'); ?>>Anthropic Claude</option>
                        <option value="google" <?php selected($provider, 'google'); ?>>Google AI (Gemini)</option>
                    </select>
                    <p class="description"><?php echo esc_html__('Select the provider for message translation', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_ai_api_key"><?php echo esc_html__('AI API Key', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="aic_ai_api_key" 
                           id="aic_ai_api_key" 
                           value="<?php echo esc_attr($api_key); ?>" 
                           class="regular-text">
                    <p class="description">
                        <?php echo esc_html__('Get your key:', 'ai-multilingual-chat'); ?> 
                        <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a> | 
                        <a href="https://console.anthropic.com/" target="_blank">Anthropic</a> | 
                        <a href="https://makersuite.google.com/" target="_blank">Google</a>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_language"><?php echo esc_html__('Administrator Language', 'ai-multilingual-chat'); ?></label>
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
                    <p class="description"><?php echo esc_html__('The language in which you communicate with clients', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_translation"><?php echo esc_html__('Automatic Translation', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_translation" 
                               id="aic_enable_translation" 
                               value="1" 
                               <?php checked($enable_translation, '1'); ?>>
                        <?php echo esc_html__('Enable automatic translation via AI', 'ai-multilingual-chat'); ?>
                    </label>
                    <p class="description"><?php echo esc_html__('If disabled, messages will not be translated', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_mobile_api_key"><?php echo esc_html__('API Key for Mobile App', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="aic_mobile_api_key" 
                           id="aic_mobile_api_key" 
                           value="<?php echo esc_attr($mobile_api_key); ?>" 
                           class="regular-text"
                           readonly>
                    <button type="button" id="aic_generate_api_key" class="aic-button">
                        <span class="dashicons dashicons-update"></span> <?php echo esc_html__('Generate', 'ai-multilingual-chat'); ?>
                    </button>
                    <button type="button" id="aic_copy_api_key" class="aic-button">
                        <span class="dashicons dashicons-admin-page"></span> <?php echo esc_html__('Copy', 'ai-multilingual-chat'); ?>
                    </button>
                    <p class="description"><?php echo esc_html__('Used for REST API access', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_chat_widget_position"><?php echo esc_html__('Chat Widget Position', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <select name="aic_chat_widget_position" id="aic_chat_widget_position" class="regular-text">
                        <option value="bottom-right" <?php selected($widget_position, 'bottom-right'); ?>><?php echo esc_html__('Bottom Right', 'ai-multilingual-chat'); ?></option>
                        <option value="bottom-left" <?php selected($widget_position, 'bottom-left'); ?>><?php echo esc_html__('Bottom Left', 'ai-multilingual-chat'); ?></option>
                        <option value="top-right" <?php selected($widget_position, 'top-right'); ?>><?php echo esc_html__('Top Right', 'ai-multilingual-chat'); ?></option>
                        <option value="top-left" <?php selected($widget_position, 'top-left'); ?>><?php echo esc_html__('Top Left', 'ai-multilingual-chat'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_emoji_picker"><?php echo esc_html__('Emoji Picker', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_emoji_picker" 
                               id="aic_enable_emoji_picker" 
                               value="1" 
                               <?php checked($enable_emoji, '1'); ?>>
                        <?php echo esc_html__('Enable emoji picker in chat', 'ai-multilingual-chat'); ?>
                    </label>
                </td>
            </tr>
            
            <!--<tr>
                <th scope="row">
                    <label for="aic_enable_dark_theme"><?php echo esc_html__('Dark Theme', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_dark_theme" 
                               id="aic_enable_dark_theme" 
                               value="1" 
                               <?php checked($enable_dark_theme, '1'); ?>>
                        <?php echo esc_html__('Enable dark theme for chat', 'ai-multilingual-chat'); ?>
                    </label>
                </td>
            </tr>-->
             
            <tr>
                <th scope="row">
                    <label for="aic_theme_mode"><?php echo esc_html__('Admin Panel Theme', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <?php
                    $theme_mode = get_option('aic_theme_mode', 'auto');
                    ?>
                    <select name="aic_theme_mode" id="aic_theme_mode" style="min-width:140px;">
                        <option value="light" <?php selected($theme_mode, 'light'); ?>><?php echo esc_html__('Light', 'ai-multilingual-chat'); ?></option>
                        <option value="dark"  <?php selected($theme_mode, 'dark'); ?>><?php echo esc_html__('Dark', 'ai-multilingual-chat'); ?></option>
                        <option value="auto"  <?php selected($theme_mode, 'auto'); ?>><?php echo esc_html__('Auto (System)', 'ai-multilingual-chat'); ?></option>
                    </select>
                    <p class="description"><?php echo esc_html__('Choose theme for plugin admin panel. "Auto" uses your device system theme.', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_enable_sound_notifications"><?php echo esc_html__('Sound Notifications', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="aic_enable_sound_notifications" 
                               id="aic_enable_sound_notifications" 
                               value="1" 
                               <?php checked($enable_sound, '1'); ?>>
                        <?php echo esc_html__('Enable sound notifications in admin panel and for clients', 'ai-multilingual-chat'); ?>
                    </label>
                    <p class="description">
                        <?php echo esc_html__('Administrators hear sound for new messages from clients, clients hear sound for admin replies. Clients can disable sound via button in chat.', 'ai-multilingual-chat'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_notification_sound"><?php echo esc_html__('Notification Sound', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <select id="aic_admin_notification_sound" name="aic_admin_notification_sound">
                        <?php
                        $current_sound = get_option('aic_admin_notification_sound', 'default');
                        $sounds = array(
                            'default' => __('Default', 'ai-multilingual-chat'),
                            'bell' => __('Bell', 'ai-multilingual-chat'),
                            'ding' => __('Ding', 'ai-multilingual-chat'),
                            'chime' => __('Chime', 'ai-multilingual-chat'),
                            'soft' => __('Soft', 'ai-multilingual-chat')
                        );
                        foreach ($sounds as $key => $label) {
                            $selected = ($current_sound === $key) ? 'selected' : '';
                            echo "<option value=\"{$key}\" {$selected}>{$label}</option>";
                        }
                        ?>
                    </select>
                    <button type="button" class="aic-button" id="aic_preview_admin_sound" style="margin-left: 10px;">
                        <span class="dashicons dashicons-controls-volumeon"></span> <?php echo esc_html__('Preview', 'ai-multilingual-chat'); ?>
                    </button>
                    <p class="description"><?php echo esc_html__('Choose melody for sound notifications in admin panel', 'ai-multilingual-chat'); ?></p>
                    
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
                    <label for="aic_client_notification_sound"><?php echo esc_html__('Client Notification Sound', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <select id="aic_client_notification_sound" name="aic_client_notification_sound">
                        <?php
                        $current_client_sound = get_option('aic_client_notification_sound', 'default');
                        $sounds = array(
                            'default' => __('Default', 'ai-multilingual-chat'),
                            'bell' => __('Bell', 'ai-multilingual-chat'),
                            'ding' => __('Ding', 'ai-multilingual-chat'),
                            'chime' => __('Chime', 'ai-multilingual-chat'),
                            'soft' => __('Soft', 'ai-multilingual-chat')
                        );
                        foreach ($sounds as $key => $label) {
                            $selected = ($current_client_sound === $key) ? 'selected' : '';
                            echo "<option value=\"{$key}\" {$selected}>{$label}</option>";
                        }
                        ?>
                    </select>
                    <button type="button" class="aic-button" id="aic_preview_client_sound" style="margin-left: 10px;">
                        <span class="dashicons dashicons-controls-volumeon"></span> <?php echo esc_html__('Preview', 'ai-multilingual-chat'); ?>
                    </button>
                    <p class="description"><?php echo esc_html__('Choose melody for client sound notifications in chat', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_avatar"><?php echo esc_html__('Administrator Avatar', 'ai-multilingual-chat'); ?></label>
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
                        <span class="dashicons dashicons-upload"></span> <?php echo esc_html__('Upload Image', 'ai-multilingual-chat'); ?>
                    </button>
                    <button type="button" id="aic_remove_avatar" class="aic-button" <?php echo empty($admin_avatar) ? 'style="display:none;"' : ''; ?>>
                        <span class="dashicons dashicons-no"></span> <?php echo esc_html__('Remove', 'ai-multilingual-chat'); ?>
                    </button>
                    <p class="description"><?php echo esc_html__('Avatar will be displayed next to administrator messages in chat. Recommended size: 100x100 pixels', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
        </table>
        </div>
        
        <!-- Frontend Design Tab -->
        <div id="tab-frontend-design" class="aic-settings-tab" style="display: none;">
        
        <h3><?php echo esc_html__('Color Settings', 'ai-multilingual-chat'); ?></h3>
        <p class="description" style="margin-bottom: 15px;"><?php echo esc_html__('Configure colors for different chat widget elements', 'ai-multilingual-chat'); ?></p>
        
        <h4><?php echo esc_html__('Main Widget Colors', 'ai-multilingual-chat'); ?></h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_widget_bg_color"><?php echo esc_html__('Widget Background Color', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_widget_bg_color" 
                           id="aic_widget_bg_color" 
                           value="<?php echo esc_attr($widget_bg_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($widget_bg_color); ?>"
                           placeholder="#000000">
                    <p class="description"><?php echo esc_html__('Background color of the chat window', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_chat_button_color">Цвет кнопки открытия чата</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_chat_button_color" 
                           id="aic_chat_button_color" 
                           value="<?php echo esc_attr($chat_button_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($chat_button_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет круглой кнопки для открытия чата</p>
                </td>
            </tr>
        </table>
        
        <h4>Цвета заголовка чата</h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_header_bg_color">Цвет фона заголовка</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_header_bg_color" 
                           id="aic_header_bg_color" 
                           value="<?php echo esc_attr($header_bg_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($header_bg_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет фона заголовка чата</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_header_text_color">Цвет текста заголовка</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_header_text_color" 
                           id="aic_header_text_color" 
                           value="<?php echo esc_attr($header_text_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($header_text_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет текста заголовка чата</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_header_status_color">Цвет текста статуса</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_header_status_color" 
                           id="aic_header_status_color" 
                           value="<?php echo esc_attr($header_status_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($header_status_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет текста статуса ('Мы онлайн')</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_header_icons_color">Цвет иконок кнопок</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_header_icons_color" 
                           id="aic_header_icons_color" 
                           value="<?php echo esc_attr($header_icons_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($header_icons_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет иконок кнопок в заголовке</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_header_close_color">Цвет кнопки закрытия</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_header_close_color" 
                           id="aic_header_close_color" 
                           value="<?php echo esc_attr($header_close_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($header_close_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет кнопки закрытия (×)</p>
                </td>
            </tr>
        </table>
        
        <h4>Цвета сообщений</h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_user_msg_bg_color">Цвет сообщений пользователя</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_user_msg_bg_color" 
                           id="aic_user_msg_bg_color" 
                           value="<?php echo esc_attr($user_msg_bg_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($user_msg_bg_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет фона сообщений от пользователя</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_msg_bg_color">Цвет сообщений администратора</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_admin_msg_bg_color" 
                           id="aic_admin_msg_bg_color" 
                           value="<?php echo esc_attr($admin_msg_bg_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($admin_msg_bg_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет фона сообщений от администратора/бота</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_user_msg_text_color">Цвет текста сообщений пользователя</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_user_msg_text_color" 
                           id="aic_user_msg_text_color" 
                           value="<?php echo esc_attr($user_msg_text_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($user_msg_text_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет текста в сообщениях пользователя</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_admin_msg_text_color">Цвет текста сообщений администратора</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_admin_msg_text_color" 
                           id="aic_admin_msg_text_color" 
                           value="<?php echo esc_attr($admin_msg_text_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($admin_msg_text_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет текста в сообщениях администратора</p>
                </td>
            </tr>
        </table>
        
        <h4>Цвета элементов управления</h4>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_send_button_color">Цвет кнопки отправки</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_send_button_color" 
                           id="aic_send_button_color" 
                           value="<?php echo esc_attr($send_button_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($send_button_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет кнопки отправки сообщения</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_input_border_color">Цвет границы поля ввода</label>
                </th>
                <td>
                    <input type="color" 
                           name="aic_input_border_color" 
                           id="aic_input_border_color" 
                           value="<?php echo esc_attr($input_border_color); ?>"
                           class="aic-color-picker">
                    <input type="text" 
                           class="aic-color-hex-input" 
                           value="<?php echo esc_attr($input_border_color); ?>"
                           placeholder="#000000">
                    <p class="description">Цвет границы поля ввода сообщения</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"></th>
                <td>
                    <button type="button" id="aic_reset_colors" class="aic-button" style="background: #dc3545; margin-top: 10px;">
                        <span class="dashicons dashicons-image-rotate"></span> Сбросить цвета к значениям по умолчанию
                    </button>
                    <p class="description">Вернуть все цвета к исходным значениям</p>
                </td>
            </tr>
        </table>
        
        <h3 style="margin-top: 30px;"><?php echo esc_html__('Widget Parameters', 'ai-multilingual-chat'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aic_widget_border_radius"><?php echo esc_html__('Border Radius (px)', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="aic_widget_border_radius" 
                           id="aic_widget_border_radius" 
                           value="<?php echo esc_attr($widget_border_radius); ?>" 
                           min="0" 
                           max="50"
                           class="small-text">
                    <p class="description"><?php echo esc_html__('Border radius of chat window corners (0-50px). Default: 12px', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_widget_font_size"><?php echo esc_html__('Font Size (px)', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="aic_widget_font_size" 
                           id="aic_widget_font_size" 
                           value="<?php echo esc_attr($widget_font_size); ?>" 
                           min="10" 
                           max="24"
                           class="small-text">
                    <p class="description"><?php echo esc_html__('Font size in chat (10-24px). Default: 14px', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_widget_padding"><?php echo esc_html__('Padding (px)', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           name="aic_widget_padding" 
                           id="aic_widget_padding" 
                           value="<?php echo esc_attr($widget_padding); ?>" 
                           min="5" 
                           max="40"
                           class="small-text">
                    <p class="description"><?php echo esc_html__('Inner padding of chat header (5-40px). Default: 20px', 'ai-multilingual-chat'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="aic_widget_custom_css"><?php echo esc_html__('Custom CSS', 'ai-multilingual-chat'); ?></label>
                </th>
                <td>
                    <textarea 
                        name="aic_widget_custom_css" 
                        id="aic_widget_custom_css" 
                        rows="10" 
                        class="large-text code"
                        placeholder="/* <?php echo esc_attr__('Enter your CSS code here', 'ai-multilingual-chat'); ?> */&#10;#aic-chat-widget .aic-chat-window {&#10;    /* your custom styles */&#10;}"><?php echo esc_textarea($widget_custom_css); ?></textarea>
                    <p class="description">
                        <?php echo esc_html__('Add your own CSS code for full control over widget design.', 'ai-multilingual-chat'); ?><br>
                        <?php echo esc_html__('Examples: change colors, sizes, padding, animations, etc.', 'ai-multilingual-chat'); ?><br>
                        <strong><?php echo esc_html__('Warning:', 'ai-multilingual-chat'); ?></strong> <?php echo esc_html__('Use carefully, incorrect CSS may break widget functionality.', 'ai-multilingual-chat'); ?>
                    </p>
                </td>
            </tr>
        </table>
        </div>
        
        <!-- REST API Tab -->
        <div id="tab-api" class="aic-settings-tab" style="display: none;">
        <h2><?php echo esc_html__('REST API for Mobile Application', 'ai-multilingual-chat'); ?></h2>
        
        <p><?php echo esc_html__('Use these endpoints for mobile application integration:', 'ai-multilingual-chat'); ?></p>
        
        <table class="widefat" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Method', 'ai-multilingual-chat'); ?></th>
                    <th><?php echo esc_html__('Endpoint', 'ai-multilingual-chat'); ?></th>
                    <th><?php echo esc_html__('Description', 'ai-multilingual-chat'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>GET</code></td>
                    <td><code><?php echo rest_url('ai-chat/v1/conversations'); ?></code></td>
                    <td><?php echo esc_html__('Get list of conversations', 'ai-multilingual-chat'); ?></td>
                </tr>
                <tr>
                    <td><code>GET</code></td>
                    <td><code><?php echo rest_url('ai-chat/v1/messages/{id}'); ?></code></td>
                    <td><?php echo esc_html__('Get conversation messages', 'ai-multilingual-chat'); ?></td>
                </tr>
                <tr>
                    <td><code>POST</code></td>
                    <td><code><?php echo rest_url('ai-chat/v1/send'); ?></code></td>
                    <td><?php echo esc_html__('Send message', 'ai-multilingual-chat'); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p style="margin-top: 15px;">
            <strong><?php echo esc_html__('Important:', 'ai-multilingual-chat'); ?></strong> <?php echo esc_html__('All requests must contain X-API-Key header with API key.', 'ai-multilingual-chat'); ?>
        </p>
        </div>
        
        <?php submit_button(__('Save Settings', 'ai-multilingual-chat'), 'primary', 'aic_save_settings'); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var targetTab = $(this).data('tab');
        
        // Update tab navigation
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Update tab content
        $('.aic-settings-tab').hide().removeClass('aic-settings-tab-active');
        $('#tab-' + targetTab).show().addClass('aic-settings-tab-active');
    });
    
    // Generate API key
    $('#aic_generate_api_key').on('click', function() {
        const key = 'aic_' + Math.random().toString(36).substring(2, 15) + 
                    Math.random().toString(36).substring(2, 15) +
                    Math.random().toString(36).substring(2, 15);
        $('#aic_mobile_api_key').val(key).prop('readonly', false);
    });
    
    // Copy API key
    $('#aic_copy_api_key').on('click', function() {
        const apiKey = $('#aic_mobile_api_key').val();
        if (!apiKey) {
            alert('<?php echo esc_js(__('Please generate API key first', 'ai-multilingual-chat')); ?>');
            return;
        }
        
        navigator.clipboard.writeText(apiKey).then(function() {
            const btn = $('#aic_copy_api_key');
            const originalText = btn.html();
            btn.html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js(__('Copied!', 'ai-multilingual-chat')); ?>');
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
            title: '<?php echo esc_js(__('Select Administrator Avatar', 'ai-multilingual-chat')); ?>',
            button: {
                text: '<?php echo esc_js(__('Use This Image', 'ai-multilingual-chat')); ?>'
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
    
    // Color picker - update HEX input field
    $('.aic-color-picker').on('change input', function() {
        var colorValue = $(this).val();
        $(this).siblings('.aic-color-hex-input').val(colorValue);
    });
    
    // HEX input - update color picker and validate
    $('.aic-color-hex-input').on('input', function() {
        var hexValue = $(this).val().trim();
        // Validate HEX: #RRGGBB
        if (/^#[0-9A-Fa-f]{6}$/.test(hexValue)) {
            $(this).siblings('.aic-color-picker').val(hexValue);
            $(this).css('border-color', '#4CAF50'); // Green border for valid
        } else {
            $(this).css('border-color', '#f44336'); // Red border for invalid
        }
    });
    
    // Normalize HEX on blur
    $('.aic-color-hex-input').on('blur', function() {
        var hexValue = $(this).val().trim().toUpperCase();
        // Add # if missing
        if (!/^#/.test(hexValue) && /^[0-9A-F]{6}$/.test(hexValue)) {
            hexValue = '#' + hexValue;
            $(this).val(hexValue);
        }
        // Update color picker if valid
        if (/^#[0-9A-F]{6}$/.test(hexValue)) {
            $(this).siblings('.aic-color-picker').val(hexValue);
            $(this).css('border-color', '');
        }
    });
    
    // Reset colors to defaults
    $('#aic_reset_colors').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to reset all colors to default values?', 'ai-multilingual-chat')); ?>')) {
            return;
        }
        
        var defaults = {
            'aic_widget_bg_color': '#1c2126',
            'aic_chat_button_color': '#18adfe',
            'aic_header_bg_color': '#18adfe',
            'aic_header_text_color': '#ffffff',
            'aic_header_status_color': '#ffffff',
            'aic_header_icons_color': '#ffffff',
            'aic_header_close_color': '#ffffff',
            'aic_user_msg_bg_color': '#18adfe',
            'aic_admin_msg_bg_color': '#ffffff',
            'aic_user_msg_text_color': '#ffffff',
            'aic_admin_msg_text_color': '#333333',
            'aic_send_button_color': '#18adfe',
            'aic_input_border_color': '#18adfe'
        };
        
        $.each(defaults, function(id, color) {
            $('#' + id).val(color);
            $('#' + id).siblings('.aic-color-hex-input').val(color);
        });
        
        alert('Цвета сброшены к значениям по умолчанию. Не забудьте сохранить настройки.');
    });
});
</script>

<style>
.nav-tab-wrapper {
    margin-bottom: 20px;
}

.aic-settings-tab {
    padding: 20px 0;
}

.form-table th {
    width: 250px;
}

.nav-tab {
    border-radius: 8px 8px 0 0;
    border: none !important;
    background: var(--aic-muted) !important;
    color: #fff;
}
.nav-tab-active {
    border-radius: 8px 8px 0 0;
    border: none !important;
    background: var(--aic-accent) !important;
    color: #fff !important;
}

.comment-ays, .feature-filter, .popular-tags, .stuffbox, .widgets-holder-wrap, .wp-editor-container, p.popular-tags, table.widefat {
    background: var(--aic-bg);
}

/* Color picker styles */
.aic-color-picker {
    width: 80px;
    height: 40px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    vertical-align: middle;
}

.aic-color-hex-input {
    display: inline-block;
    width: 120px;
    padding: 6px 10px;
    margin-left: 10px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    text-transform: uppercase;
    border: 1px solid var(--aic-accent);
    border-radius: 4px;
    background: var(--aic-bg);
    color: var(--aic-text-primary);
    vertical-align: middle;
    transition: border-color 0.3s;
}

.aic-color-hex-input:focus {
    outline: none;
    border-color: var(--aic-accent);
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
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
  background: var(--aic-hover);
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
  outline: 2px solid var(--aic-hover);
  outline-offset: 2px;
}

</style>
