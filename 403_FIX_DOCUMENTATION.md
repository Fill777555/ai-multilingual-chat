# Исправление ошибки 403 Forbidden в AJAX-запросах

## Проблема
При отправке AJAX-запросов на загрузку сообщений возникала ошибка **403 Forbidden**, которая блокировала работу чата.

## Причина
Функция WordPress `check_ajax_referer()` по умолчанию **завершает выполнение скрипта** с HTTP кодом 403 при неудачной проверке nonce (security token). Это происходило в следующих случаях:

1. **Истечение срока действия nonce** - WordPress nonces имеют ограниченный срок действия (12-24 часа)
2. **Некорректная передача nonce** - если nonce не передается или передается неправильно
3. **Проблемы с сессией** - в некоторых случаях сессия пользователя может быть сброшена

## Решение

### 1. Изменение серверной части (PHP)

Изменен способ проверки nonce во всех AJAX-обработчиках:

**Было:**
```php
check_ajax_referer('aic_frontend_nonce', 'nonce');
```

**Стало:**
```php
// Verify nonce, but don't die on failure - return error instead
if (!check_ajax_referer('aic_frontend_nonce', 'nonce', false)) {
    wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.', 'code' => 'nonce_failed'));
    return;
}
```

Ключевое изменение - добавлен третий параметр `false`, который предотвращает автоматическое завершение скрипта. Вместо этого возвращается JSON-ответ с ошибкой.

### 2. Изменение клиентской части (JavaScript)

Добавлена обработка ошибок nonce в frontend-script.js:

```javascript
// В success callback
else if (!response.success && response.data && response.data.code === 'nonce_failed') {
    // Nonce verification failed - stop polling and show error
    console.error('Nonce verification failed, please refresh the page');
    if (self.pollInterval) {
        clearInterval(self.pollInterval);
        self.pollInterval = null;
    }
    self.addSystemMessage('Security token expired. Please refresh the page to continue.');
}

// В error callback
if (xhr.status === 403) {
    console.error('403 Forbidden - security check failed');
    if (self.pollInterval) {
        clearInterval(self.pollInterval);
        self.pollInterval = null;
    }
    self.addSystemMessage('Security token expired. Please refresh the page to continue.');
}
```

## Затронутые AJAX-обработчики

### Frontend (aic_frontend_nonce)
- ✅ `ajax_start_conversation` - создание нового разговора
- ✅ `ajax_send_message` - отправка сообщения
- ✅ `ajax_get_messages` - загрузка сообщений
- ✅ `ajax_user_typing` - индикатор набора текста пользователем

### Admin (aic_admin_nonce)
- ✅ `ajax_admin_get_conversations` - получение списка разговоров
- ✅ `ajax_admin_get_messages` - загрузка сообщений в админке
- ✅ `ajax_admin_send_message` - отправка сообщения админом
- ✅ `ajax_admin_close_conversation` - закрытие разговора
- ✅ `ajax_admin_typing` - индикатор набора текста админом
- ✅ `ajax_export_conversation` - экспорт разговора в CSV
- ✅ `aic_generate_api_key` - генерация нового API ключа

## Преимущества решения

1. **Graceful degradation** - вместо жесткой ошибки 403 пользователь получает понятное сообщение
2. **Остановка polling** - при обнаружении ошибки nonce останавливается автоматический опрос сервера
3. **Информативность** - пользователь видит сообщение "Security token expired. Please refresh the page to continue."
4. **Логирование** - все ошибки nonce логируются в консоль для отладки
5. **Единообразие** - все AJAX-обработчики используют одинаковый подход к обработке ошибок

## Тестирование

### Ручное тестирование

1. **Откройте сайт в браузере** и запустите чат
2. **Откройте DevTools Console** (F12)
3. **Отправьте несколько сообщений** - все должно работать нормально
4. **Симуляция истекшего nonce:**
   - В консоли выполните: `aicFrontend.nonce = 'invalid_nonce';`
   - Попробуйте отправить сообщение
   - Должно появиться сообщение: "Security token expired. Please refresh the page to continue."

### Проверка в production

1. Откройте чат и оставьте страницу открытой на 24+ часа
2. Попробуйте отправить сообщение после истечения nonce
3. Вместо ошибки 403 должно появиться пользовательское сообщение

## Дополнительные улучшения (опционально)

В будущем можно добавить:

1. **Автоматическое обновление nonce:**
   ```javascript
   // Refresh nonce before it expires
   setInterval(function() {
       $.ajax({
           url: aicFrontend.ajax_url,
           type: 'POST',
           data: {
               action: 'aic_refresh_nonce'
           },
           success: function(response) {
               if (response.success) {
                   aicFrontend.nonce = response.data.nonce;
               }
           }
       });
   }, 10 * 60 * 60 * 1000); // every 10 hours
   ```

2. **Автоматическая перезагрузка страницы** при ошибке nonce
3. **Показ кнопки "Обновить"** вместо требования ручного обновления

## Связанные файлы

- `/ai-multilingual-chat/ai-multilingual-chat.php` - основной файл плагина с AJAX-обработчиками
- `/ai-multilingual-chat/frontend-script.js` - клиентский JavaScript для фронтенда
- `/ai-multilingual-chat/admin-script.js` - клиентский JavaScript для админки

## Версия

Исправление реализовано в версии 2.0.2+
