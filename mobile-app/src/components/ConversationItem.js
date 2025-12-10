import React from 'react';
import { TouchableOpacity, View, Text, StyleSheet } from 'react-native';
import { t } from '../utils/i18n';

const ConversationItem = ({ conversation, onPress, language = 'ru' }) => {
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    
    if (hours < 1) {
      return t('justNow', language);
    } else if (hours < 24) {
      return `${hours}${t('hoursAgo', language)}`;
    } else {
      return date.toLocaleDateString();
    }
  };

  return (
    <TouchableOpacity
      style={styles.container}
      onPress={() => onPress(conversation)}
      activeOpacity={0.7}
    >
      <View style={styles.avatar}>
        <Text style={styles.avatarText}>
          {conversation.user_name ? conversation.user_name[0].toUpperCase() : '?'}
        </Text>
      </View>
      
      <View style={styles.content}>
        <View style={styles.header}>
          <Text style={styles.userName} numberOfLines={1}>
            {conversation.user_name || t('guest', language)}
          </Text>
          <Text style={styles.time}>
            {formatDate(conversation.updated_at || conversation.created_at)}
          </Text>
        </View>
        
        <View style={styles.details}>
          <Text style={styles.language}>
            {conversation.user_language || 'en'} → {conversation.admin_language || 'ru'}
          </Text>
          <View style={[styles.statusBadge, 
            conversation.status === 'active' ? styles.statusActive : styles.statusClosed
          ]}>
            <Text style={styles.statusText}>{conversation.status}</Text>
          </View>
        </View>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    padding: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  avatar: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#667eea',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  avatarText: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    justifyContent: 'center',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  userName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    flex: 1,
  },
  time: {
    fontSize: 12,
    color: '#999',
    marginLeft: 8,
  },
  details: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  language: {
    fontSize: 13,
    color: '#666',
    marginRight: 8,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
  },
  statusActive: {
    backgroundColor: '#4caf50',
  },
  statusClosed: {
    backgroundColor: '#999',
  },
  statusText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: '500',
  },
});

export default ConversationItem;
