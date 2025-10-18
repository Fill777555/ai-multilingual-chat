# Sound Notification Feature - UI Mockups

## Admin Settings Page

### Location: WordPress Admin → AI Chat → Settings

```
╔════════════════════════════════════════════════════════════════════════╗
║ AI Multilingual Chat - Settings                                        ║
╚════════════════════════════════════════════════════════════════════════╝

┌────────────────────────────────────────────────────────────────────────┐
│ AI Провайдер                                                           │
│ [OpenAI (GPT-3.5/GPT-4)         ▼]                                    │
│ Выберите провайдера для перевода сообщений                            │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│ API ключ AI                                                            │
│ [sk-••••••••••••••••••••••••••••••••••••]                            │
│ Получить ключ: OpenAI | Anthropic | Google                            │
└────────────────────────────────────────────────────────────────────────┘

... (other settings) ...

┌────────────────────────────────────────────────────────────────────────┐
│ Звуковые уведомления                                                   │
│ [✓] Включить звуковые уведомления в админке и для клиентов            │
│ Администраторы слышат звук при новых сообщениях от клиентов,          │
│ клиенты — при ответах администратора.                                 │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│ Мелодия оповещения                              ⭐ NEW FEATURE         │
│ [По умолчанию                  ▼] [🔊 Прослушать]                     │
│   ├─ По умолчанию                                                      │
│   ├─ Колокольчик                                                       │
│   ├─ Динь                                                              │
│   ├─ Перезвон                                                          │
│   └─ Мягкий звук                                                       │
│                                                                        │
│ Выберите мелодию для звуковых уведомлений в админ-панели              │
└────────────────────────────────────────────────────────────────────────┘

[Сохранить настройки]
```

### Preview Button Action:

When user clicks "🔊 Прослушать":
```
[Колокольчик                   ▼] [🔊 Прослушать] ← Click!
                                   ↓
                              🔊 Playing sound...
                                   ↓
                              Sound plays immediately
                              (no save required)
```

## Client Chat Widget - Sound Settings Modal

### Trigger: Click gear icon (⚙️) in chat header

```
┌─────────────────────────────────────────┐
│ 🏢 Support chat         [⚙️][🔊][✕]    │ ← Click gear icon here
│ 🟢 We are online                        │
├─────────────────────────────────────────┤
│                                         │
│     Welcome!                            │
│     Please introduce yourself           │
│                                         │
│     [Your name_____________]            │
│     [English               ▼]          │
│     [Start a chat]                      │
│                                         │
└─────────────────────────────────────────┘
```

### Modal Opens:

```
╔═════════════════════════════════════════════════════════════════════╗
║                     🌐 Page Background (darkened)                   ║
║                                                                     ║
║   ┌───────────────────────────────────────────────────────┐       ║
║   │ Выбор мелодии оповещения                           [×]│       ║
║   ├───────────────────────────────────────────────────────┤       ║
║   │                                                        │       ║
║   │  ┌─────────────────────────────────────────────────┐  │       ║
║   │  │ ⦿ По умолчанию         [🔊 Прослушать]         │  │       ║
║   │  └─────────────────────────────────────────────────┘  │       ║
║   │                                                        │       ║
║   │  ┌─────────────────────────────────────────────────┐  │       ║
║   │  │ ○ Колокольчик          [🔊 Прослушать]         │  │       ║
║   │  └─────────────────────────────────────────────────┘  │       ║
║   │                                                        │       ║
║   │  ┌─────────────────────────────────────────────────┐  │       ║
║   │  │ ○ Динь                 [🔊 Прослушать]         │  │       ║
║   │  └─────────────────────────────────────────────────┘  │       ║
║   │                                                        │       ║
║   │  ┌─────────────────────────────────────────────────┐  │       ║
║   │  │ ○ Перезвон             [🔊 Прослушать]         │  │       ║
║   │  └─────────────────────────────────────────────────┘  │       ║
║   │                                                        │       ║
║   │  ┌─────────────────────────────────────────────────┐  │       ║
║   │  │ ○ Мягкий звук          [🔊 Прослушать]         │  │       ║
║   │  └─────────────────────────────────────────────────┘  │       ║
║   │                                                        │       ║
║   └───────────────────────────────────────────────────────┘       ║
║                                                                     ║
╚═════════════════════════════════════════════════════════════════════╝
```

### Interaction States:

#### 1. Hover State:
```
┌─────────────────────────────────────────────────┐
│ ○ Колокольчик          [🔊 Прослушать]         │ ← Not selected
└─────────────────────────────────────────────────┘
        ↓ User hovers
┌─────────────────────────────────────────────────┐
│ ○ Колокольчик          [🔊 Прослушать]         │ ← Border color changes
└─────────────────────────────────────────────────┘  Background: light blue
```

#### 2. Selected State:
```
┌─────────────────────────────────────────────────┐
│ ○ Колокольчик          [🔊 Прослушать]         │
└─────────────────────────────────────────────────┘
        ↓ User clicks
┌─────────────────────────────────────────────────┐
│ ⦿ Колокольчик          [🔊 Прослушать]         │ ← Radio filled
└─────────────────────────────────────────────────┘  Border: theme color
                                                     Background: light theme color
                                                     ✅ Saved to localStorage
```

#### 3. Preview Sound:
```
┌─────────────────────────────────────────────────┐
│ ○ Перезвон             [🔊 Прослушать] ← Click │
└─────────────────────────────────────────────────┘
                              ↓
                         🔊 Playing...
                              ↓
                    Sound plays without selection
```

### Close Actions:

Three ways to close:
```
1. Click [×] button
   ┌──────────────────────────────┐
   │ Title                     [×]│ ← Click here
   └──────────────────────────────┘

2. Click outside modal
   ╔═════════════════════════════╗
   ║  [Background]  ← Click      ║
   ║   ┌─────────┐               ║
   ║   │ Modal   │               ║
   ║   └─────────┘               ║
   ╚═════════════════════════════╝

3. Press ESC key (standard browser behavior)
```

## Chat Widget Header - Before and After

### Before (original):
```
┌─────────────────────────────────────────┐
│ 🏢 Support chat              [🔊][✕]   │
│ 🟢 We are online                        │
└─────────────────────────────────────────┘
```

### After (with settings button):
```
┌─────────────────────────────────────────┐
│ 🏢 Support chat         [⚙️][🔊][✕]    │ ← New gear icon added
│ 🟢 We are online                        │
└─────────────────────────────────────────┘
```

Button order (left to right):
1. **⚙️** - Sound settings (NEW)
2. **🔊** - Toggle sound on/off (existing)
3. **✕** - Close chat (existing)

## Responsive Design

### Desktop (400px modal):
```
┌────────────────────────────────────────────────────┐
│ Выбор мелодии оповещения                        [×]│
├────────────────────────────────────────────────────┤
│                                                    │
│  Full width sound items with preview buttons      │
│                                                    │
└────────────────────────────────────────────────────┘
```

### Mobile (90% width):
```
┌──────────────────────────────────────┐
│ Выбор мелодии...              [×]    │
├──────────────────────────────────────┤
│                                      │
│  Sound items stack nicely            │
│  Touch-friendly buttons              │
│                                      │
└──────────────────────────────────────┘
```

## Sound File Loading Flow

```
User Action → JavaScript → Load Sound File
     ↓
Admin: WordPress Option
     │
     ├─ Value: "bell"
     └─ URL: /wp-content/plugins/ai-multilingual-chat/sounds/notification-bell.mp3
     
Client: localStorage
     │
     ├─ Key: "aic_client_notification_sound"
     ├─ Value: "chime"
     └─ URL: /wp-content/plugins/ai-multilingual-chat/sounds/notification-chime.mp3
```

### Error Handling:
```
Try to load: notification-bell.mp3
     ↓
[404 Not Found]
     ↓
Fallback to: notification-default.mp3
     ↓
✅ Success / ❌ Silent fail with console warning
```

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      WordPress Backend                      │
│                                                             │
│  wp_options table                                           │
│  ├─ aic_admin_notification_sound: "bell"                   │
│  └─ aic_enable_sound_notifications: "1"                    │
│                                                             │
│  ↓ wp_localize_script                                       │
│                                                             │
│  JavaScript Object: aicAdmin                                │
│  ├─ sound_base_url: "/wp-content/plugins/.../sounds/"     │
│  ├─ sound_choice: "bell"                                   │
│  └─ available_sounds: {...}                                │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Admin Browser                           │
│                                                             │
│  admin-script.js                                            │
│  ├─ initNotificationSound()                                │
│  │  └─ new Audio(sound_base_url + 'notification-' +       │
│  │              sound_choice + '.mp3')                     │
│  └─ playNotificationSound()                                │
│     └─ this.notificationSound.play()                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    Client Browser                           │
│                                                             │
│  localStorage                                               │
│  └─ aic_client_notification_sound: "chime"                 │
│                                                             │
│  ↓                                                          │
│                                                             │
│  frontend-script.js                                         │
│  ├─ initNotificationSound()                                │
│  │  ├─ Get from localStorage                               │
│  │  └─ new Audio(sound_base_url + 'notification-' +       │
│  │              soundChoice + '.mp3')                      │
│  ├─ openSoundModal()                                       │
│  │  └─ Populate with available_sounds                     │
│  └─ previewSound(soundKey)                                 │
│     └─ new Audio(...).play()                               │
└─────────────────────────────────────────────────────────────┘
```

## Color Scheme

### Modal:
- **Background:** `rgba(0, 0, 0, 0.5)` - Semi-transparent black
- **Content:** `white` - Clean white background
- **Border:** `#eee` - Light gray separator

### Sound Items:
- **Default:** `#e0e0e0` border
- **Hover:** `var(--widget-color)` border + `rgba(102, 126, 234, 0.05)` background
- **Selected:** `var(--widget-color)` border + `rgba(102, 126, 234, 0.1)` background

### Buttons:
- **Preview:** `var(--widget-color)` background, `white` text
- **Close:** `#666` text, hover `#000`

## Accessibility Features

```
✅ Keyboard Navigation
   - Tab through options
   - Space/Enter to select
   - ESC to close modal

✅ Screen Reader Friendly
   - Proper label associations
   - Radio button semantics
   - Button titles/aria-labels

✅ Visual Feedback
   - Clear selection state
   - Hover effects
   - Focus indicators

✅ Error Handling
   - Fallback sounds
   - Console warnings
   - User-friendly alerts
```

## Summary

The UI implementation provides:
- **Simple admin dropdown** with instant preview
- **User-friendly modal** for clients with visual selection
- **Consistent design** following WordPress and custom theme colors
- **Responsive layout** working on all devices
- **Accessible controls** for all users
- **Clear visual feedback** for all interactions

All mockups represent the actual implementation in the code!
