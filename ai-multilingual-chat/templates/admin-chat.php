<?php
if (!defined('ABSPATH')) exit;

// Get current theme mode from localStorage (will be applied via JS)
$theme_mode = get_option('aic_theme_mode', 'auto');
?>

<div class="wrap">
    <div class="aic-header">
        <h1 class="aic-title">AI Chat - Управление диалогами</h1>
        <div class="aic-controls" role="toolbar" aria-label="Theme Controls">
            <button class="aic-btn" data-aic-theme-toggle="light" aria-pressed="<?php echo $theme_mode === 'light' ? 'true' : 'false'; ?>" title="Светлая тема">
                <span class="dashicons dashicons-admin-appearance" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> Светлая
            </button>
            <button class="aic-btn" data-aic-theme-toggle="dark" aria-pressed="<?php echo $theme_mode === 'dark' ? 'true' : 'false'; ?>" title="Тёмная тема">
                <span class="dashicons dashicons-admin-customizer" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> Тёмная
            </button>
            <button class="aic-btn" data-aic-theme-toggle="auto" aria-pressed="<?php echo $theme_mode === 'auto' ? 'true' : 'false'; ?>" title="Автоматическая тема">
                <span class="dashicons dashicons-image-rotate" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span> Авто
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
                <h2 class="aic-title" style="margin: 0; font-size: 15px;">Диалоги</h2>
                <button id="aic_refresh_conversations" class="aic-btn" title="Обновить список диалогов">
                    <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
                </button>
            </div>
            <div id="aic-conversations">
                <div style="text-align: center; padding: 20px; color: var(--aic-text-secondary);">
                    <span class="dashicons dashicons-update" style="font-size: 24px; animation: rotation 2s infinite linear;"></span>
                    <p>Инициализация...</p>
                </div>
            </div>
        </div>
        
        <div class="aic-chat-area">
            <div id="aic-current-chat">
                <div style="text-align: center; padding: 50px 20px; color: var(--aic-text-secondary);">
                    <span class="dashicons dashicons-format-chat" style="font-size: 64px; width: 64px; height: 64px; color: var(--aic-muted);"></span>
                    <h3 style="color: var(--aic-text-primary);">Выберите диалог</h3>
                    <p>Выберите диалог из списка слева для начала общения</p>
                </div>
            </div>
        </div>
    </div>
</div>
