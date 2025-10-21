# Widget Color Customization Guide

## Overview

The color customization feature allows you to fully customize the appearance of the chat widget to match your website's design.

## Where to Find Settings

1. Log in to WordPress admin panel
2. Navigate to **AI Chat → Settings**
3. Click on **"Widget Design"** tab
4. Scroll to **"Color Settings"** section

## Available Color Settings

### 1. Widget Background Color
- **Purpose:** Chat window background
- **Default:** `#1c2126` (dark gray)
- **Recommendations:** Use a contrasting color for better readability

### 2. Chat Button Color
- **Purpose:** Round chat button in the corner of the screen
- **Default:** `#667eea` (blue)
- **Recommendations:** Choose a bright color that stands out against your website

### 3. Header Color
- **Purpose:** Top panel of the chat window
- **Default:** `#667eea` (blue)
- **Recommendations:** Usually matches the open button color

### 4. User Message Background Color
- **Purpose:** Background of messages from users
- **Default:** `#667eea` (blue)
- **Recommendations:** Use a contrasting color with admin messages

### 5. Admin Message Background Color
- **Purpose:** Background of messages from admin/bot
- **Default:** `#ffffff` (white)
- **Recommendations:** Light colors usually work better for admin messages

### 6. User Message Text Color
- **Purpose:** Text color in user messages
- **Default:** `#ffffff` (white)
- **Recommendations:** Must contrast with user message background

### 7. Admin Message Text Color
- **Purpose:** Text color in admin messages
- **Default:** `#333333` (dark gray)
- **Recommendations:** Must contrast with admin message background

### 8. Send Button Color
- **Purpose:** Message send button
- **Default:** `#667eea` (blue)
- **Recommendations:** Usually matches the main widget color

### 9. Input Border Color
- **Purpose:** Border of the message input field
- **Default:** `#dddddd` (light gray)
- **Recommendations:** Use a neutral color for an unobtrusive appearance

## How to Use

### Changing a Color

1. Click on the colored square next to the setting name
2. Select a color using the browser's standard color picker
3. Or enter a hex code manually (e.g., `#667eea`)
4. The hex code will automatically display to the right of the picker
5. Click **"Save Settings"** at the bottom of the page

### Reset to Defaults

1. Click the **"🔄 Reset colors to default values"** button
2. Confirm the action in the dialog box
3. All colors will return to their original values
4. Click **"Save Settings"** to apply the changes

## Design Tips

### Contrast
- Ensure text is readable on the selected background
- Use contrast checking tools (e.g., WebAIM Contrast Checker)
- Recommended minimum contrast ratio is 4.5:1 for normal text

### Consistency
- Use colors from your website's palette
- Limit the number of different colors (2-3 main colors)
- Maintain a consistent style across all elements

### Example Color Schemes

#### Classic Blue
```
Button: #2196F3
Header: #2196F3
User Messages: #2196F3
Admin Messages: #FFFFFF
```

#### Modern Green
```
Button: #4CAF50
Header: #4CAF50
User Messages: #4CAF50
Admin Messages: #F5F5F5
```

#### Elegant Purple
```
Button: #9C27B0
Header: #9C27B0
User Messages: #9C27B0
Admin Messages: #FAFAFA
```

#### Dark Theme
```
Widget Background: #1E1E1E
Button: #BB86FC
Header: #BB86FC
User Messages: #BB86FC
Admin Messages: #2C2C2C
User Text: #FFFFFF
Admin Text: #E0E0E0
```

## Technical Details

### Color Format
- All colors must be in hex format: `#RRGGBB`
- Examples: `#667eea`, `#ffffff`, `#1c2126`
- Three-character codes are not supported (use full form)

### CSS Variables
Colors are applied through CSS variables:
```css
--widget-bg-color: #1c2126;
--chat-button-color: #667eea;
--header-bg-color: #667eea;
--user-msg-bg-color: #667eea;
--admin-msg-bg-color: #ffffff;
--user-msg-text-color: #ffffff;
--admin-msg-text-color: #333333;
--send-button-color: #667eea;
--input-border-color: #dddddd;
```

### Data Storage
- All settings are saved in WordPress `wp_options` table
- Option names: `aic_widget_bg_color`, `aic_chat_button_color`, etc.
- Values are sanitized with `sanitize_text_field()`

## Troubleshooting

### Colors not applying after saving
1. Clear browser cache (Ctrl+Shift+Del)
2. Clear WordPress cache (if using a caching plugin)
3. Verify settings were saved successfully (notification should appear)

### Poor text readability
1. Use a contrast checking tool
2. Change text or background color for better readability
3. Try standard color schemes from examples

### Colors look different on different devices
1. This is normal - monitors have different color settings
2. Test on multiple devices
3. Use standard web-safe colors

## Support

If you have questions or problems:
1. Check [GitHub Issues](https://github.com/Fill777555/ai-multilingual-chat/issues)
2. Create a new issue with a problem description
3. Attach a screenshot of your color settings

---

**Version:** 2.0.7+  
**Last Updated:** 2025-10-21
