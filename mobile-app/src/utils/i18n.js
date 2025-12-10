export const translations = {
  ru: {
    conversations: 'Разговоры',
    selectConversation: 'Выберите разговор',
    enterMessage: 'Введите сообщение...',
    send: 'Отправить',
    guest: 'Гость',
    loading: 'Загрузка...',
    errorSending: 'Ошибка отправки',
    networkError: 'Ошибка сети',
    authError: 'Ошибка авторизации',
    serverError: 'Ошибка сервера',
    retrying: 'Повторная попытка...',
    noConversations: 'Нет разговоров',
    pullToRefresh: 'Потяните для обновления',
  },
  en: {
    conversations: 'Conversations',
    selectConversation: 'Select conversation',
    enterMessage: 'Enter message...',
    send: 'Send',
    guest: 'Guest',
    loading: 'Loading...',
    errorSending: 'Error sending',
    networkError: 'Network error',
    authError: 'Authorization error',
    serverError: 'Server error',
    retrying: 'Retrying...',
    noConversations: 'No conversations',
    pullToRefresh: 'Pull to refresh',
  },
};

export const t = (key, lang = 'ru') => translations[lang][key] || key;
