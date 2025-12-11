import { API_CONFIG, ENDPOINTS, RETRY_CONFIG } from '../config/api.config';

class APIError extends Error {
  constructor(message, status, type) {
    super(message);
    this.name = 'APIError';
    this.status = status;
    this.type = type; // 'network', 'auth', 'server', 'validation'
  }
}

export class ChatAPI {
  static async request(endpoint, options = {}) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), API_CONFIG.timeout);

    try {
      const response = await fetch(`${API_CONFIG.baseUrl}${endpoint}`, {
        ...options,
        signal: controller.signal,
        headers: {
          'X-API-Key': API_CONFIG.apiKey,
          'Content-Type': 'application/json',
          ...options.headers,
        },
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        
        if (response.status === 403) {
          throw new APIError('Invalid API key', 403, 'auth');
        }
        if (response.status >= 500) {
          throw new APIError('Server error', response.status, 'server');
        }
        
        throw new APIError(
          errorData.message || `HTTP error ${response.status}`,
          response.status,
          'validation'
        );
      }

      return await response.json();
    } catch (error) {
      clearTimeout(timeoutId);
      
      if (error.name === 'AbortError') {
        throw new APIError('Request timeout', 0, 'network');
      }
      if (error instanceof APIError) {
        throw error;
      }
      throw new APIError('Network error', 0, 'network');
    }
  }

  static async requestWithRetry(endpoint, options = {}) {
    const maxRetries = RETRY_CONFIG.maxRetries;
    let lastError;
    for (let i = 0; i < maxRetries; i++) {
      try {
        return await this.request(endpoint, options);
      } catch (error) {
        lastError = error;
        // Don't retry on auth or validation errors
        if (error.type === 'auth' || error.type === 'validation') {
          throw error;
        }
        // Wait before retrying (exponential backoff)
        const delay = RETRY_CONFIG.baseDelay * (i + 1);
        await new Promise(resolve => setTimeout(resolve, delay));
      }
    }
    throw lastError;
  }

  static getConversations() {
    return this.requestWithRetry(ENDPOINTS.conversations);
  }

  static getMessages(conversationId) {
    return this.requestWithRetry(ENDPOINTS.messages(conversationId));
  }

  static sendMessage(conversationId, message, senderType = 'admin') {
    return this.requestWithRetry(ENDPOINTS.send, {
      method: 'POST',
      body: JSON.stringify({
        conversation_id: conversationId,
        message: message,
        sender_type: senderType,
      }),
    });
  }
}

export { APIError };
