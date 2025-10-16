# Визуальное руководство по тестированию AJAX

## Как запустить тест AJAX эндпоинтов

### Шаг 1: Откройте сайт WordPress с плагином

Убедитесь, что:
- ✅ Плагин `ai-multilingual-chat` установлен и активирован
- ✅ Вы находитесь на странице с виджетом чата (для Frontend тестов)
- ✅ Или на странице админки чата (для Admin тестов)

### Шаг 2: Откройте консоль браузера

**Chrome/Edge:**
```
F12 или Ctrl+Shift+J (Windows/Linux)
Cmd+Option+J (Mac)
```

**Firefox:**
```
F12 или Ctrl+Shift+K (Windows/Linux)
Cmd+Option+K (Mac)
```

### Шаг 3: Загрузите и выполните тест

1. Откройте файл `tests/test-ajax-endpoints-comprehensive.js`
2. Скопируйте весь его содержимое (Ctrl+A, Ctrl+C)
3. Вставьте в консоль браузера (Ctrl+V)
4. Нажмите Enter

### Шаг 4: Проверьте результаты

Вы увидите вывод в консоли:

```
🚀 Starting AJAX Endpoints Comprehensive Test
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📝 Test 1: Frontend: aic_start_conversation - Missing session_id
   Response from aic_start_conversation: {success: false, data: {...}}
✅ PASS: Frontend: aic_start_conversation - Missing session_id

📝 Test 2: Frontend: aic_start_conversation - Missing user_name
   Response from aic_start_conversation: {success: false, data: {...}}
✅ PASS: Frontend: aic_start_conversation - Missing user_name

...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Test Results Summary
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total Tests: 16
✅ Passed: 16
❌ Failed: 0
Success Rate: 100.0%

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎉 All AJAX endpoints passed validation!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## Интерпретация результатов

### ✅ Успешный тест (PASS)
```
✅ PASS: Frontend: aic_start_conversation - Valid request
```
- Эндпоинт работает корректно
- Валидация входных данных работает
- Обработка ошибок реализована

### ❌ Провалившийся тест (FAIL)
```
❌ FAIL: Admin: aic_admin_get_messages - Invalid conversation_id
   Error: Should fail with conversation_id = 0
```
- Эндпоинт не обрабатывает ошибку корректно
- Требуется исправление кода

### ⚠️ Предупреждение
```
⚠ Warning: Could not verify timeout configuration
```
- Не критично, но требует внимания
- Возможно, требуется улучшение

---

## Тестируемые сценарии

### Frontend AJAX (4 эндпоинта × 4 сценария = ~7 тестов)

1. **aic_start_conversation**
   - ❌ Отсутствует session_id → Должен вернуть ошибку
   - ❌ Отсутствует user_name → Должен вернуть ошибку
   - ✅ Валидные параметры → Должен создать разговор

2. **aic_send_message**
   - ❌ Пустое сообщение → Должен вернуть ошибку

3. **aic_get_messages**
   - ❌ Отсутствует session_id → Должен вернуть ошибку
   - ✅ Валидные параметры → Должен вернуть массив сообщений

4. **aic_user_typing**
   - ❌ Невалидный conversation_id → Должен вернуть ошибку

### Admin AJAX (6 эндпоинтов × 2 сценария = ~9 тестов)

5. **aic_admin_get_conversations**
   - ✅ Валидный запрос → Должен вернуть массив диалогов

6. **aic_admin_get_messages**
   - ❌ conversation_id = 0 → Должен вернуть ошибку
   - ❌ conversation_id < 0 → Должен вернуть ошибку

7. **aic_admin_send_message**
   - ❌ Пустое сообщение → Должен вернуть ошибку

8. **aic_admin_close_conversation**
   - ❌ Невалидный conversation_id → Должен вернуть ошибку

9. **aic_admin_typing**
   - ❌ Невалидный conversation_id → Должен вернуть ошибку

10. **aic_export_conversation**
    - ❌ Отсутствует conversation_id → Должен вернуть ошибку
    - ❌ Невалидный conversation_id → Должен вернуть ошибку

---

## Расширенная диагностика

### Проверка доступности объектов

Если тесты не запускаются, проверьте в консоли:

```javascript
// Проверка доступности Frontend объектов
console.log('aicFrontend:', typeof aicFrontend);
console.log('aicFrontend.ajax_url:', aicFrontend?.ajax_url);
console.log('aicFrontend.nonce:', aicFrontend?.nonce ? 'Present' : 'Missing');

// Проверка доступности Admin объектов
console.log('aicAdmin:', typeof aicAdmin);
console.log('aicAdmin.ajax_url:', aicAdmin?.ajax_url);
console.log('aicAdmin.nonce:', aicAdmin?.nonce ? 'Present' : 'Missing');

// Проверка jQuery
console.log('jQuery version:', jQuery.fn.jquery);
```

Ожидаемый результат:
```
aicFrontend: object
aicFrontend.ajax_url: /wp-admin/admin-ajax.php
aicFrontend.nonce: Present
aicAdmin: object (только на админских страницах)
aicAdmin.ajax_url: /wp-admin/admin-ajax.php
aicAdmin.nonce: Present
jQuery version: 3.x.x
```

### Ручная проверка одного эндпоинта

Если нужно проверить только один эндпоинт:

```javascript
jQuery.ajax({
    url: aicFrontend.ajax_url,
    type: 'POST',
    timeout: 10000,
    data: {
        action: 'aic_start_conversation',
        nonce: aicFrontend.nonce,
        session_id: 'test_' + Date.now(),
        user_name: 'Test User',
        user_language: 'en'
    },
    success: function(response) {
        console.log('✅ Success:', response);
    },
    error: function(xhr, status, error) {
        console.error('❌ Error:', xhr.status, status, error);
    }
});
```

---

## Устранение проблем

### Проблема: "Nonce not available"

**Причина**: Скрипты не загружены или вы на неправильной странице

**Решение**:
1. Убедитесь, что плагин активирован
2. Обновите страницу (Ctrl+F5)
3. Очистите кэш браузера
4. Для Frontend тестов: откройте страницу с виджетом чата
5. Для Admin тестов: откройте админку WordPress → AI Chat

### Проблема: "jQuery is not defined"

**Причина**: jQuery не загружен

**Решение**:
```javascript
// Подождите загрузки jQuery
if (typeof jQuery === 'undefined') {
    console.error('jQuery не загружен. Подождите загрузки страницы.');
} else {
    // Вставьте код теста здесь
}
```

### Проблема: Все тесты падают с 403 ошибкой

**Причина**: Истёк nonce или проблемы с безопасностью

**Решение**:
1. Обновите страницу (F5)
2. Запустите тест снова
3. Если проблема сохраняется - очистите cookies и кэш

### Проблема: Timeout на всех запросах

**Причина**: Проблемы с сервером или сетью

**Решение**:
1. Проверьте, работает ли сайт
2. Проверьте консоль на наличие других ошибок
3. Проверьте Network tab в DevTools
4. Увеличьте timeout в тесте до 30000 (30 сек)

---

## Мониторинг в реальном времени

### Просмотр Network запросов

1. Откройте DevTools (F12)
2. Перейдите на вкладку "Network"
3. Фильтр: "Fetch/XHR"
4. Запустите тест
5. Смотрите запросы к `admin-ajax.php`

**Что проверить:**
- ✅ Status Code: 200 (успех) или 403 (nonce истёк)
- ✅ Response Type: application/json
- ✅ Response Time: < 1000ms (хорошо), < 3000ms (приемлемо)

### Просмотр Console логов

Во время теста в консоли отображаются:
- `📝` Какой тест выполняется
- `Response from [action]` Ответ сервера
- `✅ PASS` или `❌ FAIL` Результат теста
- Детали ошибок при провале теста

---

## Результаты теста для отчёта

После выполнения теста результаты сохраняются в:
```javascript
window.ajaxTestResults
```

Вы можете экспортировать их:
```javascript
console.table(window.ajaxTestResults);

// Или скопировать как JSON
copy(JSON.stringify(window.ajaxTestResults, null, 2));
```

Структура результатов:
```javascript
{
  "total": 16,
  "passed": 16,
  "failed": 0,
  "errors": []
}
```

---

## Автоматизация

Для автоматического запуска тестов при каждом деплое:

### 1. Создайте тестовый файл HTML:

```html
<!DOCTYPE html>
<html>
<head>
    <title>AJAX Test Runner</title>
</head>
<body>
    <h1>Running Tests...</h1>
    <div id="results"></div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Задайте переменные окружения
        var aicFrontend = {
            ajax_url: '/wp-admin/admin-ajax.php',
            nonce: 'YOUR_FRONTEND_NONCE'
        };
        var aicAdmin = {
            ajax_url: '/wp-admin/admin-ajax.php',
            nonce: 'YOUR_ADMIN_NONCE'
        };
    </script>
    <script src="test-ajax-endpoints-comprehensive.js"></script>
</body>
</html>
```

### 2. Запуск через Puppeteer (Node.js):

```javascript
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    await page.goto('https://yoursite.com/test-runner.html');
    
    // Ждём завершения тестов
    await page.waitForFunction(() => window.ajaxTestResults !== undefined);
    
    const results = await page.evaluate(() => window.ajaxTestResults);
    
    console.log('Test Results:', results);
    
    if (results.failed > 0) {
        console.error('Some tests failed!');
        process.exit(1);
    }
    
    await browser.close();
})();
```

---

**Успешного тестирования! 🚀**
