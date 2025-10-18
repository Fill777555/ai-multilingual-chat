# Frontend Sound Notification Feature

## Обзор / Overview

Добавлена возможность выбора и управления звуковыми уведомлениями для клиентов на фронтенде чата.

The ability to select and manage sound notifications for clients on the frontend chat has been added.

## Основные функции / Key Features

### 1. Звуковые уведомления для клиентов / Sound Notifications for Clients
- **Описание**: Клиенты получают звуковой сигнал при получении новых сообщений от администратора
- **Description**: Clients receive an audio alert when receiving new messages from administrator
- **Реализация**: Используется тот же звуковой файл, что и в админ-панели (Base64-encoded WAV)
- **Implementation**: Uses the same sound file as the admin panel (Base64-encoded WAV)

### 2. Кнопка переключения звука / Sound Toggle Button
- **Расположение**: В заголовке чата, рядом с кнопкой закрытия
- **Location**: In the chat header, next to the close button
- **Визуальное отображение**:
  - Иконка динамика со звуковыми волнами = звук включен
  - Иконка динамика с перечеркнутой линией = звук выключен
- **Visual Display**:
  - Speaker icon with sound waves = sound enabled
  - Speaker icon with crossed line = sound disabled

### 3. Сохранение настроек / Settings Persistence
- **Хранение**: localStorage браузера (ключ: `aic_sound_enabled`)
- **Storage**: Browser localStorage (key: `aic_sound_enabled`)
- **Сохраняется между сеансами**: Да
- **Persists between sessions**: Yes

### 4. Глобальный контроль / Global Control
- **Настройка**: Панель настроек WordPress (`aic_enable_sound_notifications`)
- **Setting**: WordPress settings panel (`aic_enable_sound_notifications`)
- **Влияние**: Отключает звук для всех клиентов, если выключено
- **Impact**: Disables sound for all clients when turned off

## Технические детали / Technical Details

### Измененные файлы / Modified Files

1. **`frontend-script.js`**
   - Добавлена инициализация звука: `initNotificationSound()`
   - Добавлено воспроизведение звука: `playNotificationSound()`
   - Добавлено обновление кнопки: `updateSoundButton()`
   - Добавлена логика определения новых сообщений администратора

2. **`templates/chat-widget.php`**
   - Добавлена кнопка переключения звука в заголовок чата
   - Добавлены стили для кнопки и иконок
   - Добавлена визуализация состояния звука

3. **`ai-multilingual-chat.php`**
   - Добавлен параметр `enable_sound` в `wp_localize_script()`

4. **`templates/settings.php`**
   - Обновлена метка для пояснения применения к клиентам и админам

### Алгоритм работы / Algorithm

1. **Инициализация / Initialization**
   ```javascript
   - Загружается предпочтение из localStorage
   - Создается Audio объект с Base64 звуком
   - Устанавливается начальное состояние кнопки
   ```

2. **При получении новых сообщений / On receiving new messages**
   ```javascript
   - Проверяется: это новое сообщение (id > lastMessageId)?
   - Проверяется: это сообщение от админа (sender_type === 'admin')?
   - Проверяется: чат уже инициализирован (isInitialized === true)?
   - Если все да → воспроизводится звук (если включен)
   ```

3. **При клике на кнопку / On button click**
   ```javascript
   - Переключается состояние soundEnabled
   - Сохраняется в localStorage
   - Обновляется визуальное состояние кнопки
   - Воспроизводится тестовый звук (если включено)
   ```

### Условия воспроизведения звука / Sound Playback Conditions

Звук воспроизводится ТОЛЬКО если:
Sound plays ONLY if:

```javascript
this.notificationSound && 
this.soundEnabled && 
aicFrontend.enable_sound === '1'
```

1. ✅ Звуковой объект инициализирован
2. ✅ Пользователь не отключил звук локально
3. ✅ Глобальная настройка включена в WordPress

### Особенности / Special Features

#### Предотвращение звука при первой загрузке
**Проблема**: При первой загрузке истории сообщений не должен воспроизводиться звук для старых сообщений от админа.

**Решение**: Используется флаг `isInitialized`, который становится `true` только после первой загрузки.

```javascript
if (msg.sender_type === 'admin' && self.isInitialized) {
    hasNewAdminMessage = true;
}
```

#### Автоматическая обработка ошибок
```javascript
this.notificationSound.play().catch(function(e) {
    console.log('Could not play notification sound:', e);
});
```

## Тестирование / Testing

### Автоматические тесты / Automated Tests

Файл: `tests/test-frontend-sound-notifications.js`

Тесты покрывают:
Tests cover:
- ✅ Инициализация звука / Sound initialization
- ✅ Переключение звука / Sound toggle
- ✅ Сохранение в localStorage / localStorage persistence
- ✅ Воспроизведение для новых сообщений админа / Playing for new admin messages
- ✅ Глобальная настройка / Global setting

Запуск тестов:
```bash
node tests/test-frontend-sound-notifications.js
```

### Ручное тестирование / Manual Testing

1. **Открыть чат на фронтенде**
   - Проверить наличие кнопки звука в заголовке
   - Убедиться, что иконка показывает корректное состояние

2. **Протестировать переключение звука**
   - Кликнуть на кнопку
   - Проверить изменение иконки
   - Проверить воспроизведение тестового звука (если включено)

3. **Протестировать новые сообщения**
   - Отправить сообщение как пользователь
   - Ответить как админ
   - Проверить воспроизведение звука (если включен)

4. **Протестировать сохранение настроек**
   - Отключить звук
   - Обновить страницу (F5)
   - Проверить, что звук остался выключен

5. **Протестировать глобальную настройку**
   - Отключить в админке: Settings → Sound Notifications
   - Проверить, что звук не воспроизводится на фронтенде

## Совместимость / Compatibility

- **WordPress**: 5.0+
- **Браузеры / Browsers**: 
  - Chrome/Edge: ✅
  - Firefox: ✅
  - Safari: ✅
  - Opera: ✅
  - IE 11: ❌ (не поддерживается / not supported)

## Известные ограничения / Known Limitations

1. **Автовоспроизведение в браузерах**
   - Некоторые браузеры блокируют автовоспроизведение звука до взаимодействия пользователя
   - Звук может не воспроизвестись при первом сообщении без взаимодействия

2. **Размер звука**
   - Звук встроен в JavaScript как Base64
   - Увеличивает размер JS файла на ~12KB

## Будущие улучшения / Future Improvements

Возможные улучшения для будущих версий:
Possible improvements for future versions:

1. **Выбор мелодии / Melody Selection**
   - Добавить несколько вариантов звуков
   - Позволить выбирать в UI
   - Add multiple sound options
   - Allow selection in UI

2. **Регулировка громкости / Volume Control**
   - Добавить слайдер громкости
   - Сохранять в localStorage
   - Add volume slider
   - Save to localStorage

3. **Настройки уведомлений / Notification Settings**
   - Расширенные опции
   - Уведомления только для определенных типов сообщений
   - Advanced options
   - Notifications only for specific message types

## Связанные issue / Related Issues

- Original issue: "Фронтенд: Выбор варианта звукового оповещения для клиента"
- References:
  - `admin-script.js#L20` - Implementation reference
  - `settings.php#L153-L184` - Settings reference

## Автор / Author

Implementation by: GitHub Copilot
Date: 2025-10-18
