/**
 * Simple i18n (internationalization) for AI Multilingual Chat
 */
(function($) {
    'use strict';
    
    const AIC_i18n = {
        translations: {
            'ru': {
                'welcome': 'Добро пожаловать!',
                'introduce_yourself': 'Представьтесь, пожалуйста, чтобы начать чат',
                'your_name': 'Ваше имя',
                'start_chat': 'Начать чат',
                'type_message': 'Введите сообщение...',
                'send': 'Отправить',
                'online': 'Онлайн',
                'offline': 'Оффлайн',
                'close': 'Закрыть',
                'enter_name': 'Пожалуйста, введите ваше имя',
                'connection_error': 'Ошибка соединения. Попробуйте позже.',
                'chat_support': 'Поддержка'
            },
            'en': {
                'welcome': 'Welcome!',
                'introduce_yourself': 'Please introduce yourself to start the chat',
                'your_name': 'Your name',
                'start_chat': 'Start Chat',
                'type_message': 'Type a message...',
                'send': 'Send',
                'online': 'Online',
                'offline': 'Offline',
                'close': 'Close',
                'enter_name': 'Please enter your name',
                'connection_error': 'Connection error. Please try again later.',
                'chat_support': 'Chat Support'
            },
            'uk': {
                'welcome': 'Ласкаво просимо!',
                'introduce_yourself': 'Будь ласка, представтеся, щоб почати чат',
                'your_name': 'Ваше ім\'я',
                'start_chat': 'Почати чат',
                'type_message': 'Введіть повідомлення...',
                'send': 'Відправити',
                'online': 'Онлайн',
                'offline': 'Офлайн',
                'close': 'Закрити',
                'enter_name': 'Будь ласка, введіть ваше ім\'я',
                'connection_error': 'Помилка з\'єднання. Спробуйте пізніше.',
                'chat_support': 'Підтримка'
            },
            'es': {
                'welcome': '¡Bienvenido!',
                'introduce_yourself': 'Por favor preséntese para iniciar el chat',
                'your_name': 'Su nombre',
                'start_chat': 'Iniciar Chat',
                'type_message': 'Escriba un mensaje...',
                'send': 'Enviar',
                'online': 'En línea',
                'offline': 'Fuera de línea',
                'close': 'Cerrar',
                'enter_name': 'Por favor ingrese su nombre',
                'connection_error': 'Error de conexión. Inténtelo más tarde.',
                'chat_support': 'Soporte de Chat'
            },
            'de': {
                'welcome': 'Willkommen!',
                'introduce_yourself': 'Bitte stellen Sie sich vor, um den Chat zu starten',
                'your_name': 'Ihr Name',
                'start_chat': 'Chat starten',
                'type_message': 'Nachricht eingeben...',
                'send': 'Senden',
                'online': 'Online',
                'offline': 'Offline',
                'close': 'Schließen',
                'enter_name': 'Bitte geben Sie Ihren Namen ein',
                'connection_error': 'Verbindungsfehler. Bitte versuchen Sie es später.',
                'chat_support': 'Chat-Support'
            },
            'fr': {
                'welcome': 'Bienvenue!',
                'introduce_yourself': 'Veuillez vous présenter pour commencer le chat',
                'your_name': 'Votre nom',
                'start_chat': 'Démarrer le chat',
                'type_message': 'Tapez un message...',
                'send': 'Envoyer',
                'online': 'En ligne',
                'offline': 'Hors ligne',
                'close': 'Fermer',
                'enter_name': 'Veuillez entrer votre nom',
                'connection_error': 'Erreur de connexion. Veuillez réessayer plus tard.',
                'chat_support': 'Support de chat'
            },
            'it': {
                'welcome': 'Benvenuto!',
                'introduce_yourself': 'Per favore presentati per iniziare la chat',
                'your_name': 'Il tuo nome',
                'start_chat': 'Inizia Chat',
                'type_message': 'Digita un messaggio...',
                'send': 'Invia',
                'online': 'Online',
                'offline': 'Offline',
                'close': 'Chiudi',
                'enter_name': 'Per favore inserisci il tuo nome',
                'connection_error': 'Errore di connessione. Riprova più tardi.',
                'chat_support': 'Supporto Chat'
            },
            'pt': {
                'welcome': 'Bem-vindo!',
                'introduce_yourself': 'Por favor apresente-se para iniciar o chat',
                'your_name': 'Seu nome',
                'start_chat': 'Iniciar Chat',
                'type_message': 'Digite uma mensagem...',
                'send': 'Enviar',
                'online': 'Online',
                'offline': 'Offline',
                'close': 'Fechar',
                'enter_name': 'Por favor insira seu nome',
                'connection_error': 'Erro de conexão. Tente novamente mais tarde.',
                'chat_support': 'Suporte de Chat'
            },
            'zh': {
                'welcome': '欢迎！',
                'introduce_yourself': '请自我介绍以开始聊天',
                'your_name': '您的名字',
                'start_chat': '开始聊天',
                'type_message': '输入消息...',
                'send': '发送',
                'online': '在线',
                'offline': '离线',
                'close': '关闭',
                'enter_name': '请输入您的名字',
                'connection_error': '连接错误。请稍后再试。',
                'chat_support': '聊天支持'
            },
            'ja': {
                'welcome': 'ようこそ！',
                'introduce_yourself': 'チャットを開始するには自己紹介してください',
                'your_name': 'あなたの名前',
                'start_chat': 'チャット開始',
                'type_message': 'メッセージを入力...',
                'send': '送信',
                'online': 'オンライン',
                'offline': 'オフライン',
                'close': '閉じる',
                'enter_name': 'お名前を入力してください',
                'connection_error': '接続エラー。後でもう一度お試しください。',
                'chat_support': 'チャットサポート'
            }
        },
        
        currentLang: 'ru',
        
        init: function(language) {
            this.currentLang = language || this.detectLanguage();
        },
        
        detectLanguage: function() {
            // Try to get language from browser
            const browserLang = navigator.language || navigator.userLanguage;
            const lang = browserLang.substring(0, 2);
            
            return this.translations[lang] ? lang : 'en';
        },
        
        t: function(key) {
            if (this.translations[this.currentLang] && this.translations[this.currentLang][key]) {
                return this.translations[this.currentLang][key];
            }
            
            // Fallback to English
            if (this.translations['en'] && this.translations['en'][key]) {
                return this.translations['en'][key];
            }
            
            // Fallback to key itself
            return key;
        },
        
        setLanguage: function(language) {
            if (this.translations[language]) {
                this.currentLang = language;
            }
        }
    };
    
    // Export to global scope
    window.AIC_i18n = AIC_i18n;
    
})(jQuery);
