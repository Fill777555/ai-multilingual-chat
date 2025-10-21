# Frontend Design Settings Implementation - v2.0.7

## Overview
Added a new "Frontend Design" tab to plugin settings providing full control over the chat widget appearance through CSS customization options.

## Changes Made

### 1. Version Update
- Updated plugin version from 2.0.5 to 2.0.7
- Updated in `ai-multilingual-chat.php` and `readme.txt`

### 2. Tabbed Settings Interface
Refactored settings page (`templates/settings.php`) to use a tabbed layout with three tabs:
- **Общие настройки (General Settings)** - Existing configuration options
- **Дизайн виджета (Frontend Design)** - NEW tab with CSS customization
- **REST API** - API documentation

### 3. New Frontend Design Options

#### Border Radius (Скругление углов)
- Control: Number input (0-50px)
- Option: `aic_widget_border_radius`
- Default: 12px
- Applies to: Chat window border-radius

#### Font Size (Размер шрифта)
- Control: Number input (10-24px)
- Option: `aic_widget_font_size`
- Default: 14px
- Applies to: All text within the widget

#### Padding (Внутренние отступы)
- Control: Number input (5-40px)
- Option: `aic_widget_padding`
- Default: 20px
- Applies to: Chat header padding

#### Custom CSS (Произвольный CSS)
- Control: Textarea with code highlighting
- Option: `aic_widget_custom_css`
- Default: Empty
- Purpose: Full CSS control for advanced customization
- Sanitization: Uses `wp_strip_all_tags()` for security

### 4. Implementation Details

#### Backend Changes (`ai-multilingual-chat.php`)

**Updated `set_default_options()` method:**
```php
'aic_widget_border_radius' => '12',
'aic_widget_font_size' => '14',
'aic_widget_padding' => '20',
'aic_widget_custom_css' => '',
```

**Updated `save_settings()` method:**
- Added new settings to the save array
- Special handling for custom CSS with proper sanitization
- Uses `wp_strip_all_tags()` to prevent XSS vulnerabilities

#### Frontend Changes (`templates/chat-widget.php`)

**CSS Variables:**
```css
:root {
    --widget-color: #667eea;
    --widget-border-radius: 12px;
    --widget-font-size: 14px;
    --widget-padding: 20px;
}
```

**Applied Variables:**
- Widget font-size uses `var(--widget-font-size)`
- Chat window border-radius uses `var(--widget-border-radius)`
- Chat header padding uses `var(--widget-padding)`
- Custom CSS injected at end of style block

### 5. Tab Switching Implementation

**JavaScript (in settings.php):**
```javascript
$('.nav-tab').on('click', function(e) {
    e.preventDefault();
    var targetTab = $(this).data('tab');
    
    // Update tab navigation
    $('.nav-tab').removeClass('nav-tab-active');
    $(this).addClass('nav-tab-active');
    
    // Update tab content
    $('.aic-settings-tab').hide().removeClass('aic-settings-tab-active');
    $('#tab-' + targetTab).show().addClass('aic-settings-tab-active');
});
```

### 6. Security Considerations

1. **Input Validation:**
   - All number inputs have min/max constraints
   - Border radius: 0-50px
   - Font size: 10-24px
   - Padding: 5-40px

2. **CSS Sanitization:**
   - Custom CSS is sanitized with `wp_strip_all_tags()`
   - Prevents injection of malicious scripts
   - Only pure CSS is stored

3. **Nonce Protection:**
   - Existing nonce check remains in place: `check_admin_referer('aic_settings_nonce')`

## Usage Examples

### Example 1: Rounded Corners
Set Border Radius to 20px for more rounded chat window

### Example 2: Larger Text
Set Font Size to 16px for better readability

### Example 3: Spacious Header
Set Padding to 30px for a more spacious header

### Example 4: Custom CSS
```css
/* Change message bubble colors */
#aic-chat-widget .aic-message-user {
    background: #4CAF50;
}

#aic-chat-widget .aic-message-admin {
    background: #2196F3;
}

/* Add custom animations */
#aic-chat-window {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
```

## Benefits

1. **User Control:** Site administrators have full control over chat widget appearance
2. **Brand Consistency:** Easy to match widget design with site branding
3. **No Code Editing:** Changes made through admin panel without touching theme files
4. **Safe Defaults:** Sensible defaults ensure widget works well out of the box
5. **Advanced Options:** Custom CSS allows unlimited customization for power users

## Testing Checklist

- [x] PHP syntax validation passed
- [x] All files updated correctly
- [x] Version numbers updated
- [x] Default options added
- [x] Save settings method updated
- [x] CSS variables applied
- [x] Tab navigation functional
- [ ] Visual testing in WordPress admin
- [ ] Frontend widget rendering
- [ ] Custom CSS application

## Files Modified

1. `ai-multilingual-chat/ai-multilingual-chat.php`
   - Version updated
   - Default options added
   - Save settings method updated

2. `ai-multilingual-chat/readme.txt`
   - Stable tag updated to 2.0.7

3. `ai-multilingual-chat/templates/settings.php`
   - Added tab navigation
   - Added Frontend Design tab
   - Restructured form layout
   - Added tab switching JavaScript

4. `ai-multilingual-chat/templates/chat-widget.php`
   - Added CSS variable retrieval
   - Applied CSS variables to styles
   - Added custom CSS injection

## Backward Compatibility

All changes are fully backward compatible:
- Existing options remain unchanged
- New options have safe defaults
- Widget appearance unchanged unless settings modified
- No database migrations required

## Future Enhancements

Potential additions for future versions:
- Color picker for additional colors
- Width/height controls
- Animation speed controls
- Button shape customization
- Position fine-tuning (pixel-perfect positioning)
- CSS preprocessor support
- Live preview of changes
