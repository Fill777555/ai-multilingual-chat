import AsyncStorage from '@react-native-async-storage/async-storage';

export const Storage = {
  async setSessionId(sessionId) {
    await AsyncStorage.setItem('session_id', sessionId);
  },

  async getSessionId() {
    return await AsyncStorage.getItem('session_id');
  },

  async clearSession() {
    await AsyncStorage.removeItem('session_id');
  },
};
