# Visual Guide - Frontend Design Settings v2.0.7

## Overview
This guide demonstrates the new Frontend Design settings tab and how it enhances the chat widget customization capabilities.

## Settings Page Layout

### New Tabbed Interface

```
┌─────────────────────────────────────────────────────────────┐
│  Настройки AI Multilingual Chat                              │
├─────────────────────────────────────────────────────────────┤
│  [Общие настройки] [Дизайн виджета] [REST API]              │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Currently viewing: [Active Tab Content]                     │
│                                                               │
│  • Tab 1: General Settings - AI provider, API keys, etc.    │
│  • Tab 2: Frontend Design - NEW! Widget styling options     │
│  • Tab 3: REST API - API documentation                      │
│                                                               │
│  [Сохранить настройки]                                       │
└─────────────────────────────────────────────────────────────┘
```

## Frontend Design Tab - New Settings

### 1. Border Radius Control

```
┌─────────────────────────────────────────────┐
│ Скругление углов (px)                        │
│ [12] (slider: 0 ─────●───── 50)            │
│ Радиус скругления углов окна чата (0-50px). │
│ По умолчанию: 12px                          │
└─────────────────────────────────────────────┘
```

**Effect on Widget:**
```
With radius = 0:        With radius = 12:      With radius = 30:
┌────────────┐          ╭────────────╮          ╭──────────╮
│  Square    │          │  Default   │          │  Very    │
│  corners   │          │  rounded   │          │  rounded │
└────────────┘          ╰────────────╯          ╰──────────╯
```

### 2. Font Size Control

```
┌─────────────────────────────────────────────┐
│ Размер шрифта (px)                           │
│ [14] (slider: 10 ──●──── 24)               │
│ Размер шрифта в чате (10-24px).             │
│ По умолчанию: 14px                          │
└─────────────────────────────────────────────┘
```

**Effect on Widget:**
```
10px:  Small text for compact display
14px:  Default - balanced readability  ← Default
18px:  Larger text for better visibility
24px:  Extra large for accessibility
```

### 3. Padding Control

```
┌─────────────────────────────────────────────┐
│ Внутренние отступы (px)                      │
│ [20] (slider: 5 ────●──── 40)              │
│ Внутренние отступы заголовка чата (5-40px). │
│ По умолчанию: 20px                          │
└─────────────────────────────────────────────┘
```

**Effect on Header:**
```
Padding = 10:           Padding = 20:           Padding = 35:
┌──────────┐            ┌────────────┐          ┌──────────────┐
│Support   │            │            │          │              │
│Chat      │            │Support Chat│          │ Support Chat │
└──────────┘            │            │          │              │
 Compact                └────────────┘          └──────────────┘
                        Default                  Spacious
```

### 4. Custom CSS Textarea

```
┌────────────────────────────────────────────────────────┐
│ Произвольный CSS                                        │
│ ┌────────────────────────────────────────────────────┐ │
│ │ /* Введите ваш CSS код здесь */                    │ │
│ │ #aic-chat-widget .aic-chat-window {                │ │
│ │     /* your custom styles */                       │ │
│ │ }                                                  │ │
│ │                                                    │ │
│ │                                                    │ │
│ │                                                    │ │
│ │                                                    │ │
│ └────────────────────────────────────────────────────┘ │
│ Добавьте собственный CSS код для полного контроля       │
│ над дизайном виджета.                                  │
│ Примеры: изменение цветов, размеров, отступов...      │
│ ⚠️ Внимание: Используйте осторожно                    │
└────────────────────────────────────────────────────────┘
```

## Usage Examples with Visual Results

### Example 1: Modern Rounded Design

**Settings:**
- Border Radius: 25px
- Font Size: 15px
- Padding: 25px

**Result:**
```
         ╭──────────────────────╮
         │                      │
         │   Support Chat       │
         │   We are online      │
         │                      │
         ├──────────────────────┤
         │ Messages area...     │
         │                      │
         ╰──────────────────────╯
   Smooth, modern appearance
```

### Example 2: Compact Design

**Settings:**
- Border Radius: 8px
- Font Size: 12px
- Padding: 12px

**Result:**
```
      ┌─────────────────┐
      │Support Chat     │
      ├─────────────────┤
      │ Messages...     │
      │                 │
      └─────────────────┘
  Space-efficient layout
```

### Example 3: Accessibility-Focused

**Settings:**
- Border Radius: 5px
- Font Size: 18px
- Padding: 30px

**Result:**
```
    ┌───────────────────────┐
    │                       │
    │   Support Chat        │
    │   Large, readable     │
    │                       │
    ├───────────────────────┤
    │  Larger text for      │
    │  better visibility    │
    │                       │
    └───────────────────────┘
   High visibility design
```

### Example 4: Custom CSS - Branded Colors

**Custom CSS:**
```css
/* Corporate brand colors */
#aic-chat-widget .aic-chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

#aic-chat-widget .aic-message-user {
    background: #667eea;
    border-radius: 18px;
}

#aic-chat-widget .aic-send-button {
    background: #764ba2;
    transition: all 0.3s ease;
}

#aic-chat-widget .aic-send-button:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
}
```

**Result:**
- Gradient header matching brand colors
- Rounded message bubbles
- Animated send button with hover effect

## CSS Variables Reference

The plugin now exposes these CSS variables for styling:

```css
:root {
    --widget-color: #667eea;              /* Main theme color */
    --widget-border-radius: 12px;         /* Corner rounding */
    --widget-font-size: 14px;             /* Base font size */
    --widget-padding: 20px;               /* Header padding */
}
```

### Using Variables in Custom CSS

```css
/* Example: Use double the padding for specific element */
#aic-chat-widget .aic-welcome-screen {
    padding: calc(var(--widget-padding) * 2);
}

/* Example: Match font size across elements */
#aic-chat-widget .custom-element {
    font-size: var(--widget-font-size);
}

/* Example: Consistent border radius */
#aic-chat-widget .custom-button {
    border-radius: calc(var(--widget-border-radius) / 2);
}
```

## Advanced Customization Ideas

### 1. Animation Effects
```css
#aic-chat-widget .aic-message {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-20px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
```

### 2. Custom Scrollbar
```css
#aic-chat-widget .aic-chat-messages::-webkit-scrollbar {
    width: 8px;
}

#aic-chat-widget .aic-chat-messages::-webkit-scrollbar-thumb {
    background: var(--widget-color);
    border-radius: 4px;
}
```

### 3. Glassmorphism Effect
```css
#aic-chat-widget .aic-chat-window {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
```

### 4. Dark Mode Adjustments
```css
@media (prefers-color-scheme: dark) {
    #aic-chat-widget .aic-chat-window {
        background: #1a1a1a;
        color: #ffffff;
    }
    
    #aic-chat-widget .aic-message-user {
        background: #2d3748;
    }
}
```

## Before and After Comparison

### Before v2.0.7
- Fixed border radius (12px)
- Fixed font size (14px)
- Fixed padding (20px)
- No custom CSS support
- Single settings page

### After v2.0.7
- ✅ Adjustable border radius (0-50px)
- ✅ Adjustable font size (10-24px)
- ✅ Adjustable padding (5-40px)
- ✅ Full custom CSS support
- ✅ Organized tabbed interface
- ✅ CSS variables exposed
- ✅ Live CSS application

## Benefits Summary

### For Site Owners
- 🎨 Match widget to site branding
- 🔧 No code editing required
- 👁️ Visual consistency
- ⚡ Quick adjustments

### For Developers
- 💻 Full CSS control
- 🔌 CSS variables for consistency
- 🛠️ Advanced customization options
- 📚 Clean, documented API

### For End Users
- 📱 Better mobile experience
- ♿ Accessibility improvements
- 🎯 Consistent user interface
- ⚡ Smooth interactions

## Tips and Best Practices

### 1. Start with Defaults
Begin with default values and make small adjustments to see the effect.

### 2. Test Responsiveness
```css
/* Make sure custom CSS works on mobile */
@media (max-width: 768px) {
    #aic-chat-widget .aic-chat-window {
        width: 100%;
        height: 100%;
        border-radius: 0;
    }
}
```

### 3. Maintain Readability
- Keep font size ≥ 14px for readability
- Ensure sufficient contrast ratios
- Test with actual chat content

### 4. Use Browser DevTools
- Inspect widget elements
- Test CSS before adding to settings
- Verify CSS syntax

### 5. Keep Backup
Save your custom CSS externally before making major changes.

## Troubleshooting

### CSS Not Applying?
1. Check CSS syntax (no closing brackets missing)
2. Use specific selectors (#aic-chat-widget prefix)
3. Add `!important` if needed (use sparingly)
4. Clear browser cache

### Widget Looks Broken?
1. Reset settings to defaults
2. Remove custom CSS temporarily
3. Check browser console for errors
4. Verify CSS doesn't conflict with theme

### Performance Issues?
1. Keep custom CSS concise
2. Avoid complex selectors
3. Minimize animations
4. Test on slower devices

## Conclusion

The Frontend Design settings in v2.0.7 provide powerful, flexible customization options while maintaining security and ease of use. Whether you need simple tweaks or complete redesigns, the new features have you covered.

---

**Need Help?** Check the plugin documentation or contact support for assistance with custom styling.
