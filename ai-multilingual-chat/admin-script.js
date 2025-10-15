jQuery(document).ready(function($) {
    const adminChat = {
        currentConversationId: null,
        pollInterval: null,
        lastUnreadCount: 0,
        notificationSound: null,

        init: function() {
            console.log('Admin chat initialization started');
            console.log('Checking for #aic-conversations element:', $('#aic-conversations').length > 0);
            this.initNotificationSound();
            this.loadConversations();
            this.bindEvents();
            this.startPolling();
            console.log('Admin chat initialized successfully');
        },

        initNotificationSound: function() {
            // Create notification sound using Web Audio API
            this.notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYHGGa77Od/Sh0MTKXi8LJjHAU2jtXyz3kpBSp4x/DckD4KEly06OqnVBIKRp7f8L5sIAUrgs/y2Yk3Bxdlu+znfkkdC0yl4vCyYxwFN47V8c55KgQpecfv3JA+ChJcten')]
        },

        bindEvents: function() {
            const self = this;

            // Обновить список диалогов
            $(document).on('click', '#aic_refresh_conversations', function() {
                self.loadConversations();
            });

            // Выбрать диалог
            $(document).on('click', '.aic-conversation-item', function() {
                const conversationId = $(this).data('id');
                self.loadConversation(conversationId);
            });

            // Отправить сообщение
            $(document).on('click', '#aic_admin_send_message', function() {
                self.sendMessage();
            });

            $(document).on('keypress', '#aic_admin_message_input', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            // Typing indicator
            let typingTimer;
            $(document).on('input', '#aic_admin_message_input', function() {
                clearTimeout(typingTimer);
                self.sendTypingStatus(true);
                typingTimer = setTimeout(function() {
                    self.sendTypingStatus(false);
                }, 1000);
            });
            
            // Export conversation
            $(document).on('click', '#aic_export_conversation', function() {
                const conversationId = $(this).data('conversation-id');
                self.exportConversation(conversationId);
            });
        },

        sendTypingStatus: function(isTyping) {
            if (!this.currentConversationId) return;
            
            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aic_admin_typing',
                    nonce: aicAdmin.nonce,
                    conversation_id: this.currentConversationId,
                    is_typing: isTyping ? 1 : 0
                }
            });
        },

        playNotificationSound: function() {
            if (this.notificationSound) {
                this.notificationSound.play().catch(function(e) {
                    console.log('Could not play notification sound:', e);
                });
            }
        },

        loadConversations: function() {
            const self = this;
            
            console.log('loadConversations called');
            console.log('aicAdmin object:', aicAdmin);

            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'aic_admin_get_conversations',
                    nonce: aicAdmin.nonce
                },
                success: function(response) {
                    console.log('Диалоги загружены:', response);
                    if (response.success && response.data && response.data.conversations) {
                        const conversations = response.data.conversations;
                        console.log('Found conversations:', conversations.length);
                        
                        // Check for new unread messages and play sound
                        const totalUnread = conversations.reduce((sum, conv) => sum + (conv.unread_count || 0), 0);
                        if (totalUnread > self.lastUnreadCount) {
                            self.playNotificationSound();
                        }
                        self.lastUnreadCount = totalUnread;
                        
                        self.renderConversations(conversations);
                    } else {
                        console.error('Response not successful or missing data:', response);
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        $('#aic-conversations').html('<p style="color: #d32f2f; padding: 15px;">Ошибка: ' + errorMsg + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка загрузки диалогов:', error);
                    console.error('XHR:', xhr);
                    console.error('Status:', status);
                    $('#aic-conversations').html('<p style="color: #d32f2f; padding: 15px;">Ошибка загрузки диалогов. Проверьте консоль для деталей.</p>');
                }
            });
        },

        renderConversations: function(conversations) {
            const container = $('#aic-conversations');
            
            if (!conversations || conversations.length === 0) {
                container.html('<p style="color: #666; padding: 15px; text-align: center;">Нет активных диалогов</p>');
                return;
            }

            let html = '';
            conversations.forEach(function(conv) {
                const unreadBadge = conv.unread_count > 0 
                    ? `<span class="aic-unread-badge">${conv.unread_count}</span>` 
                    : '';
                
                const userName = conv.user_name || 'Гость #' + conv.id;
                const lastMessage = conv.last_message || 'Нет сообщений';
                const activeClass = conv.id == adminChat.currentConversationId ? 'active' : '';

                html += `
                    <div class="aic-conversation-item ${activeClass}" data-id="${conv.id}">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="aic-conversation-name">${userName}</div>
                            ${unreadBadge}
                        </div>
                        <div class="aic-conversation-preview">${lastMessage.substring(0, 50)}${lastMessage.length > 50 ? '...' : ''}</div>
                        <div style="font-size: 11px; color: #999; margin-top: 5px;">
                            ${new Date(conv.updated_at).toLocaleString('ru-RU')}
                        </div>
                    </div>
                `;
            });

            container.html(html);
        },

        loadConversation: function(conversationId) {
            const self = this;
            this.currentConversationId = conversationId;
            
            console.log('Loading conversation:', conversationId);

            // Обновить активный класс
            $('.aic-conversation-item').removeClass('active');
            $(`.aic-conversation-item[data-id="${conversationId}"]`).addClass('active');

            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'aic_admin_get_messages',
                    nonce: aicAdmin.nonce,
                    conversation_id: conversationId
                },
                success: function(response) {
                    console.log('Сообщения загружены:', response);
                    if (response.success && response.data && response.data.messages) {
                        self.renderMessages(response.data.messages, response.data.conversation);
                    } else {
                        console.error('Response not successful or missing data:', response);
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        $('#aic-current-chat').html('<p style="color: #d32f2f; padding: 15px;">Ошибка: ' + errorMsg + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка загрузки сообщений:', error);
                    console.error('XHR:', xhr);
                    console.error('Status:', status);
                    $('#aic-current-chat').html('<p style="color: #d32f2f; padding: 15px;">Ошибка загрузки сообщений. Проверьте консоль для деталей.</p>');
                }
            });
        },

        renderMessages: function(messages, conversation) {
            const container = $('#aic-current-chat');
            
            console.log('renderMessages called with', messages ? messages.length : 0, 'messages');
            
            // Check if input field is currently focused (user is typing)
            const inputIsFocused = $('#aic_admin_message_input').is(':focus');
            
            // If input is focused, skip the update to avoid interrupting user typing
            if (inputIsFocused) {
                console.log('Input field is focused, skipping HTML update to preserve user typing');
                return;
            }
            
            // Save current input value before rewriting HTML
            const currentInputValue = $('#aic_admin_message_input').val() || '';
            
            let html = '<div style="padding: 20px; max-height: 500px; overflow-y: auto; background: #f8f9fa;" id="aic_messages_container">';
            
            if (!messages || messages.length === 0) {
                html += `
                    <div style="text-align: center; padding: 50px 20px; color: #666;">
                        <span class="dashicons dashicons-format-chat" style="font-size: 48px; width: 48px; height: 48px; color: #ccc;"></span>
                        <h3>Нет сообщений</h3>
                        <p>Начните диалог с клиентом</p>
                    </div>
                `;
            } else {
                messages.forEach(function(msg) {
                    const isAdmin = msg.sender_type === 'admin';
                    const alignClass = isAdmin ? 'flex-end' : 'flex-start';
                    const bgColor = isAdmin ? '#667eea' : '#fff';
                    const textColor = isAdmin ? '#fff' : '#333';
                    const time = new Date(msg.created_at).toLocaleTimeString('ru-RU', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });

                    // Show translated text to admin if available, otherwise show original
                    const displayText = (!isAdmin && msg.translated_text) ? msg.translated_text : msg.message_text;
                    const hasTranslation = (!isAdmin && msg.translated_text);

                    html += `
                        <div style="display: flex; justify-content: ${alignClass}; margin-bottom: 15px;">
                            <div style="max-width: 70%; padding: 12px 16px; border-radius: 12px; background: ${bgColor}; color: ${textColor}; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                ${adminChat.escapeHtml(displayText)}
                                ${hasTranslation ? '<div style="font-size: 10px; margin-top: 5px; opacity: 0.6; font-style: italic;">📝 Оригинал: ' + adminChat.escapeHtml(msg.message_text) + '</div>' : ''}
                                <div style="font-size: 11px; margin-top: 5px; opacity: 0.7;">${time}</div>
                            </div>
                        </div>
                    `;
                });
            }
            
            // Show typing indicator if user is typing
            if (conversation && conversation.user_typing) {
                const typingTime = new Date(conversation.user_typing_at);
                const now = new Date();
                const timeDiff = (now - typingTime) / 1000; // seconds
                
                // Only show typing indicator if less than 3 seconds old
                if (timeDiff < 3) {
                    html += `
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 15px;">
                            <div style="max-width: 70%; padding: 12px 16px; border-radius: 12px; background: #fff; color: #666; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                <span class="typing-indicator">
                                    <span style="animation: typing-dot 1.4s infinite; display: inline-block;">●</span>
                                    <span style="animation: typing-dot 1.4s infinite 0.2s; display: inline-block;">●</span>
                                    <span style="animation: typing-dot 1.4s infinite 0.4s; display: inline-block;">●</span>
                                </span>
                            </div>
                        </div>
                    `;
                }
            }

            html += '</div>';
            html += `
                <div style="padding: 15px; border-top: 1px solid #eee; background: #fff;">
                    <div style="display: flex; gap: 5px; align-items: flex-end;">
                        <textarea id="aic_admin_message_input" 
                                  placeholder="Введите сообщение..." 
                                  style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; font-family: inherit; box-sizing: border-box;"
                                  rows="3"></textarea>
                        ${aicAdmin.enable_emoji === '1' ? '<button type="button" id="aic_admin_emoji_button" class="aic-emoji-button" title="Выбрать эмодзи">😀</button>' : ''}
                    </div>
                    <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button id="aic_admin_send_message" class="button button-primary">
                            <span class="dashicons dashicons-email" style="vertical-align: middle;"></span> Отправить
                        </button>
                        <button id="aic_export_conversation" class="button" data-conversation-id="${self.currentConversationId}">
                            <span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Экспорт CSV
                        </button>
                    </div>
                </div>
            `;

            container.html(html);
            console.log('HTML rendered, input field present:', $('#aic_admin_message_input').length > 0);
            
            // Restore the saved input value after HTML is rewritten
            if (currentInputValue) {
                $('#aic_admin_message_input').val(currentInputValue);
            }
            
            // Initialize emoji picker if enabled
            if (aicAdmin.enable_emoji === '1' && window.AICEmojiPicker) {
                window.AICEmojiPicker.init('#aic_admin_message_input', '#aic_admin_emoji_button');
            }
            
            this.scrollToBottom();
        },

        sendMessage: function() {
            const message = $('#aic_admin_message_input').val().trim();
            
            if (!message || !this.currentConversationId) {
                console.log('Нет сообщения или conversation_id');
                return;
            }

            const self = this;
            const $button = $('#aic_admin_send_message');
            const originalText = $button.html();
            $button.prop('disabled', true).html('Отправка...');

            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'aic_admin_send_message',
                    nonce: aicAdmin.nonce,
                    conversation_id: this.currentConversationId,
                    message: message
                },
                success: function(response) {
                    console.log('Сообщение отправлено:', response);
                    if (response.success) {
                        $('#aic_admin_message_input').val('');
                        self.loadConversation(self.currentConversationId);
                        self.loadConversations(); // Обновить список диалогов
                    } else {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        alert('Ошибка отправки сообщения: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка отправки:', error);
                    alert('Ошибка отправки сообщения');
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalText);
                }
            });
        },

        startPolling: function() {
            const self = this;
            
            // Обновлять список диалогов каждые 5 секунд
            this.pollInterval = setInterval(function() {
                self.loadConversations();
                
                // Если открыт диалог, обновить и его
                if (self.currentConversationId) {
                    self.loadConversation(self.currentConversationId);
                }
            }, 5000);
        },

        scrollToBottom: function() {
            const container = $('#aic_messages_container');
            if (container.length) {
                container.scrollTop(container[0].scrollHeight);
            }
        },

        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        },
        
        exportConversation: function(conversationId) {
            if (!conversationId) {
                alert('Выберите диалог для экспорта');
                return;
            }
            
            const $button = $('#aic_export_conversation');
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Экспорт...');
            
            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aic_export_conversation',
                    nonce: aicAdmin.nonce,
                    conversation_id: conversationId
                },
                success: function(response) {
                    if (response.success && response.data.csv) {
                        // Create download link
                        const csvContent = atob(response.data.csv);
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        
                        link.setAttribute('href', url);
                        link.setAttribute('download', response.data.filename);
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                    } else {
                        alert('Ошибка экспорта');
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                    }
                },
                error: function() {
                    alert('Ошибка экспорта');
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                }
            });
        }
    };

    // Инициализация только на странице чата
    if ($('#aic-conversations').length) {
        console.log('Admin chat page detected, initializing...');
        adminChat.init();
    } else {
        console.log('Admin chat page not detected, skipping initialization');
    }
});
