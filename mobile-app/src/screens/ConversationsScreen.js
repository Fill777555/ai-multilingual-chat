import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  FlatList,
  ActivityIndicator,
  StyleSheet,
  Text,
  RefreshControl,
  Alert,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import ConversationItem from '../components/ConversationItem';
import { ChatAPI, APIError } from '../services/ChatAPI';
import { t } from '../utils/i18n';
import { API_CONFIG, UI_CONFIG } from '../config/api.config';

const ConversationsScreen = ({ navigation }) => {
  const [conversations, setConversations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [language] = useState(UI_CONFIG.defaultLanguage);

  const loadConversations = useCallback(async (isRefreshing = false) => {
    try {
      if (!isRefreshing) {
        setLoading(true);
      }
      const data = await ChatAPI.getConversations();
      setConversations(data.conversations || []);
    } catch (error) {
      if (error instanceof APIError) {
        switch (error.type) {
          case 'auth':
            Alert.alert(t('authError', language), error.message);
            break;
          case 'network':
            Alert.alert(t('networkError', language), error.message);
            break;
          case 'server':
            Alert.alert(t('serverError', language), error.message);
            break;
          default:
            Alert.alert('Error', error.message);
        }
      } else {
        Alert.alert('Error', error.message || 'Unknown error');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [language]);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    loadConversations(true);
  }, [loadConversations]);

  useEffect(() => {
    loadConversations();

    // Set up polling for new conversations
    const pollInterval = setInterval(() => {
      loadConversations(true);
    }, API_CONFIG.pollingInterval);

    // Cleanup interval on unmount
    return () => {
      clearInterval(pollInterval);
    };
  }, [loadConversations]);

  const handleConversationPress = (conversation) => {
    navigation.navigate('Chat', {
      conversationId: conversation.id,
      userName: conversation.user_name || t('guest', language),
    });
  };

  const renderItem = ({ item }) => (
    <ConversationItem
      conversation={item}
      onPress={handleConversationPress}
      language={language}
    />
  );

  const renderEmpty = () => (
    <View style={styles.emptyContainer}>
      <Text style={styles.emptyText}>{t('noConversations', language)}</Text>
    </View>
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
      <FlatList
        data={conversations}
        renderItem={renderItem}
        keyExtractor={(item) => item.id.toString()}
        ListEmptyComponent={renderEmpty}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={['#667eea']}
            tintColor="#667eea"
          />
        }
      />
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
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 40,
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
  },
});

export default ConversationsScreen;
