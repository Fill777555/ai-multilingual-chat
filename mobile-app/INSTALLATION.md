# Installation Guide - AI Multilingual Chat Mobile App

Complete step-by-step installation guide for setting up the mobile application.

## Prerequisites

### Required Software

1. **Node.js** (v16 or higher)
   ```bash
   # Check version
   node --version
   
   # Install from https://nodejs.org/
   ```

2. **React Native CLI**
   ```bash
   npm install -g react-native-cli
   ```

3. **Git**
   ```bash
   # Check if installed
   git --version
   ```

### Platform-Specific Requirements

#### For iOS Development (macOS only)

1. **Xcode** (v12 or higher)
   - Download from Mac App Store
   - Install Command Line Tools:
     ```bash
     xcode-select --install
     ```

2. **CocoaPods**
   ```bash
   sudo gem install cocoapods
   ```

3. **Watchman** (optional but recommended)
   ```bash
   brew install watchman
   ```

#### For Android Development

1. **Android Studio**
   - Download from https://developer.android.com/studio
   - Install Android SDK (API 28 or higher)
   - Set up Android Virtual Device (AVD)

2. **Java Development Kit (JDK 11)**
   ```bash
   # Check version
   java -version
   ```

3. **Environment Variables**
   Add to `~/.bash_profile` or `~/.zshrc`:
   ```bash
   export ANDROID_HOME=$HOME/Library/Android/sdk
   export PATH=$PATH:$ANDROID_HOME/emulator
   export PATH=$PATH:$ANDROID_HOME/tools
   export PATH=$PATH:$ANDROID_HOME/tools/bin
   export PATH=$PATH:$ANDROID_HOME/platform-tools
   ```

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/your-username/ai-multilingual-chat.git
cd ai-multilingual-chat/mobile-app
```

### 2. Install Dependencies

```bash
npm install
```

For iOS (macOS only):
```bash
cd ios
pod install
cd ..
```

### 3. Configure Environment Variables

#### Create `.env` file

```bash
cp .env.example .env
```

#### Edit `.env` file

```env
# Your WordPress site URL (no trailing slash)
WORDPRESS_URL=https://your-wordpress-site.com

# API key from WordPress admin
API_KEY=your_actual_api_key_here

# Optional: Adjust polling and timeout
POLLING_INTERVAL=5000
REQUEST_TIMEOUT=30000
```

### 4. Get API Key from WordPress

#### Step 4.1: Install WordPress Plugin

1. Log in to WordPress admin panel
2. Navigate to **Plugins → Add New**
3. Search for "AI Multilingual Chat"
4. Install and activate

#### Step 4.2: Generate API Key

1. Navigate to **AI Chat → Settings**
2. Find **Mobile API** section
3. Click **Generate API Key**
4. Copy the generated key
5. Paste into `.env` file

#### Step 4.3: Verify API Endpoint

Test the API endpoint:
```bash
curl -H "X-API-Key: your_api_key_here" \
  https://your-wordpress-site.com/wp-json/ai-chat/v1/conversations
```

Expected response:
```json
{
  "conversations": []
}
```

### 5. Configure CORS (WordPress)

Add to WordPress `wp-config.php` or plugin settings:

```php
// Allow mobile app requests
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: X-API-Key, Content-Type');
        return $value;
    });
}, 15);
```

### 6. Run Application

#### Option A: Android

##### Start Metro Bundler
```bash
npm start
```

##### In another terminal, run Android
```bash
npm run android
```

Or manually:
```bash
# Launch emulator from Android Studio
# Then:
react-native run-android
```

#### Option B: iOS (macOS only)

##### Start Metro Bundler
```bash
npm start
```

##### In another terminal, run iOS
```bash
npm run ios
```

Or manually:
```bash
# Open Xcode
open ios/AiChatMobile.xcworkspace

# Select simulator and press Run
# Or from terminal:
react-native run-ios
```

#### Option C: Physical Device

##### Android Physical Device

1. Enable USB debugging on device:
   - Settings → About phone → Tap "Build number" 7 times
   - Settings → Developer options → Enable USB debugging

2. Connect device via USB

3. Verify device is connected:
   ```bash
   adb devices
   ```

4. Run app:
   ```bash
   npm run android
   ```

##### iOS Physical Device

1. Open `ios/AiChatMobile.xcworkspace` in Xcode

2. Select your device from device list

3. Configure signing:
   - Select project in navigator
   - Select target
   - Go to "Signing & Capabilities"
   - Select your team

4. Click Run button

## Troubleshooting

### Common Issues

#### Issue: "Unable to resolve module"

**Solution:**
```bash
# Clear cache
npm start -- --reset-cache

# Or
watchman watch-del-all
rm -rf node_modules
npm install
```

#### Issue: "Could not connect to development server"

**Solution:**
```bash
# Check if Metro bundler is running
npm start

# For Android, check ADB connection
adb reverse tcp:8081 tcp:8081
```

#### Issue: "ANDROID_HOME not set"

**Solution:**
```bash
# Add to ~/.bash_profile or ~/.zshrc
export ANDROID_HOME=$HOME/Library/Android/sdk
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/platform-tools

# Reload shell
source ~/.bash_profile  # or source ~/.zshrc
```

#### Issue: "CocoaPods not installed" (iOS)

**Solution:**
```bash
sudo gem install cocoapods
cd ios
pod install
cd ..
```

#### Issue: "Error: EMFILE: too many open files"

**Solution:**
```bash
# Install watchman
brew install watchman

# Or increase file limit
ulimit -n 4096
```

#### Issue: API returns 403 Forbidden

**Solutions:**
1. Verify API key is correct in `.env`
2. Check WordPress plugin is activated
3. Verify CORS settings in WordPress
4. Test API endpoint with curl (see step 4.3)

#### Issue: API returns 0 Network error

**Solutions:**
1. Check WordPress site is accessible
2. Verify URL in `.env` (no trailing slash)
3. Check device/emulator has internet connection
4. For Android emulator, check network settings
5. For iOS simulator, check network permissions

### Build Errors

#### Android Build Failed

```bash
# Clean build
cd android
./gradlew clean
cd ..

# Rebuild
npm run android
```

#### iOS Build Failed

```bash
# Clean build
cd ios
xcodebuild clean
pod install
cd ..

# Rebuild
npm run ios
```

### Performance Issues

#### Slow Metro Bundler

```bash
# Disable source maps in development
# Add to metro.config.js
module.exports = {
  transformer: {
    sourceExporter: false,
  },
};
```

#### Slow App Launch

```bash
# Enable Hermes engine (if not enabled)
# Edit android/app/build.gradle
def enableHermes = project.ext.react.get("enableHermes", true)
```

## Verification

### Test Installation Success

1. **Launch app**
   - App should open without crashes
   - Navigation bar should display "Разговоры"

2. **Test API connection**
   - Pull down to refresh
   - Should show loading indicator
   - Should display conversations or "No conversations"

3. **Test navigation**
   - Tap a conversation (if any)
   - Should navigate to chat screen
   - Back button should work

4. **Test sending message**
   - Type a message
   - Tap send button
   - Message should appear in chat

### Check Logs

#### React Native Logs
```bash
# In Metro bundler terminal
# Look for errors in yellow or red
```

#### Android Logs
```bash
adb logcat *:S ReactNative:V ReactNativeJS:V
```

#### iOS Logs
```bash
# In Xcode: View → Debug Area → Show Debug Area
# Look for NSLog output
```

## Next Steps

1. Review [README.md](./README.md) for app features
2. Check [TESTING_CHECKLIST.md](./TESTING_CHECKLIST.md) for testing guide
3. Configure app settings as needed
4. Test on multiple devices/screen sizes

## Support

If you encounter issues not covered here:

1. Check GitHub issues: https://github.com/your-repo/issues
2. Review React Native troubleshooting: https://reactnative.dev/docs/troubleshooting
3. Check WordPress plugin logs
4. Verify API endpoint accessibility

## Additional Resources

- [React Native Documentation](https://reactnative.dev/docs/getting-started)
- [React Navigation](https://reactnavigation.org/docs/getting-started)
- [React Native Config](https://github.com/luggit/react-native-config)
- [Debugging Guide](https://reactnative.dev/docs/debugging)
