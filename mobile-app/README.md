# AI Multilingual Chat - Mobile Application

React Native mobile application for the AI Multilingual Chat WordPress plugin. Provides a native mobile interface for administrators to manage chat conversations with automatic translation.

## 🚀 Features

- **Real-time Chat**: Communicate with users in real-time
- **Pull-to-Refresh**: Manual refresh conversations and messages
- **Auto-Polling**: Automatic updates every 5 seconds
- **Secure API**: API key authentication with secure storage
- **Error Handling**: Comprehensive error handling with retry logic
- **Bilingual Support**: Russian and English interface
- **Mobile Optimized**: Responsive design for all screen sizes
- **Keyboard Aware**: Smart keyboard handling
- **Connection Status**: Network error detection and handling

## 📋 Requirements

- Node.js >= 16
- React Native CLI
- iOS development: Xcode 12+ (macOS only)
- Android development: Android Studio and Android SDK
- WordPress site with AI Multilingual Chat plugin installed

## 🏗️ Architecture

### Project Structure

```
mobile-app/
├── src/
│   ├── config/          # Configuration files
│   │   └── api.config.js
│   ├── services/        # API services
│   │   └── ChatAPI.js
│   ├── screens/         # Screen components
│   │   ├── ConversationsScreen.js
│   │   └── ChatScreen.js
│   ├── components/      # Reusable components
│   │   ├── ConversationItem.js
│   │   └── MessageItem.js
│   ├── utils/           # Utility functions
│   │   ├── storage.js
│   │   └── i18n.js
│   ├── navigation/      # Navigation setup
│   │   └── AppNavigator.js
│   └── App.js           # Root component
├── .env.example         # Environment variables template
├── package.json         # Dependencies
└── README.md           # This file
```

### Key Components

#### API Layer (`src/services/ChatAPI.js`)
- Handles all API communication
- Implements retry logic with exponential backoff
- Categorizes errors (network, auth, server, validation)
- Request timeout handling
- Automatic retries for network failures

#### Navigation (`src/navigation/AppNavigator.js`)
- Stack-based navigation
- Conversations list → Individual chat
- Custom header styling
- Dynamic screen titles

#### Screens
- **ConversationsScreen**: Lists all conversations with pull-to-refresh
- **ChatScreen**: Individual chat interface with real-time updates

#### Components
- **ConversationItem**: Displays conversation preview
- **MessageItem**: Displays individual message bubble

## 🔧 Configuration

### Environment Variables

Create a `.env` file from `.env.example`:

```bash
cp .env.example .env
```

Configure the following variables:

```env
WORDPRESS_URL=https://your-wordpress-site.com
API_KEY=your_api_key_here
POLLING_INTERVAL=5000
REQUEST_TIMEOUT=30000
```

### Getting API Key

1. Log in to WordPress admin panel
2. Navigate to **AI Chat → Settings**
3. Find **Mobile API Key** section
4. Copy the generated API key
5. Paste into `.env` file

## 📦 Installation

See [INSTALLATION.md](./INSTALLATION.md) for detailed installation instructions.

Quick start:

```bash
# Install dependencies
npm install

# iOS only - install pods
cd ios && pod install && cd ..

# Run on Android
npm run android

# Run on iOS
npm run ios
```

## 🔐 Security Features

### API Key Protection
- API keys stored in environment variables
- Never committed to version control
- Loaded via `react-native-config`

### Secure Storage
- Uses AsyncStorage for session data
- Encrypted storage on device
- No sensitive data in plain text

### Error Handling
- Detailed error types for debugging
- Safe error messages for users
- Automatic retry for transient failures
- Request timeout protection

## 🌐 API Integration

The app integrates with WordPress REST API endpoints:

### GET `/wp-json/ai-chat/v1/conversations`
Returns list of all conversations

**Response:**
```json
{
  "conversations": [
    {
      "id": 1,
      "user_name": "John Doe",
      "user_language": "en",
      "admin_language": "ru",
      "status": "active",
      "created_at": "2024-01-01T12:00:00Z",
      "updated_at": "2024-01-01T13:00:00Z"
    }
  ]
}
```

### GET `/wp-json/ai-chat/v1/messages/{id}`
Returns messages for a specific conversation

**Response:**
```json
{
  "messages": [
    {
      "id": 1,
      "conversation_id": 1,
      "message": "Hello",
      "sender_type": "user",
      "created_at": "2024-01-01T12:00:00Z"
    }
  ]
}
```

### POST `/wp-json/ai-chat/v1/send`
Send a new message

**Request Body:**
```json
{
  "conversation_id": 1,
  "message": "Hello, how can I help you?",
  "sender_type": "admin"
}
```

**Response:**
```json
{
  "success": true,
  "message_id": 2
}
```

## 🎨 Customization

### Colors
Edit colors in component StyleSheet sections:
- Primary color: `#667eea`
- Background: `#f5f5f5`
- Text: `#333`

### Polling Interval
Adjust in `.env`:
```env
POLLING_INTERVAL=5000  # milliseconds
```

### Language
Default language is Russian. To change:
```javascript
// In screen files
const [language] = useState('en');
```

## 🐛 Troubleshooting

### API Key Invalid
- Verify API key in WordPress settings
- Check `.env` file configuration
- Ensure WordPress plugin is activated

### Connection Errors
- Verify WordPress site is accessible
- Check CORS settings in WordPress
- Ensure API endpoints are enabled

### Build Errors
- Clear cache: `npm start -- --reset-cache`
- Reinstall: `rm -rf node_modules && npm install`
- iOS: `cd ios && pod install && cd ..`

## 📱 Testing

See [TESTING_CHECKLIST.md](./TESTING_CHECKLIST.md) for complete testing guide.

## 📄 License

Same as parent project.

## 🤝 Contributing

Contributions welcome! Please ensure:
- Code follows existing style
- All tests pass
- Documentation is updated
- No security vulnerabilities introduced

## 📞 Support

For issues related to:
- **Mobile app**: Open issue in this repository
- **WordPress plugin**: Check plugin documentation
- **API integration**: Verify WordPress plugin version compatibility
