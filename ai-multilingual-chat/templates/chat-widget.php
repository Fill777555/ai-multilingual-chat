<?php
if (!defined('ABSPATH')) exit;

$position = get_option('aic_chat_widget_position', 'bottom-right');
$color = get_option('aic_chat_widget_color', '#667eea');
$border_radius = get_option('aic_widget_border_radius', '12');
$font_size = get_option('aic_widget_font_size', '14');
$padding = get_option('aic_widget_padding', '20');
$custom_css = get_option('aic_widget_custom_css', '');

// New color settings
$widget_bg_color = get_option('aic_widget_bg_color', '#1c2126');
$chat_button_color = get_option('aic_chat_button_color', '#667eea');
$header_bg_color = get_option('aic_header_bg_color', '#667eea');
$user_msg_bg_color = get_option('aic_user_msg_bg_color', '#667eea');
$admin_msg_bg_color = get_option('aic_admin_msg_bg_color', '#ffffff');
$user_msg_text_color = get_option('aic_user_msg_text_color', '#ffffff');
$admin_msg_text_color = get_option('aic_admin_msg_text_color', '#333333');
$send_button_color = get_option('aic_send_button_color', '#667eea');
$input_border_color = get_option('aic_input_border_color', '#dddddd');

// New header color settings
$header_text_color = get_option('aic_header_text_color', '#ffffff');
$header_status_color = get_option('aic_header_status_color', '#ffffff');
$header_icons_color = get_option('aic_header_icons_color', '#ffffff');
$header_close_color = get_option('aic_header_close_color', '#ffffff');
?>

<div id="aic-chat-widget" class="aic-widget-<?php echo esc_attr($position); ?>" style="--widget-color: <?php echo esc_attr($color); ?>; --widget-border-radius: <?php echo esc_attr($border_radius); ?>px; --widget-font-size: <?php echo esc_attr($font_size); ?>px; --widget-padding: <?php echo esc_attr($padding); ?>px; --widget-bg-color: <?php echo esc_attr($widget_bg_color); ?>; --chat-button-color: <?php echo esc_attr($chat_button_color); ?>; --header-bg-color: <?php echo esc_attr($header_bg_color); ?>; --header-text-color: <?php echo esc_attr($header_text_color); ?>; --header-status-color: <?php echo esc_attr($header_status_color); ?>; --header-icons-color: <?php echo esc_attr($header_icons_color); ?>; --header-close-color: <?php echo esc_attr($header_close_color); ?>; --user-msg-bg-color: <?php echo esc_attr($user_msg_bg_color); ?>; --admin-msg-bg-color: <?php echo esc_attr($admin_msg_bg_color); ?>; --user-msg-text-color: <?php echo esc_attr($user_msg_text_color); ?>; --admin-msg-text-color: <?php echo esc_attr($admin_msg_text_color); ?>; --send-button-color: <?php echo esc_attr($send_button_color); ?>; --input-border-color: <?php echo esc_attr($input_border_color); ?>;">
    <button id="aic-chat-button" class="aic-chat-button">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span id="aic-unread-badge" class="aic-unread-badge" style="display: none;">0</span>
    </button>
    
    <div id="aic-chat-window" class="aic-chat-window" style="display: none;">
        <div class="aic-chat-header">
            <img width="50" height="50" src="https://web-proekt.com/wp-content/uploads/2020/09/MARKER-WEB-PROekt-blue.svg" class="attachment-large size-large" alt="" loading="lazy" decoding="async">
            <div>
                <h3 id="aic-chat_support-header">Support chat</h3>
                <p class="aic-chat-status" id="aic-online-header">We are online</p>
            </div>

            <div style="display: flex; gap: 5px; align-items: center;">
                <button id="aic-sound-toggle" class="aic-icon-button" title="Toggle sound notifications">
                    <span class="aic-sound-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path class="sound-waves" d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                        <span class="sound-mute-line"></span>
                    </span>
                </button>
                <button id="aic-chat-close" class="aic-chat-close">&times;</button>
            </div>
        </div>
        
        <div id="aic-welcome-screen" class="aic-welcome-screen">
            <div class="aic-welcome-content">
                <h3 id="aic-welcome-heading">Welcome!</h3>
                <p id="aic-welcome-text">Please introduce yourself to start the chat</p>
                <input type="text" 
                       id="aic-user-name" 
                       class="aic-input" 
                       placeholder="Your name">
                <select id="aic-user-language" class="aic-input">
                    <option value="en" selected>English</option>
                    <option value="ru">Русский</option>
                    <option value="uk">Українська</option>
                    <option value="es">Español</option>
                    <option value="de">Deutsch</option>
                    <option value="fr">Français</option>
                    <option value="it">Italiano</option>
                    <option value="pt">Português</option>
                    <option value="zh">中文</option>
                    <option value="ja">日本語</option>
                    <option value="ko">한국어</option>
                    <option value="ar">العربية</option>
                </select>
                <button id="aic-start-chat" class="aic-button">Start a chat</button>
            </div>
        </div>
        
        <div id="aic-chat-messages" class="aic-chat-messages" style="display: none;"></div>
        
        <div id="aic-chat-input-wrapper" class="aic-chat-input-wrapper" style="display: none;">
            <textarea 
                id="aic-message-input" 
                class="aic-message-input" 
                placeholder="Enter your message..."
                rows="1"></textarea>
            <button id="aic-send-button" class="aic-send-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
:root {
    --widget-color: <?php echo esc_attr($color); ?>;
    --widget-border-radius: <?php echo esc_attr($border_radius); ?>px;
    --widget-font-size: <?php echo esc_attr($font_size); ?>px;
    --widget-padding: <?php echo esc_attr($padding); ?>px;
    --widget-bg-color: <?php echo esc_attr($widget_bg_color); ?>;
    --chat-button-color: <?php echo esc_attr($chat_button_color); ?>;
    --header-bg-color: <?php echo esc_attr($header_bg_color); ?>;
    --header-text-color: <?php echo esc_attr($header_text_color); ?>;
    --header-status-color: <?php echo esc_attr($header_status_color); ?>;
    --header-icons-color: <?php echo esc_attr($header_icons_color); ?>;
    --header-close-color: <?php echo esc_attr($header_close_color); ?>;
    --user-msg-bg-color: <?php echo esc_attr($user_msg_bg_color); ?>;
    --admin-msg-bg-color: <?php echo esc_attr($admin_msg_bg_color); ?>;
    --user-msg-text-color: <?php echo esc_attr($user_msg_text_color); ?>;
    --admin-msg-text-color: <?php echo esc_attr($admin_msg_text_color); ?>;
    --send-button-color: <?php echo esc_attr($send_button_color); ?>;
    --input-border-color: <?php echo esc_attr($input_border_color); ?>;
}

#aic-chat-widget {
    position: fixed;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    font-size: var(--widget-font-size);
}

.aic-widget-bottom-right {
    bottom: 20px;
    right: 20px;
}

.aic-widget-bottom-left {
    bottom: 20px;
    left: 20px;
}

.aic-widget-top-right {
    top: 20px;
    right: 20px;
}

.aic-widget-top-left {
    top: 20px;
    left: 20px;
}

.aic-chat-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--chat-button-color);
    border: none;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.aic-chat-button:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.aic-unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f44336;
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
}

.aic-chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 600px;
    background: var(--widget-bg-color);
    border-radius: var(--widget-border-radius);
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.aic-widget-bottom-left .aic-chat-window,
.aic-widget-top-left .aic-chat-window {
    right: auto;
    left: 0;
}

.aic-widget-top-right .aic-chat-window,
.aic-widget-top-left .aic-chat-window {
    bottom: auto;
    top: 80px;
}

.aic-chat-header {
    background: var(--header-bg-color);
    color: white;
    padding: var(--widget-padding);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.aic-icon-button {
    background: transparent;
    border: none;
    color: var(--header-icons-color);
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background-color 0.3s;
    position: relative;
}

.aic-icon-button:hover {
    background: rgba(255, 255, 255, 0.1);
}

.aic-sound-icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sound-mute-line {
    display: none;
    position: absolute;
    width: 28px;
    height: 2px;
    background: white;
    transform: rotate(-45deg);
    border-radius: 1px;
}

.aic-icon-button.sound-disabled .sound-waves {
    display: none;
}

.aic-icon-button.sound-disabled .sound-mute-line {
    display: block;
}

.aic-icon-button.sound-disabled {
    opacity: 0.7;
}

.aic-chat-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--header-text-color);
}

.aic-chat-status {
    margin: 5px 0 0 0;
    font-size: 12px;
    opacity: 0.9;
    color: var(--header-status-color);
}

.aic-chat-close {
    background: transparent;
    border: none;
    color: var(--header-close-color);
    font-size: 28px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
}

.aic-welcome-screen {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
}

.aic-welcome-content {
    text-align: center;
    width: 100%;
}

.aic-welcome-content h3 {
    margin: 0 0 10px 0;
    font-size: 62px;
    color: var(--header-bg-color);
}

.aic-welcome-content p {
    margin: 0 0 25px 0;
    color: #666;
    font-size: 14px;
}

.aic-input {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--input-border-color);
    border-radius: 8px;
    margin-bottom: 12px;
    font-size: 14px;
    box-sizing: border-box;
}

.aic-input:focus {
    outline: none;
    border-color: var(--header-bg-color);
}

.aic-button {
    width: 100%;
    padding: 12px;
    background: var(--header-bg-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.3s;
}

.aic-button:hover {
    opacity: 0.9;
}

.aic-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: var(--widget-bg-color);
}

.aic-message {
    margin-bottom: 15px;
    display: flex;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.aic-message.user {
    justify-content: flex-end;
}

.aic-message.admin {
    justify-content: flex-start;
    align-items: flex-start;
    gap: 8px;
}

.aic-admin-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}

.aic-message-content {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.5;
}

.aic-message.user .aic-message-content {
    background: var(--user-msg-bg-color);
    color: var(--user-msg-text-color);
    border-bottom-right-radius: 4px;
}

.aic-message.admin .aic-message-content {
    background: var(--admin-msg-bg-color);
    color: var(--admin-msg-text-color);
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.aic-message-time {
    font-size: 11px;
    color: #382d2d;
    margin-top: 5px;
}

.aic-chat-input-wrapper {
    padding: 15px;
    background: #1a1a1a;
    border-top: 1px solid #18adfe;
    display: flex;
    gap: 10px;
}

.aic-message-input { 
    flex: 1;
    padding: 10px 15px;
    border: 1px solid var(--input-border-color);
    border-radius: 20px;
    resize: none;
    font-size: 14px;
    font-family: inherit;
    max-height: 100px;
}

.aic-message-input:focus {
    outline: none;
    border-color: var(--send-button-color);
}

.aic-send-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--send-button-color);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.3s;
    flex-shrink: 0;
}

.aic-send-button:hover {
    opacity: 0.9;
}

.aic-send-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Мобильная адаптация */
@media (max-width: 480px) {
    .aic-chat-window {
        width: calc(100vw - 50px);
        height: calc(100vh - 100px);
        bottom: 10px;
        right: 10px;
    }
    
    .aic-widget-bottom-left .aic-chat-window {
        left: 10px;
    }
}

/* Скроллбар */
.aic-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.aic-chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.aic-chat-messages::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.aic-chat-messages::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Custom CSS from settings - sanitized with wp_strip_all_tags() on save */
<?php if (!empty($custom_css)): ?>
<?php echo $custom_css; // Already sanitized with wp_strip_all_tags() ?>
<?php endif; ?>
</style>
