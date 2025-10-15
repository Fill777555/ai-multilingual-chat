<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>AI Chat - Управление диалогами</h1>
    
    <!-- Debug info (visible in browser console) -->
    <script>
        console.log('Admin chat template loaded');
        console.log('Time:', new Date().toISOString());
        console.log('Page URL:', window.location.href);
    </script>
    
    <div id="aic-admin-chat-container" style="display: flex; gap: 20px; margin-top: 20px; min-height: 600px;">
        <div class="aic-conversations-list" style="width: 300px; background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); min-height: 500px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0;">Диалоги</h2>
                <button id="aic_refresh_conversations" class="button" title="Обновить список диалогов">
                    <span class="dashicons dashicons-update"></span>
                </button>
            </div>
            <div id="aic-conversations">
                <div style="text-align: center; padding: 20px; color: #999;">
                    <span class="dashicons dashicons-update" style="font-size: 24px; animation: rotation 2s infinite linear;"></span>
                    <p>Инициализация...</p>
                </div>
            </div>
        </div>
        
        <div class="aic-chat-area" style="flex: 1; background: #fff; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); min-height: 500px;">
            <div id="aic-current-chat">
                <div style="text-align: center; padding: 50px 20px; color: #666;">
                    <span class="dashicons dashicons-format-chat" style="font-size: 64px; width: 64px; height: 64px;"></span>
                    <h3>Выберите диалог</h3>
                    <p>Выберите диалог из списка слева для начала общения</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#aic-conversations {
    max-height: 600px;
    overflow-y: auto;
}

.aic-conversation-item {
    padding: 12px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.2s;
}

.aic-conversation-item:hover {
    background: #f5f5f5;
}

.aic-conversation-item.active {
    background: #e3f2fd;
}

.aic-conversation-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.aic-conversation-preview {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.aic-unread-badge {
    background: #f44336;
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
}

/* Ensure containers are visible */
#aic-admin-chat-container {
    min-height: 600px;
}

.aic-conversations-list,
.aic-chat-area {
    min-height: 500px;
}
</style>
