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
            // Get sound choice from WordPress settings or use default
            const soundChoice = aicAdmin.sound_choice || 'default';
            const soundUrl = aicAdmin.sound_base_url + 'notification-' + soundChoice + '.mp3';
            
            this.notificationSound = new Audio(soundUrl);
            
            // Handle error loading sound
            this.notificationSound.addEventListener('error', function() {
                console.warn('Could not load selected sound, falling back to default');
                const fallbackUrl = aicAdmin.sound_base_url + 'notification-default.mp3';
                this.notificationSound = new Audio(fallbackUrl);
            }.bind(this));
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
                // Use currentConversationId directly instead of data attribute to avoid stale values
                console.log('[AIC Export] Export button clicked, currentConversationId:', self.currentConversationId);
                self.exportConversation(self.currentConversationId);
            });
        },

        sendTypingStatus: function(isTyping) {
            if (!this.currentConversationId) return;
            
            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                timeout: 5000,
                data: {
                    action: 'aic_admin_typing',
                    nonce: aicAdmin.nonce,
                    conversation_id: this.currentConversationId,
                    is_typing: isTyping ? 1 : 0
                },
                error: function(xhr, status, error) {
                    // Silent fail for typing indicator - not critical
                    console.log('Typing indicator failed (non-critical):', status);
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

        loadConversations: function(retryCount) {
            const self = this;
            retryCount = retryCount || 0;
            
            console.log('loadConversations called (attempt ' + (retryCount + 1) + ')');
            console.log('aicAdmin object:', aicAdmin);
            
            // Verify DOM element exists
            if (!$('#aic-conversations').length) {
                console.error('ERROR: #aic-conversations element not found in DOM');
                return;
            }
            
            // Show loading indicator
            $('#aic-conversations').html('<div style="text-align: center; padding: 20px; color: #666;"><span class="dashicons dashicons-update" style="animation: rotation 2s infinite linear; font-size: 24px;"></span><p>Загрузка диалогов...</p></div>');

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
                    } else if (!response.success && response.data && response.data.code === 'nonce_failed') {
                        // Nonce verification failed - show error and stop polling
                        console.error('Nonce verification failed');
                        if (self.pollInterval) {
                            clearInterval(self.pollInterval);
                            self.pollInterval = null;
                        }
                        $('#aic-conversations').html('<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;"><strong>⚠️ Security Token Expired</strong><p style="margin: 10px 0 0 0;">Your security token has expired. Please refresh the page to continue.</p><button onclick="location.reload()" class="button button-primary">🔄 Refresh Page</button></div>');
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
                    
                    // Handle 403 Forbidden specifically
                    if (xhr.status === 403) {
                        console.error('403 Forbidden - security check failed');
                        if (self.pollInterval) {
                            clearInterval(self.pollInterval);
                            self.pollInterval = null;
                        }
                        $('#aic-conversations').html('<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;"><strong>⚠️ Security Token Expired</strong><p style="margin: 10px 0 0 0;">Your security token has expired. Please refresh the page to continue.</p><button onclick="location.reload()" class="button button-primary">🔄 Refresh Page</button></div>');
                        return;
                    }
                    
                    // Retry up to 2 times for other errors
                    if (retryCount < 2) {
                        console.log('Retrying in 2 seconds...');
                        setTimeout(function() {
                            self.loadConversations(retryCount + 1);
                        }, 2000);
                    } else {
                        $('#aic-conversations').html('<div style="padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px;"><strong>❌ Ошибка загрузки диалогов</strong><p style="margin: 10px 0 0 0;">Не удалось загрузить список диалогов после нескольких попыток. Попробуйте:</p><ul style="margin: 10px 0; padding-left: 20px;"><li>Обновить страницу (F5)</li><li>Очистить кэш браузера (Ctrl+Shift+Delete)</li><li>Проверить консоль браузера (F12) для деталей</li></ul><button onclick="location.reload()" class="button button-primary">🔄 Обновить страницу</button></div>');
                    }
                }
            });
        },

        renderConversations: function(conversations) {
            const container = $('#aic-conversations');
            
            if (!container.length) {
                console.error('ERROR: #aic-conversations container not found in DOM');
                return;
            }
            
            if (!conversations || conversations.length === 0) {
                container.html('<p style="color: #666; padding: 15px; text-align: center;">Нет активных диалогов</p>');
                return;
            }

            let html = '';
            try {
                conversations.forEach(function(conv) {
                    const unreadBadge = conv.unread_count > 0 
                        ? `<span class="aic-unread-badge">${conv.unread_count}</span>` 
                        : '';
                    
                    const userName = adminChat.escapeHtml(conv.user_name || 'Гость #' + conv.id);
                    const lastMessage = adminChat.escapeHtml(conv.last_message || 'Нет сообщений');
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
            } catch(error) {
                console.error('Error rendering conversations:', error);
                container.html('<p style="color: #d32f2f; padding: 15px;">Ошибка отображения диалогов. См. консоль.</p>');
                return;
            }

            container.html(html);
        },

        loadConversation: function(conversationId, retryCount) {
            const self = this;
            retryCount = retryCount || 0;
            this.currentConversationId = conversationId;
            
            console.log('Loading conversation:', conversationId, '(attempt ' + (retryCount + 1) + ')');
            
            // Verify DOM element exists
            if (!$('#aic-current-chat').length) {
                console.error('ERROR: #aic-current-chat element not found in DOM');
                return;
            }

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
                    
                    // Retry up to 2 times
                    if (retryCount < 2) {
                        console.log('Retrying in 2 seconds...');
                        setTimeout(function() {
                            self.loadConversation(conversationId, retryCount + 1);
                        }, 2000);
                    } else {
                        $('#aic-current-chat').html('<div style="padding: 15px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px;"><strong>❌ Ошибка загрузки сообщений</strong><p style="margin: 10px 0 0 0;">Не удалось загрузить сообщения после нескольких попыток.</p><button onclick="location.reload()" class="button button-primary" style="margin-top: 10px;">🔄 Обновить страницу</button></div>');
                    }
                }
            });
        },

        renderMessages: function(messages, conversation) {
            const container = $('#aic-current-chat');
            
            console.log('renderMessages called with', messages ? messages.length : 0, 'messages');
            
            if (!container.length) {
                console.error('ERROR: #aic-current-chat container not found in DOM');
                return;
            }
            
            // Check if input field is currently focused (user is typing)
            const inputIsFocused = $('#aic_admin_message_input').is(':focus');
            
            // If input is focused, skip the update to avoid interrupting user typing
            if (inputIsFocused) {
                console.log('Input field is focused, skipping HTML update to preserve user typing');
                return;
            }
            
            // Save current input value before rewriting HTML
            const currentInputValue = $('#aic_admin_message_input').val() || '';
            
            let html = '';
            
            try {
                html = '<div style="padding: 20px; max-height: 500px; overflow-y: auto; background: var(--aic-tab);" id="aic_messages_container">';
            
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
                        
                        // Add avatar for admin messages if configured
                        let avatarHtml = '';
                        if (isAdmin && aicAdmin.admin_avatar) {
                            avatarHtml = '<img src="' + adminChat.escapeHtml(aicAdmin.admin_avatar) + '" style="width: 32px; height: 32px; border-radius: 50%; margin-right: 8px;" alt="Admin">';
                        }

                        html += `
                            <div style="display: flex; justify-content: ${alignClass}; margin-bottom: 15px;">
                                ${isAdmin && avatarHtml ? '<div style="display: flex; align-items: flex-start; gap: 8px;">' + avatarHtml : ''}
                                <div style="max-width: 70%; padding: 12px 16px; border-radius: 12px; background: ${bgColor}; color: ${textColor}; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                    ${adminChat.escapeHtml(displayText)}
                                    ${hasTranslation ? '<div style="font-size: 10px; margin-top: 5px; opacity: 0.6; font-style: italic;">📝 Оригинал: ' + adminChat.escapeHtml(msg.message_text) + '</div>' : ''}
                                    <div style="font-size: 11px; margin-top: 5px; opacity: 0.7;">${time}</div>
                                </div>
                                ${isAdmin && avatarHtml ? '</div>' : ''}
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
            } catch(error) {
                console.error('Error rendering messages:', error);
                html = '<div style="padding: 20px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px; margin-bottom: 15px;"><strong>❌ Ошибка отображения сообщений</strong><p>' + error.message + '</p></div>';
            }
            
            // Always add input area at the bottom
            html += `
                <div style="padding: 15px; border-top: 1px solid #eee; var(--aic-tab);">
                <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button id="aic_export_conversation" class="button">
                            <span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Экспорт CSV
                        </button>
                  </div>
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
                try {
                    window.AICEmojiPicker.init('#aic_admin_message_input', '#aic_admin_emoji_button');
                } catch(e) {
                    console.warn('Could not initialize emoji picker:', e);
                }
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
                        alert(aicAdmin.i18n.error_sending_message_details.replace('%s', errorMsg));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка отправки:', error);
                    alert(aicAdmin.i18n.error_sending_message);
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
            }, 20000);
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
            // Enhanced validation with detailed logging
            console.log('[AIC Export] exportConversation called with ID:', conversationId, 'Type:', typeof conversationId);
            
            if (!conversationId || conversationId === null || conversationId === 'null' || conversationId === undefined) {
                console.error('[AIC Export] Invalid conversation ID:', conversationId);
                alert(aicAdmin.i18n.error_select_conversation_first);
                return;
            }
            
            // Ensure it's a number
            conversationId = parseInt(conversationId, 10);
            if (isNaN(conversationId) || conversationId <= 0) {
                console.error('[AIC Export] Conversation ID is not a valid positive number:', conversationId);
                alert(aicAdmin.i18n.error_invalid_conversation_id);
                return;
            }
            
            console.log('[AIC Export] Starting export for conversation:', conversationId);
            const $button = $('#aic_export_conversation');
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Экспорт...');
            
            // Log the data being sent
            const requestData = {
                action: 'aic_export_conversation',
                nonce: aicAdmin.nonce,
                conversation_id: conversationId
            };
            console.log('[AIC Export] Sending AJAX request with data:', requestData);
            
            $.ajax({
                url: aicAdmin.ajax_url,
                type: 'POST',
                data: requestData,
                success: function(response) {
                    console.log('[AIC Export] Server response:', response);
                    
                    // Validate response structure
                    if (!response) {
                        console.error('[AIC Export] Empty response from server');
                        alert(aicAdmin.i18n.error_export_empty_response);
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                        return;
                    }
                    
                    if (!response.success) {
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Неизвестная ошибка';
                        console.error('[AIC Export] Export failed:', errorMsg);
                        alert(aicAdmin.i18n.error_export_details.replace('%s', errorMsg));
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                        return;
                    }
                    
                    if (!response.data || !response.data.csv) {
                        console.error('[AIC Export] Invalid response data:', response.data);
                        alert(aicAdmin.i18n.error_export_no_csv_data);
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                        return;
                    }
                    
                    try {
                        // Decode base64 CSV content
                        // UTF-8 BOM is now added on the server side for proper Cyrillic character encoding
                        const binaryString = atob(response.data.csv);
                        console.log('[AIC Export] CSV decoded, length:', binaryString.length);
                        
                        // Convert binary string to Uint8Array to preserve UTF-8 encoding
                        // This is critical for proper Cyrillic character handling
                        const bytes = new Uint8Array(binaryString.length);
                        for (let i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        
                        // Create blob with UTF-8 charset (BOM already included in content from server)
                        const blob = new Blob([bytes], { type: 'text/csv;charset=utf-8;' });
                        
                        // Create download link
                        const link = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        
                        link.setAttribute('href', url);
                        link.setAttribute('download', response.data.filename || 'conversation_export.csv');
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        
                        // Clean up
                        setTimeout(function() {
                            document.body.removeChild(link);
                            URL.revokeObjectURL(url);
                        }, 100);
                        
                        console.log('[AIC Export] Export successful:', response.data.filename);
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                        
                    } catch (error) {
                        console.error('[AIC Export] Error processing CSV:', error);
                        alert(aicAdmin.i18n.error_processing_csv.replace('%s', error.message));
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[AIC Export] AJAX error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    let errorMsg = 'Ошибка соединения с сервером';
                    if (xhr.status === 403) {
                        errorMsg = 'Security token expired. Please refresh the page and try again.';
                    } else if (xhr.status === 404) {
                        errorMsg = 'Действие не найдено (404)';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Ошибка сервера (500)';
                    } else if (status === 'timeout') {
                        errorMsg = 'Превышено время ожидания';
                    } else if (status === 'parsererror') {
                        errorMsg = 'Ошибка разбора ответа сервера';
                    }
                    
                    alert(aicAdmin.i18n.error_export_details.replace('%s', errorMsg));
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Экспорт CSV');
                }
            });
        }
    };

    // Инициализация только на странице чата
    if ($('#aic-conversations').length) {
        console.log('Admin chat page detected, initializing...');
        console.log('DOM ready state:', document.readyState);
        console.log('jQuery version:', $.fn.jquery);
        
        // Check if aicAdmin object exists
        if (typeof aicAdmin === 'undefined') {
            console.error('ERROR: aicAdmin object is not defined! Scripts may not be properly enqueued.');
            $('#aic-conversations').html('<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;"><strong>⚠️ Ошибка инициализации:</strong><p>Объект aicAdmin не определен. Скрипты админки не загружены правильно.</p><p style="margin-top: 10px;">Попробуйте:</p><ul style="margin: 10px 0; padding-left: 20px;"><li>Очистить кэш браузера (Ctrl+Shift+Delete)</li><li>Обновить страницу с очисткой кэша (Ctrl+F5)</li><li>Проверить консоль браузера (F12) для деталей</li></ul><button onclick="location.reload()" class="button button-primary">🔄 Обновить страницу</button></div>');
            return;
        }
        
        console.log('aicAdmin object found:', aicAdmin);
        console.log('AJAX URL:', aicAdmin.ajax_url);
        console.log('Nonce:', aicAdmin.nonce ? 'Present' : 'Missing');
        
        // Verify essential properties
        if (!aicAdmin.ajax_url || !aicAdmin.nonce) {
            console.error('ERROR: aicAdmin object is incomplete!');
            $('#aic-conversations').html('<div style="padding: 20px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px;"><strong>❌ Ошибка конфигурации:</strong><p>Объект aicAdmin неполный. Отсутствуют необходимые параметры.</p><button onclick="location.reload()" class="button button-primary">🔄 Обновить страницу</button></div>');
            return;
        }
        
        // Initialize with slight delay to ensure all DOM is ready
        setTimeout(function() {
            try {
                adminChat.init();
                console.log('✓ Admin chat initialized successfully');
            } catch(e) {
                console.error('ERROR during initialization:', e);
                $('#aic-conversations').html('<div style="padding: 20px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px;"><strong>❌ Ошибка запуска:</strong><p>' + e.message + '</p><button onclick="location.reload()" class="button button-primary">🔄 Обновить страницу</button></div>');
            }
        }, 100);
    } else {
        console.log('Admin chat page not detected, skipping initialization');
        console.log('Current page URL:', window.location.href);
    }
});
