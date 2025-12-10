import Config from 'react-native-config';

// API Configuration
export const API_CONFIG = {
  baseUrl: `${Config.WORDPRESS_URL}/wp-json/ai-chat/v1`,
  apiKey: Config.API_KEY,
  timeout: parseInt(Config.REQUEST_TIMEOUT) || 30000,
  pollingInterval: parseInt(Config.POLLING_INTERVAL) || 5000,
};

// API Endpoints
export const ENDPOINTS = {
  conversations: '/conversations',
  messages: (id) => `/messages/${id}`,
  send: '/send',
};

// Retry Configuration
export const RETRY_CONFIG = {
  maxRetries: 3,
  baseDelay: 1000, // 1 second base delay for retry
};

// UI Configuration
export const UI_CONFIG = {
  scrollDelay: 100, // Delay before scrolling to bottom after loading messages
  maxMessageLength: 1000, // Maximum message length
  defaultLanguage: 'ru', // Default language for the app (ru or en)
};
