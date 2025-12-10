import Config from 'react-native-config';

export const API_CONFIG = {
  baseUrl: `${Config.WORDPRESS_URL}/wp-json/ai-chat/v1`,
  apiKey: Config.API_KEY,
  timeout: parseInt(Config.REQUEST_TIMEOUT) || 30000,
  pollingInterval: parseInt(Config.POLLING_INTERVAL) || 5000,
};

export const ENDPOINTS = {
  conversations: '/conversations',
  messages: (id) => `/messages/${id}`,
  send: '/send',
};
