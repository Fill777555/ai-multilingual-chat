import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  FlatList,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  Alert,
  Text,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import MessageItem from '../components/MessageItem';
import { ChatAPI, APIError } from '../services/ChatAPI';
import { t } from '../utils/i18n';
import { API_CONFIG, UI_CONFIG } from '../config/api.config';

const ChatScreen = ({ route }) => {
  const { conversationId } = route.params;
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [language] = useState(UI_CONFIG.defaultLanguage);
  const flatListRef = useRef(null);
  const pollIntervalRef = useRef(null);

  const loadMessages = useCallback(async () => {
    try {
      const data = await ChatAPI.getMessages(conversationId);
      setMessages(data.messages || []);
      // Scroll to bottom after loading messages
      setTimeout(() => {
        flatListRef.current?.scrollToEnd({ animated: true });
      }, UI_CONFIG.scrollDelay);
    } catch (error) {
      if (error instanceof APIError) {
        switch (error.type) {
          case 'auth':
            Alert.alert(t('authError', language), error.message);
            break;
          case 'network':
            // Silent fail for polling errors
            console.warn('Network error loading messages:', error.message);
            break;
          case 'server':
            Alert.alert(t('serverError', language), error.message);
            break;
          default:
            Alert.alert('Error', error.message);
        }
      }
    } finally {
      setLoading(false);
    }
  }, [conversationId, language]);

  useEffect(() => {
    // Initial load
    loadMessages();

    // Set up polling for new messages
    pollIntervalRef.current = setInterval(() => {
      loadMessages();
    }, API_CONFIG.pollingInterval);

    // Cleanup on unmount
    return () => {
      if (pollIntervalRef.current) {
        clearInterval(pollIntervalRef.current);
      }
    };
  }, [loadMessages]);

  const handleSend = async () => {
    if (!inputText.trim() || sending) return;

    const messageToSend = inputText.trim();
    setInputText('');
    setSending(true);

    try {
      await ChatAPI.sendMessage(conversationId, messageToSend, 'admin');
      // Reload messages after sending
      await loadMessages();
    } catch (error) {
      // Restore input text on error
      setInputText(messageToSend);
      
      if (error instanceof APIError) {
        switch (error.type) {
          case 'auth':
            Alert.alert(t('authError', language), error.message);
            break;
          case 'network':
            Alert.alert(t('networkError', language), t('retrying', language));
            break;
          case 'server':
            Alert.alert(t('serverError', language), error.message);
            break;
          default:
            Alert.alert(t('errorSending', language), error.message);
        }
      } else {
        Alert.alert(t('errorSending', language), error.message);
      }
    } finally {
      setSending(false);
    }
  };

  const renderItem = ({ item }) => (
    <MessageItem message={item} />
  );

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#667eea" />
          <Text style={styles.loadingText}>{t('loading', language)}</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['bottom']}>
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 90 : 0}
      >
        <FlatList
          ref={flatListRef}
          data={messages}
          renderItem={renderItem}
          keyExtractor={(item, index) => `${item.id}-${index}`}
          contentContainerStyle={styles.messagesList}
          onContentSizeChange={() => flatListRef.current?.scrollToEnd({ animated: true })}
        />

        <View style={styles.inputContainer}>
          <TextInput
            style={styles.input}
            value={inputText}
            onChangeText={setInputText}
            placeholder={t('enterMessage', language)}
            placeholderTextColor="#999"
            multiline
            maxLength={UI_CONFIG.maxMessageLength}
            onSubmitEditing={handleSend}
            blurOnSubmit={false}
            editable={!sending}
          />
          <TouchableOpacity
            style={[
              styles.sendButton,
              (!inputText.trim() || sending) && styles.sendButtonDisabled
            ]}
            onPress={handleSend}
            disabled={!inputText.trim() || sending}
          >
            {sending ? (
              <ActivityIndicator size="small" color="#fff" />
            ) : (
              <Text style={styles.sendButtonText}>{t('send', language)}</Text>
            )}
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  messagesList: {
    paddingVertical: 10,
  },
  inputContainer: {
    flexDirection: 'row',
    padding: 10,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#e0e0e0',
    alignItems: 'flex-end',
  },
  input: {
    flex: 1,
    minHeight: 40,
    maxHeight: 100,
    backgroundColor: '#f5f5f5',
    borderRadius: 20,
    paddingHorizontal: 16,
    paddingVertical: 10,
    fontSize: 15,
    marginRight: 10,
  },
  sendButton: {
    backgroundColor: '#667eea',
    borderRadius: 20,
    paddingHorizontal: 20,
    paddingVertical: 10,
    justifyContent: 'center',
    alignItems: 'center',
    minWidth: 70,
    height: 40,
  },
  sendButtonDisabled: {
    backgroundColor: '#ccc',
  },
  sendButtonText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: '600',
  },
});

export default ChatScreen;
