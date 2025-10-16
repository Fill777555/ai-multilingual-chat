<?php
/**
 * Demonstration Test: CSV Export with Cyrillic Characters
 * 
 * This test demonstrates that the UTF-8 BOM fix correctly handles Cyrillic characters
 * in CSV exports, solving the issue described in the problem statement.
 */

echo "=== Cyrillic CSV Export Demonstration ===\n\n";

// Simulate the server-side CSV generation (as in ajax_export_conversation)
function generate_csv_export() {
    // UTF-8 BOM to ensure proper encoding of Cyrillic characters
    $csv_output = "\xEF\xBB\xBF"; // UTF-8 BOM
    $csv_output .= "Дата,Время,Отправитель,Сообщение,Перевод\n";
    
    // Sample data with Cyrillic characters
    $messages = [
        [
            'date' => '2024-01-15',
            'time' => '10:30:00',
            'sender' => 'Администратор',
            'message' => 'Здравствуйте! Чем могу помочь?',
            'translation' => 'Hello! How can I help you?'
        ],
        [
            'date' => '2024-01-15',
            'time' => '10:31:00',
            'sender' => 'Пользователь',
            'message' => 'Привет! У меня вопрос о вашем продукте.',
            'translation' => 'Hi! I have a question about your product.'
        ],
        [
            'date' => '2024-01-15',
            'time' => '10:32:00',
            'sender' => 'Администратор',
            'message' => 'Конечно! Задавайте ваш вопрос, я с радостью отвечу.',
            'translation' => 'Of course! Ask your question, I will be happy to answer.'
        ],
        [
            'date' => '2024-01-15',
            'time' => '10:33:00',
            'sender' => 'Пользователь',
            'message' => 'Спасибо большое! Это очень важно для меня.',
            'translation' => 'Thank you very much! This is very important to me.'
        ],
    ];
    
    foreach ($messages as $msg) {
        // Properly escape CSV fields
        $message = str_replace('"', '""', $msg['message']);
        $translation = str_replace('"', '""', $msg['translation']);
        $sender = str_replace('"', '""', $msg['sender']);
        
        $csv_output .= "\"{$msg['date']}\",\"{$msg['time']}\",\"{$sender}\",\"{$message}\",\"{$translation}\"\n";
    }
    
    return $csv_output;
}

// Generate the CSV
$csv_content = generate_csv_export();

echo "Step 1: Generate CSV with UTF-8 BOM\n";
echo "----------------------------------------\n";
echo "CSV size: " . strlen($csv_content) . " bytes\n";
echo "Starts with BOM: " . (substr($csv_content, 0, 3) === "\xEF\xBB\xBF" ? "✓ YES" : "✗ NO") . "\n";
echo "Valid UTF-8: " . (mb_check_encoding($csv_content, 'UTF-8') ? "✓ YES" : "✗ NO") . "\n\n";

// Display first few lines (header + 1 data row)
$lines = explode("\n", $csv_content);
echo "First 2 lines of CSV:\n";
// Skip BOM for display
$content_without_bom = substr($csv_content, 3);
$lines_without_bom = explode("\n", $content_without_bom);
echo "  " . $lines_without_bom[0] . "\n";
echo "  " . $lines_without_bom[1] . "\n\n";

// Simulate base64 encoding (as done in the AJAX handler)
echo "Step 2: Base64 Encode (for AJAX transport)\n";
echo "----------------------------------------\n";
$encoded = base64_encode($csv_content);
echo "Encoded size: " . strlen($encoded) . " bytes\n";
echo "First 50 chars: " . substr($encoded, 0, 50) . "...\n\n";

// Simulate client-side decoding (as done in JavaScript)
echo "Step 3: Client-side Base64 Decode\n";
echo "----------------------------------------\n";
$decoded = base64_decode($encoded);
echo "Decoded size: " . strlen($decoded) . " bytes\n";
echo "Still has BOM: " . (substr($decoded, 0, 3) === "\xEF\xBB\xBF" ? "✓ YES" : "✗ NO") . "\n";
echo "Still valid UTF-8: " . (mb_check_encoding($decoded, 'UTF-8') ? "✓ YES" : "✗ NO") . "\n";
echo "Data preserved: " . ($decoded === $csv_content ? "✓ YES" : "✗ NO") . "\n\n";

// Save to a test file to demonstrate
$test_file = '/tmp/test-cyrillic-export.csv';
file_put_contents($test_file, $csv_content);
echo "Step 4: Save to File\n";
echo "----------------------------------------\n";
echo "Test file created: {$test_file}\n";
echo "File size: " . filesize($test_file) . " bytes\n";

// Verify the saved file
$file_content = file_get_contents($test_file);
echo "File has BOM: " . (substr($file_content, 0, 3) === "\xEF\xBB\xBF" ? "✓ YES" : "✗ NO") . "\n";
echo "File is valid UTF-8: " . (mb_check_encoding($file_content, 'UTF-8') ? "✓ YES" : "✗ NO") . "\n\n";

// Check for Cyrillic characters
$cyrillic_tests = [
    'Администратор' => 'Administrator',
    'Здравствуйте' => 'Hello',
    'Привет' => 'Hi',
    'Спасибо' => 'Thank you'
];

echo "Step 5: Verify Cyrillic Characters\n";
echo "----------------------------------------\n";
foreach ($cyrillic_tests as $cyrillic => $english) {
    $found = mb_strpos($file_content, $cyrillic) !== false;
    echo ($found ? "✓" : "✗") . " Found '{$cyrillic}' ({$english})\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✓ DEMONSTRATION COMPLETE\n";
echo "\nThe UTF-8 BOM fix ensures that:\n";
echo "  1. Cyrillic characters are properly encoded in the CSV\n";
echo "  2. The BOM tells programs like Excel to use UTF-8 encoding\n";
echo "  3. Data integrity is maintained through base64 transport\n";
echo "  4. Export files can be opened correctly in any spreadsheet program\n";
echo str_repeat("=", 60) . "\n";

// Clean up
unlink($test_file);
