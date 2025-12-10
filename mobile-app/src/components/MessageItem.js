import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

const MessageItem = ({ message, isAdmin = false }) => {
  const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const isFromAdmin = message.sender_type === 'admin';

  return (
    <View style={[
      styles.container,
      isFromAdmin ? styles.adminContainer : styles.userContainer
    ]}>
      <View style={[
        styles.bubble,
        isFromAdmin ? styles.adminBubble : styles.userBubble
      ]}>
        <Text style={[
          styles.messageText,
          isFromAdmin ? styles.adminText : styles.userText
        ]}>
          {message.message}
        </Text>
        <Text style={[
          styles.timeText,
          isFromAdmin ? styles.adminTimeText : styles.userTimeText
        ]}>
          {formatTime(message.created_at)}
        </Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    marginVertical: 4,
    marginHorizontal: 12,
  },
  adminContainer: {
    justifyContent: 'flex-end',
  },
  userContainer: {
    justifyContent: 'flex-start',
  },
  bubble: {
    maxWidth: '75%',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 18,
  },
  adminBubble: {
    backgroundColor: '#667eea',
    borderBottomRightRadius: 4,
  },
  userBubble: {
    backgroundColor: '#e0e0e0',
    borderBottomLeftRadius: 4,
  },
  messageText: {
    fontSize: 15,
    lineHeight: 20,
  },
  adminText: {
    color: '#fff',
  },
  userText: {
    color: '#333',
  },
  timeText: {
    fontSize: 11,
    marginTop: 4,
  },
  adminTimeText: {
    color: 'rgba(255, 255, 255, 0.7)',
    textAlign: 'right',
  },
  userTimeText: {
    color: '#999',
    textAlign: 'left',
  },
});

export default MessageItem;
