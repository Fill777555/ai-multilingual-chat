jQuery(document).ready(function($) {
    const adminChat = {
        currentConversationId: null,
        pollInterval: null,

        init: function() {
            this.loadConversations();
            this.bindEvents();
            this.startPolling();
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
        },

        loadConversations: function() {
            const self = this;

            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aic_admin_get_conversations',
                    nonce: aicAdmin.nonce
                },
                success: function(response) {
                    console.log('Диалоги загружены:', response);
                    if (response.success) {
                        self.renderConversations(response.data.conversations);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка загрузки диалогов:', error);
                    $('#aic-conversations').html('<p style="color: #d32f2f; padding: 15px;">Ошибка загрузки диалогов</p>');
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

            // Обновить активный класс
            $('.aic-conversation-item').removeClass('active');
            $(`.aic-conversation-item[data-id="${conversationId}"]`).addClass('active');

            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aic_admin_get_messages',
                    nonce: aicAdmin.nonce,
                    conversation_id: conversationId
                },
                success: function(response) {
                    console.log('Сообщения загружены:', response);
                    if (response.success) {
                        self.renderMessages(response.data.messages);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка загрузки сообщений:', error);
                }
            });
        },

        renderMessages: function(messages) {
            const container = $('#aic-current-chat');
            
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

            html += '</div>';
            html += `
                <div style="padding: 15px; border-top: 1px solid #eee; background: #fff;">
                    <textarea id="aic_admin_message_input" 
                              placeholder="Введите сообщение..." 
                              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; font-family: inherit; box-sizing: border-box;"
                              rows="3"></textarea>
                    <button id="aic_admin_send_message" class="button button-primary" style="margin-top: 10px;">
                        <span class="dashicons dashicons-email" style="vertical-align: middle;"></span> Отправить
                    </button>
                </div>
            `;

            container.html(html);
            this.scrollToBottom();
        },

        sendMessage: function() {
            const message = $('#aic_admin_message_input').val().trim();
            
            if (!message || !this.currentConversationId) {
                console.log('Нет сообщения или conversation_id');
                return;
            }

            const self = this;

            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
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
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка отправки:', error);
                    alert('Ошибка отправки сообщения');
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
        }
    };

    // Инициализация только на странице чата
    if ($('#aic-conversations').length) {
        adminChat.init();
    }
});
