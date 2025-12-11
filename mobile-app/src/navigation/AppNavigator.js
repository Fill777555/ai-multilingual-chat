import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import ConversationsScreen from '../screens/ConversationsScreen';
import ChatScreen from '../screens/ChatScreen';
import { t } from '../utils/i18n';
import { UI_CONFIG } from '../config/api.config';

const Stack = createStackNavigator();

export default function AppNavigator() {
  const language = UI_CONFIG.defaultLanguage;
  
  return (
    <NavigationContainer>
      <Stack.Navigator
        initialRouteName="Conversations"
        screenOptions={{
          headerStyle: { backgroundColor: '#667eea' },
          headerTintColor: '#fff',
          headerTitleStyle: { fontWeight: 'bold' },
        }}
      >
        <Stack.Screen
          name="Conversations"
          component={ConversationsScreen}
          options={{ title: t('conversations', language) }}
        />
        <Stack.Screen
          name="Chat"
          component={ChatScreen}
          options={({ route }) => ({ title: route.params?.userName || t('chat', language) })}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
