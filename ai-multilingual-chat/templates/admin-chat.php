<?php
if (!defined('ABSPATH')) exit;

// Get current theme mode from localStorage (will be applied via JS)
$theme_mode = get_option('aic_theme_mode', 'auto');
?>

<div class="wrap">
    <div class="aic-header">
        <h1 class="aic-title"><?php echo esc_html__('AI Chat - Conversation Management', 'ai-multilingual-chat'); ?></h1>
        <div class="aic-controls" role="toolbar" aria-label="<?php esc_attr_e('Theme Controls', 'ai-multilingual-chat'); ?>">
            <button class="aic-btn" data-aic-theme-toggle="light" aria-pressed="<?php echo $theme_mode === 'light' ? 'true' : 'false'; ?>" title="<?php esc_attr_e('Light Theme', 'ai-multilingual-chat'); ?>">
                <span class="dashicons dashicons-admin-appearance" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> <?php echo esc_html__('Light', 'ai-multilingual-chat'); ?>
            </button>
            <button class="aic-btn" data-aic-theme-toggle="dark" aria-pressed="<?php echo $theme_mode === 'dark' ? 'true' : 'false'; ?>" title="<?php esc_attr_e('Dark Theme', 'ai-multilingual-chat'); ?>">
                <span class="dashicons dashicons-admin-customizer" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> <?php echo esc_html__('Dark', 'ai-multilingual-chat'); ?>
            </button>
            <button class="aic-btn" data-aic-theme-toggle="auto" aria-pressed="<?php echo $theme_mode === 'auto' ? 'true' : 'false'; ?>" title="<?php esc_attr_e('Automatic Theme', 'ai-multilingual-chat'); ?>">
                <span class="dashicons dashicons-image-rotate" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> <?php echo esc_html__('Auto', 'ai-multilingual-chat'); ?>
            </button>
        </div>
    </div>
    
    <!-- Debug info (visible in browser console) -->
    <script>
        console.log('Admin chat template loaded');
        console.log('Time:', new Date().toISOString());
        console.log('Page URL:', window.location.href);
    </script>
    
    <div id="aic-admin-chat-container">
        <div class="aic-conversations-list">
            <div class="aic-header" style="padding-bottom: 8px;">
                <h2 class="aic-title" style="margin: 0; font-size: 15px;"><?php echo esc_html__('Conversations', 'ai-multilingual-chat'); ?></h2>
                <button id="aic_refresh_conversations" class="aic-btn" title="<?php esc_attr_e('Refresh conversation list', 'ai-multilingual-chat'); ?>">
                    <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
                </button>
            </div>
            <div id="aic-conversations">
                <div style="text-align: center; padding: 20px; color: var(--aic-text-secondary);">
                    <span class="dashicons dashicons-update" style="font-size: 24px; animation: rotation 2s infinite linear;"></span>
                    <p><?php echo esc_html__('Initializing...', 'ai-multilingual-chat'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="aic-chat-area">
            <div id="aic-current-chat">
                <div style="text-align: center; padding: 50px 20px; color: var(--aic-text-secondary);">
                    <span class="dashicons dashicons-format-chat" style="font-size: 64px; width: 64px; height: 64px; color: var(--aic-muted);"></span>
                    <h3 style="color: var(--aic-text-primary);"><?php echo esc_html__('Select a Conversation', 'ai-multilingual-chat'); ?></h3>
                    <p><?php echo esc_html__('Select a conversation from the list on the left to start chatting', 'ai-multilingual-chat'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
