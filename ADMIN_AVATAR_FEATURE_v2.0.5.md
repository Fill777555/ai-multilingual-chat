# Admin Avatar Feature Implementation - v2.0.5

## Overview
This document describes the implementation of the administrator avatar feature in the AI Multilingual Chat plugin version 2.0.5.

## Feature Description
The admin avatar feature allows administrators to upload and display a custom avatar image that appears next to their messages in both the admin chat interface and the frontend client chat widget.

## Changes Made

### 1. Plugin Version Update
- Updated plugin version from 2.0.4 to 2.0.5
- Updated in both the plugin header and AIC_VERSION constant

### 2. Backend Changes (ai-multilingual-chat.php)

#### Default Options
Added new option `aic_admin_avatar` with empty default value:
```php
'aic_admin_avatar' => '',
```

#### Settings Save
Added `aic_admin_avatar` to the list of settings to be saved:
```php
$settings = array(..., 'aic_admin_avatar');
```

#### Script Localization
Added admin avatar URL to both admin and frontend scripts:
```php
'admin_avatar' => get_option('aic_admin_avatar', ''),
```

#### Media Library
Enqueued WordPress media library on settings page:
```php
if (strpos($hook, 'ai-chat-settings') !== false) {
    wp_enqueue_media();
}
```

### 3. Settings Page (templates/settings.php)

#### Upload Field
Added new settings field with:
- Hidden input field for storing avatar URL
- Image preview element (circular, 100x100px max)
- Upload button using WordPress media library
- Remove button to clear the avatar
- Descriptive help text

#### JavaScript Integration
Implemented WordPress media uploader with:
- Media library modal dialog
- Image selection handler
- Preview update on selection
- Remove functionality

### 4. Frontend Display (frontend-script.js)

#### Message Rendering
Updated `addMessage` function to:
- Check if message is from admin
- Check if admin avatar is configured
- Add avatar image before message content
- Apply `aic-admin-avatar` CSS class

### 5. Admin Display (admin-script.js)

#### Message Rendering
Updated message rendering to:
- Check if message is from admin
- Check if admin avatar is configured
- Add avatar image with inline styles (32x32px, circular)
- Properly align avatar with message bubble

### 6. Styling (templates/chat-widget.php)

#### CSS Styles
Added styles for admin messages and avatar:
```css
.aic-message.admin {
    justify-content: flex-start;
    align-items: flex-start;
    gap: 8px;
}

.aic-admin-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}
```

## Usage Instructions

### For Administrators

1. **Navigate to Settings**
   - Go to WordPress admin panel
   - Click on "AI Chat" menu
   - Select "Настройки" (Settings)

2. **Upload Avatar**
   - Scroll to "Аватар администратора" (Administrator Avatar) field
   - Click "Загрузить изображение" (Upload Image) button
   - Select an image from your media library or upload a new one
   - Recommended size: 100x100 pixels
   - Image will be displayed in circular format

3. **Preview**
   - The uploaded image will appear above the upload button
   - Shows a preview of how the avatar will look

4. **Remove Avatar**
   - Click "Удалить" (Remove) button to clear the avatar
   - Avatar will be removed from all chat messages

5. **Save Settings**
   - Click "Сохранить настройки" (Save Settings) at the bottom

### Avatar Display Behavior

- **Frontend Chat Widget**: Avatar appears to the left of admin messages (32x32px, circular)
- **Admin Interface**: Avatar appears to the left of admin messages (32x32px, circular)
- **Without Avatar**: Messages display normally without avatar image
- **Responsive**: Avatar sizing is fixed and works on all screen sizes

## Technical Details

### Security
- All inputs are sanitized using `sanitize_text_field()`
- Avatar URL is escaped using `esc_url()` and `esc_attr()`
- Uses WordPress nonce verification for settings save
- Media library access is restricted to admin users only

### Compatibility
- Works with WordPress media library
- Compatible with existing emoji picker feature
- Compatible with dark theme mode
- Compatible with sound notifications
- Maintains backward compatibility (no avatar = no display)

### Performance
- Avatar URL is stored as option in WordPress database
- No additional database queries per message
- Image loaded via standard WordPress media URLs
- CSS uses modern flexbox for layout

## Testing

All automated tests pass successfully:
- ✓ Version updated to 2.0.5
- ✓ Default option added
- ✓ Settings save includes avatar
- ✓ Avatar passed to scripts
- ✓ Upload field in settings
- ✓ Media library integration
- ✓ Preview element
- ✓ Frontend rendering
- ✓ Admin rendering
- ✓ CSS styling
- ✓ Media library enqueued

## Files Modified

1. `ai-multilingual-chat/ai-multilingual-chat.php` - Main plugin file
2. `ai-multilingual-chat/templates/settings.php` - Settings page template
3. `ai-multilingual-chat/frontend-script.js` - Frontend JavaScript
4. `ai-multilingual-chat/admin-script.js` - Admin JavaScript
5. `ai-multilingual-chat/templates/chat-widget.php` - Chat widget template with CSS

## Files Added

1. `tests/test-admin-avatar-feature.php` - Test suite for avatar feature

## Migration Notes

- Existing installations will automatically get the new setting with empty value
- No database migration needed
- Plugin will work normally without avatar configured
- Administrators can optionally configure avatar at any time

## Future Enhancements (Potential)

- Support for user avatars (in addition to admin)
- Avatar customization per conversation
- Default avatar fallback image
- Avatar size settings
- Multiple admin avatars (for multi-admin setups)
