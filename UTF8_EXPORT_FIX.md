# UTF-8 Encoding Fix for CSV Export

## Problem
After exporting conversations to CSV, Cyrillic characters were broken/corrupted when opening the file in spreadsheet applications like Excel or LibreOffice Calc. This was due to missing UTF-8 encoding specification in the exported CSV files.

## Root Cause
The CSV export functionality was not adding a UTF-8 BOM (Byte Order Mark) on the server side. While the client-side JavaScript was adding a BOM, it's best practice to include it on the server side before base64 encoding to ensure proper encoding throughout the entire data flow.

## Solution
Added UTF-8 BOM (`\xEF\xBB\xBF`) at the beginning of the CSV content on the server side in the `ajax_export_conversation` function. This ensures:

1. **Proper encoding declaration**: The BOM tells applications like Excel to interpret the file as UTF-8
2. **Cyrillic character preservation**: Russian, Ukrainian, and other Cyrillic text displays correctly
3. **Data integrity**: The BOM is included before base64 encoding, ensuring it survives the transport process

## Changes Made

### 1. Server-side PHP (`ai-multilingual-chat.php`)
**Location**: Line 1037-1040
```php
// Before:
$csv_output = "Дата,Время,Отправитель,Сообщение,Перевод\n";

// After:
$csv_output = "\xEF\xBB\xBF"; // UTF-8 BOM
$csv_output .= "Дата,Время,Отправитель,Сообщение,Перевод\n";
```

### 2. Client-side JavaScript (`admin-script.js`)
**Location**: Line 538-545
```javascript
// Before:
const csvContent = atob(response.data.csv);
const BOM = '\uFEFF';
const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });

// After:
const csvContent = atob(response.data.csv);
// BOM is now included in the CSV content from the server
const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
```

### 3. Updated Tests
- Enhanced `test-csv-export.php` to verify BOM byte values (0xEF 0xBB 0xBF)
- Added comprehensive test for CSV export with Cyrillic characters
- Updated `test-csv-export.js` to reflect server-side BOM inclusion
- Created `test-cyrillic-export.php` demonstration test

## Testing
All existing tests pass, plus new tests that specifically verify:
- UTF-8 BOM is present at the start of CSV content
- BOM has correct byte values (0xEF, 0xBB, 0xBF)
- Cyrillic characters are preserved through base64 encoding/decoding
- CSV files are valid UTF-8 throughout the export process

Run tests:
```bash
# PHP tests
php tests/test-csv-export.php
php tests/test-cyrillic-export.php

# JavaScript tests
node tests/test-csv-export.js
```

## What is UTF-8 BOM?
The UTF-8 BOM (Byte Order Mark) is a special sequence of three bytes (`EF BB BF` in hexadecimal) placed at the beginning of a UTF-8 encoded file. It signals to text editors and spreadsheet applications that the file uses UTF-8 encoding, which is crucial for correctly displaying non-ASCII characters like Cyrillic, Chinese, Arabic, etc.

## Benefits
1. **Excel compatibility**: Microsoft Excel correctly recognizes and opens the CSV with proper encoding
2. **Cross-platform**: Works correctly on Windows, macOS, and Linux
3. **International support**: Supports all languages using non-Latin scripts
4. **Standard compliance**: Follows RFC 3629 recommendations for UTF-8 files

## Before and After

### Before (Broken)
```
Äàòà,Âðåìÿ,Îòïðàâèòåëü,Ñîîáùåíèå,Ïåðåâîä
"2024-01-15","10:30:00","Àäìèíèñòðàòîð","Çäðàâñòâóéòå!","Hello!"
```

### After (Fixed)
```
Дата,Время,Отправитель,Сообщение,Перевод
"2024-01-15","10:30:00","Администратор","Здравствуйте!","Hello!"
```

## References
- [RFC 3629 - UTF-8](https://tools.ietf.org/html/rfc3629)
- [Unicode BOM FAQ](https://www.unicode.org/faq/utf_bom.html)
- [CSV and Character Encoding](https://en.wikipedia.org/wiki/Byte_order_mark#UTF-8)
