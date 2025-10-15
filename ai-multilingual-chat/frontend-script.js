jQuery(document).ready(function($) {
    const widget = {
        sessionId: null,
        conversationId: null,
        userName: null,
        userLanguage: 'ru',
        pollInterval: null,
        lastMessageId: 0,
        isInitialized: false,

        init: function() {
            this.sessionId = this.getOrCreateSessionId();
            this.bindEvents();
            this.checkExistingConversation();
        },

        getOrCreateSessionId: function() {
            let sessionId = localStorage.getItem('aic_session_id');
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('aic_session_id', sessionId);
            }
            return sessionId;
        },

        bindEvents: function() {
            const self = this;

            $('#aic-chat-button').on('click', function() {
                $('#aic-chat-window').slideToggle(300);
            });

            $('#aic-chat-close').on('click', function() {
                $('#aic-chat-window').slideUp(300);
            });

            $('#aic-start-chat').on('click', function() {
                const name = $('#aic-user-name').val().trim();
                const language = $('#aic-user-language').val();

                if (!name) {
                    alert('Пожалуйста, введите ваше имя');
                    return;
                }

                self.userName = name;
                self.userLanguage = language;
                localStorage.setItem('aic_user_name', name);
                localStorage.setItem('aic_user_language', language);

                self.createConversation();
            });

            $('#aic-send-button').on('click', function() {
                self.sendMessage();
            });

            $('#aic-message-input').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            $('#aic-message-input').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        },

        checkExistingConversation: function() {
            const savedName = localStorage.getItem('aic_user_name');
            const savedLanguage = localStorage.getItem('aic_user_language');

            if (savedName && savedLanguage) {
                this.userName = savedName;
                this.userLanguage = savedLanguage;
                this.loadMessages();
            }
        },

        createConversation: function() {
            const self = this;

            if (!this.sessionId || !this.userName) {
                alert('Ошибка: отсутствуют необходимые данные');
                return;
            }

            $.ajax({
                url: aicFrontend.ajax_url,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'aic_start_conversation',
                    nonce: aicFrontend.nonce,
                    session_id: this.sessionId,
                    user_name: this.userName,
                    user_language: this.userLanguage
                },
                success: function(response) {
                    console.log('Разговор создан:', response);
                    if (response.success && response.data && response.data.conversation_id) {
                        self.conversationId = response.data.conversation_id;
                        self.isInitialized = true;
                        self.startChat();
                    } else {
                        alert('Ошибка создания разговора: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка создания разговора:', error);
                    alert('Ошибка соединения с сервером');
                }
            });
        },

        startChat: function() {
            $('#aic-welcome-screen').hide();
            $('#aic-chat-messages').show();
            $('#aic-chat-input-wrapper').show();

            this.addSystemMessage(aicFrontend.welcome_message || 'Добро пожаловать! Чем могу помочь?');
            this.startPolling();
        },

        sendMessage: function() {
            const message = $('#aic-message-input').val().trim();
            
            if (!message) return;

            // Проверяем, инициализирован ли разговор
            if (!this.isInitialized) {
                console.error('Разговор не инициализирован');
                this.addSystemMessage('Ошибка: разговор не инициализирован. Попробуйте перезагрузить страницу.');
                return;
            }

            const self = this;

            this.addMessage(message, 'user');
            $('#aic-message-input').val('').css('height', 'auto');

            $.ajax({
                url: aicFrontend.ajax_url,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'aic_send_message',
                    nonce: aicFrontend.nonce,
                    message: message,
                    session_id: this.sessionId,
                    user_language: this.userLanguage
                },
                success: function(response) {
                    console.log('Сообщение отправлено:', response);
                    if (response.success && response.data) {
                        self.conversationId = response.data.conversation_id;
                    } else {
                        console.error('Ошибка:', response.data);
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        self.addSystemMessage('Ошибка отправки сообщения: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX ошибка:', error);
                    self.addSystemMessage('Ошибка отправки сообщения. Попробуйте еще раз.');
                }
            });
        },

        loadMessages: function() {
            const self = this;

            $.ajax({
                url: aicFrontend.ajax_url,
                type: 'POST',
                timeout: 30000,
                data: {
                    action: 'aic_get_messages',
                    nonce: aicFrontend.nonce,
                    session_id: this.sessionId,
                    last_message_id: this.lastMessageId
                },
                success: function(response) {
                    if (response.success && response.data && response.data.messages && response.data.messages.length > 0) {
                        
                        // Если это первая загрузка
                        if (self.lastMessageId === 0) {
                            $('#aic-welcome-screen').hide();
                            $('#aic-chat-messages').show();
                            $('#aic-chat-input-wrapper').show();
                            $('#aic-chat-messages').empty();
                            self.isInitialized = true;
                            
                            if (response.data.conversation_id) {
                                self.conversationId = response.data.conversation_id;
                            }
                        }

                        response.data.messages.forEach(function(msg) {
                            if (msg && 
                                msg.id && 
                                msg.message_text && 
                                typeof msg.message_text === 'string' && 
                                msg.message_text.trim() !== '' &&
                                msg.sender_type) {
                                
                                if (parseInt(msg.id) > self.lastMessageId) {
                                    // Show translated text for admin messages if available, otherwise show original
                                    const displayText = (msg.sender_type === 'admin' && msg.translated_text) ? msg.translated_text : msg.message_text;
                                    self.addMessage(displayText, msg.sender_type, false);
                                    self.lastMessageId = parseInt(msg.id);
                                }
                            }
                        });

                        self.scrollToBottom();
                        
                        if (!self.pollInterval) {
                            self.startPolling();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка загрузки сообщений:', error);
                    // Не показываем пользователю ошибки при polling, чтобы не раздражать
                    // Но в будущем можно показать после нескольких неудачных попыток
                }
            });
        },

        startPolling: function() {
            const self = this;
            
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }

            this.pollInterval = setInterval(function() {
                self.loadMessages();
            }, 3000);
        },

        addMessage: function(text, type, animate = true) {
            if (!text || typeof text !== 'string' || text.trim() === '') {
                console.error('Некорректный текст сообщения:', text);
                return;
            }

            const messageClass = type === 'user' ? 'user' : 'admin';
            const time = new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            
            const messageHtml = `
                <div class="aic-message ${messageClass}" style="${animate ? 'display:none;' : ''}">
                    <div class="aic-message-content">
                        ${this.escapeHtml(text)}
                        <div class="aic-message-time">${time}</div>
                    </div>
                </div>
            `;

            $('#aic-chat-messages').append(messageHtml);
            
            if (animate) {
                $('#aic-chat-messages .aic-message:last').fadeIn(300);
            }

            this.scrollToBottom();
        },

        addSystemMessage: function(text) {
            if (!text || typeof text !== 'string' || text.trim() === '') {
                return;
            }

            const messageHtml = `
                <div class="aic-message admin">
                    <div class="aic-message-content" style="background: #e3f2fd; color: #1976d2;">
                        ${this.escapeHtml(text)}
                    </div>
                </div>
            `;

            $('#aic-chat-messages').append(messageHtml);
            this.scrollToBottom();
        },

        scrollToBottom: function() {
            const messages = $('#aic-chat-messages');
            if (messages.length) {
                messages.scrollTop(messages[0].scrollHeight);
            }
        },

        escapeHtml: function(text) {
            if (!text || typeof text !== 'string') {
                return '';
            }
            
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return text.replace(/[&<>"']/g, function(m) { 
                return map[m]; 
            });
        }
    };

    if ($('#aic-chat-widget').length) {
        console.log('AI Chat Widget инициализирован');
        widget.init();
    }
});
