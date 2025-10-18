jQuery(document).ready(function($) {
    const widget = {
        sessionId: null,
        conversationId: null,
        userName: null,
        userLanguage: 'ru',
        pollInterval: null,
        lastMessageId: 0,
        isInitialized: false,
        notificationSound: null,
        soundEnabled: true,

        init: function() {
            this.sessionId = this.getOrCreateSessionId();
            this.initNotificationSound();
            this.bindEvents();
            this.checkExistingConversation();
        },

        initNotificationSound: function() {
            // Load user's sound preference from localStorage
            const savedSoundEnabled = localStorage.getItem('aic_sound_enabled');
            if (savedSoundEnabled !== null) {
                this.soundEnabled = savedSoundEnabled === 'true';
            }
            
            // Get sound choice from localStorage or use default
            const soundChoice = localStorage.getItem('aic_client_notification_sound') || 'default';
            const soundUrl = aicFrontend.sound_base_url + 'notification-' + soundChoice + '.mp3';
            
            this.notificationSound = new Audio(soundUrl);
            
            // Handle error loading sound
            this.notificationSound.addEventListener('error', function() {
                console.warn('Could not load selected sound, falling back to default');
                const fallbackUrl = aicFrontend.sound_base_url + 'notification-default.mp3';
                this.notificationSound = new Audio(fallbackUrl);
            }.bind(this));
        },

        playNotificationSound: function() {
            if (this.notificationSound && this.soundEnabled && aicFrontend.enable_sound === '1') {
                this.notificationSound.play().catch(function(e) {
                    console.log('Could not play notification sound:', e);
                });
            }
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

            // Initialize i18n with default language
            if (window.AIC_i18n) {
                AIC_i18n.init('en');
            }

            // Sound toggle button
            $(document).on('click', '#aic-sound-toggle', function() {
                self.soundEnabled = !self.soundEnabled;
                localStorage.setItem('aic_sound_enabled', self.soundEnabled);
                self.updateSoundButton();
                
                // Play a test sound if enabled
                if (self.soundEnabled) {
                    self.playNotificationSound();
                }
            });

            // Handle language dropdown change
            $('#aic-user-language').on('change', function() {
                const selectedLang = $(this).val();
                if (window.AIC_i18n) {
                    AIC_i18n.setLanguage(selectedLang);
                    self.updateWelcomeScreen();
                }
            });

            $('#aic-chat-button').on('click', function() {
                $('#aic-chat-window').slideToggle(300);
                self.updateSoundButton();
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
                
                // Send typing indicator
                clearTimeout(self.typingTimer);
                self.sendTypingStatus(true);
                self.typingTimer = setTimeout(function() {
                    self.sendTypingStatus(false);
                }, 1000);
            });
            
            // Sound settings modal
            $(document).on('click', '#aic-sound-settings', function() {
                self.openSoundModal();
            });

            $(document).on('click', '.aic-modal-close, .aic-modal-overlay', function() {
                $('#aic-sound-modal').fadeOut(300);
            });

            $(document).on('click', '.aic-sound-item', function() {
                $('.aic-sound-item').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
                
                const soundKey = $(this).find('input[type="radio"]').val();
                localStorage.setItem('aic_client_notification_sound', soundKey);
                self.initNotificationSound(); // Reload sound with new choice
            });

            $(document).on('click', '.aic-sound-preview', function(e) {
                e.stopPropagation();
                const soundKey = $(this).data('sound');
                self.previewSound(soundKey);
            });
        },

        sendTypingStatus: function(isTyping) {
            if (!this.conversationId) return;
            
            $.ajax({
                url: aicFrontend.ajax_url,
                type: 'POST',
                timeout: 5000,
                data: {
                    action: 'aic_user_typing',
                    nonce: aicFrontend.nonce,
                    conversation_id: this.conversationId,
                    is_typing: isTyping ? 1 : 0
                },
                error: function(xhr, status, error) {
                    // Silent fail for typing indicator - not critical
                    console.log('Typing indicator failed (non-critical):', status);
                }
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
                    } else if (!response.success && response.data && response.data.code === 'nonce_failed') {
                        // Nonce verification failed
                        alert('Security token expired. Please refresh the page and try again.');
                    } else {
                        alert('Ошибка создания разговора: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка создания разговора:', error, 'Status:', xhr.status);
                    
                    // Handle 403 Forbidden specifically
                    if (xhr.status === 403) {
                        alert('Security token expired. Please refresh the page and try again.');
                    } else {
                        alert('Ошибка соединения с сервером');
                    }
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
                        // Update lastMessageId to prevent duplication from polling
                        if (response.data.message_id) {
                            self.lastMessageId = Math.max(self.lastMessageId, parseInt(response.data.message_id));
                            console.log('Updated lastMessageId to:', self.lastMessageId);
                        }
                    } else if (!response.success && response.data && response.data.code === 'nonce_failed') {
                        // Nonce verification failed
                        console.error('Nonce verification failed');
                        self.addSystemMessage('Security token expired. Please refresh the page to continue.');
                        if (self.pollInterval) {
                            clearInterval(self.pollInterval);
                            self.pollInterval = null;
                        }
                    } else {
                        console.error('Ошибка:', response.data);
                        const errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                        self.addSystemMessage('Ошибка отправки сообщения: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX ошибка:', error, 'Status:', xhr.status);
                    
                    // Handle 403 Forbidden specifically
                    if (xhr.status === 403) {
                        console.error('403 Forbidden - security check failed');
                        self.addSystemMessage('Security token expired. Please refresh the page to continue.');
                        if (self.pollInterval) {
                            clearInterval(self.pollInterval);
                            self.pollInterval = null;
                        }
                    } else {
                        self.addSystemMessage('Ошибка отправки сообщения. Попробуйте еще раз.');
                    }
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

                        let hasNewAdminMessage = false;
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
                                    
                                    // Track if there's a new admin message (but not on initial load)
                                    if (msg.sender_type === 'admin' && self.isInitialized) {
                                        hasNewAdminMessage = true;
                                    }
                                }
                            }
                        });
                        
                        // Play notification sound if there's a new admin message
                        if (hasNewAdminMessage) {
                            self.playNotificationSound();
                        }

                        self.scrollToBottom();
                        
                        if (!self.pollInterval) {
                            self.startPolling();
                        }
                    } else if (!response.success && response.data && response.data.code === 'nonce_failed') {
                        // Nonce verification failed - stop polling and show error
                        console.error('Nonce verification failed, please refresh the page');
                        if (self.pollInterval) {
                            clearInterval(self.pollInterval);
                            self.pollInterval = null;
                        }
                        self.addSystemMessage('Security token expired. Please refresh the page to continue.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка загрузки сообщений:', error, 'Status:', xhr.status);
                    
                    // Handle 403 Forbidden specifically
                    if (xhr.status === 403) {
                        console.error('403 Forbidden - security check failed');
                        if (self.pollInterval) {
                            clearInterval(self.pollInterval);
                            self.pollInterval = null;
                        }
                        self.addSystemMessage('Security token expired. Please refresh the page to continue.');
                    }
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
        },

        updateSoundButton: function() {
            const $button = $('#aic-sound-toggle');
            if ($button.length) {
                if (this.soundEnabled) {
                    $button.removeClass('sound-disabled');
                    $button.attr('title', 'Sound enabled - click to disable');
                } else {
                    $button.addClass('sound-disabled');
                    $button.attr('title', 'Sound disabled - click to enable');
                }
            }
        },

        updateWelcomeScreen: function() {
            if (window.AIC_i18n) {
                $('#aic-chat_support-header').text(AIC_i18n.t('chat_support'));
                $('#aic-online-header').text(AIC_i18n.t('online'));
                $('#aic-welcome-heading').text(AIC_i18n.t('welcome'));
                $('#aic-welcome-text').text(AIC_i18n.t('introduce_yourself'));
                $('#aic-user-name').attr('placeholder', AIC_i18n.t('your_name'));
                $('#aic-start-chat').text(AIC_i18n.t('start_chat'));
            }
        },
        
        openSoundModal: function() {
            const self = this;
            const currentSound = localStorage.getItem('aic_client_notification_sound') || 'default';
            
            let html = '';
            $.each(aicFrontend.available_sounds, function(key, label) {
                const checked = (key === currentSound) ? 'checked' : '';
                const selectedClass = (key === currentSound) ? 'selected' : '';
                
                html += `
                    <div class="aic-sound-item ${selectedClass}">
                        <input type="radio" name="sound_choice" value="${key}" id="sound_${key}" ${checked}>
                        <label for="sound_${key}">${label}</label>
                        <button class="aic-sound-preview" data-sound="${key}">🔊 Прослушать</button>
                    </div>
                `;
            });
            
            $('.aic-sound-list').html(html);
            $('#aic-sound-modal').fadeIn(300);
        },

        previewSound: function(soundKey) {
            const soundUrl = aicFrontend.sound_base_url + 'notification-' + soundKey + '.mp3';
            const previewAudio = new Audio(soundUrl);
            previewAudio.play().catch(function(e) {
                console.log('Could not play preview sound:', e);
                alert('Ошибка воспроизведения звука');
            });
        }
    };

    if ($('#aic-chat-widget').length) {
        console.log('AI Chat Widget инициализирован');
        widget.init();
    }
});
