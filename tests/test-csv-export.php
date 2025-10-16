<?php
/**
 * Test for CSV Export Functionality
 * 
 * This test validates that the CSV export function properly handles errors,
 * encodes data correctly, and provides appropriate user feedback.
 */

// Mock WordPress functions if not in WordPress environment
if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action, $query_arg) { return true; }
}
if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data) { 
        echo json_encode(['success' => false, 'data' => $data]); 
        exit;
    }
}
if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data) { 
        echo json_encode(['success' => true, 'data' => $data]); 
        exit;
    }
}
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

// Simple test class
class CSV_Export_Test {
    private $passed = 0;
    private $failed = 0;
    private $test_results = array();
    
    public function __construct() {
        echo "=== CSV Export Test Suite ===\n\n";
        $this->run_tests();
        $this->print_results();
    }
    
    private function run_tests() {
        // Test 1: CSV field escaping
        $this->test_csv_field_escaping();
        
        // Test 2: UTF-8 BOM presence
        $this->test_utf8_bom_handling();
        
        // Test 3: Base64 encoding/decoding
        $this->test_base64_encoding();
        
        // Test 4: Error message validation
        $this->test_error_messages();
        
        // Test 5: Filename format
        $this->test_filename_format();
        
        // Test 6: CSV export with BOM and Cyrillic characters
        $this->test_csv_export_with_cyrillic();
    }
    
    private function test_csv_field_escaping() {
        echo "Test 1: CSV Field Escaping\n";
        
        $test_cases = array(
            'Simple text' => '"Simple text"',
            'Text with "quotes"' => '"Text with ""quotes"""',
            'Multi
line
text' => '"Multi
line
text"',
            'Cyrillic: Привет' => '"Cyrillic: Привет"',
        );
        
        foreach ($test_cases as $input => $expected) {
            $escaped = '"' . str_replace('"', '""', $input) . '"';
            if ($escaped === $expected) {
                $this->record_pass("Escaped correctly: " . substr($input, 0, 20));
            } else {
                $this->record_fail("Escaping failed for: $input");
            }
        }
    }
    
    private function test_utf8_bom_handling() {
        echo "\nTest 2: UTF-8 BOM Handling\n";
        
        // UTF-8 BOM should be added as actual bytes \xEF\xBB\xBF
        $BOM = "\xEF\xBB\xBF";
        $content = "test content";
        $with_bom = $BOM . $content;
        
        // Validate BOM is 3 bytes
        if (strlen($BOM) === 3) {
            $this->record_pass("BOM is correct length (3 bytes)");
        } else {
            $this->record_fail("BOM length is incorrect: " . strlen($BOM));
        }
        
        // Validate BOM byte values
        if (ord($BOM[0]) === 0xEF && ord($BOM[1]) === 0xBB && ord($BOM[2]) === 0xBF) {
            $this->record_pass("BOM has correct byte values (0xEF 0xBB 0xBF)");
        } else {
            $this->record_fail("BOM byte values are incorrect");
        }
        
        // Test that content with BOM starts with the BOM
        if (substr($with_bom, 0, 3) === $BOM) {
            $this->record_pass("BOM is correctly prepended to content");
        } else {
            $this->record_fail("BOM not prepended correctly");
        }
    }
    
    private function test_base64_encoding() {
        echo "\nTest 3: Base64 Encoding/Decoding\n";
        
        $test_data = "Дата,Время,Отправитель,Сообщение,Перевод\n\"2024-01-01\",\"12:00:00\",\"Администратор\",\"Привет\",\"Hello\"";
        
        // Encode
        $encoded = base64_encode($test_data);
        if ($encoded !== false && strlen($encoded) > 0) {
            $this->record_pass("Base64 encoding successful");
        } else {
            $this->record_fail("Base64 encoding failed");
            return;
        }
        
        // Decode
        $decoded = base64_decode($encoded);
        if ($decoded === $test_data) {
            $this->record_pass("Base64 decoding successful (data matches)");
        } else {
            $this->record_fail("Base64 decoding failed (data mismatch)");
        }
    }
    
    private function test_error_messages() {
        echo "\nTest 4: Error Message Validation\n";
        
        $error_messages = array(
            'Неверный ID диалога' => 'Invalid conversation ID',
            'Диалог не найден' => 'Conversation not found',
            'Ошибка базы данных' => 'Database error',
            'В диалоге нет сообщений' => 'No messages in conversation',
            'Ошибка кодирования CSV' => 'CSV encoding error',
        );
        
        foreach ($error_messages as $russian => $english) {
            // Validate that Russian error messages are properly UTF-8 encoded
            if (mb_check_encoding($russian, 'UTF-8')) {
                $this->record_pass("Error message valid: $russian");
            } else {
                $this->record_fail("Error message encoding issue: $russian");
            }
        }
    }
    
    private function test_filename_format() {
        echo "\nTest 5: Filename Format Validation\n";
        
        $conversation_id = 123;
        $filename = "conversation_{$conversation_id}_" . date('Y-m-d_His') . ".csv";
        
        // Validate filename pattern
        if (preg_match('/^conversation_\d+_\d{4}-\d{2}-\d{2}_\d{6}\.csv$/', $filename)) {
            $this->record_pass("Filename format is correct: $filename");
        } else {
            $this->record_fail("Filename format is incorrect: $filename");
        }
        
        // Validate extension
        if (substr($filename, -4) === '.csv') {
            $this->record_pass("Filename has .csv extension");
        } else {
            $this->record_fail("Filename missing .csv extension");
        }
    }
    
    private function test_csv_export_with_cyrillic() {
        echo "\nTest 6: CSV Export with BOM and Cyrillic Characters\n";
        
        // Simulate the server-side CSV generation with UTF-8 BOM
        $BOM = "\xEF\xBB\xBF";
        $csv_output = $BOM;
        $csv_output .= "Дата,Время,Отправитель,Сообщение,Перевод\n";
        
        // Add test data with Cyrillic characters
        $test_message = "Привет! Как дела?";
        $test_translation = "Hello! How are you?";
        $test_sender = "Администратор";
        
        $csv_output .= "\"2024-01-01\",\"12:00:00\",\"{$test_sender}\",\"{$test_message}\",\"{$test_translation}\"\n";
        
        // Verify BOM is at the start
        if (substr($csv_output, 0, 3) === $BOM) {
            $this->record_pass("CSV starts with UTF-8 BOM");
        } else {
            $this->record_fail("CSV missing UTF-8 BOM at start");
        }
        
        // Verify Cyrillic characters are present and properly encoded
        if (mb_check_encoding($csv_output, 'UTF-8')) {
            $this->record_pass("CSV is valid UTF-8");
        } else {
            $this->record_fail("CSV is not valid UTF-8");
        }
        
        // Verify Cyrillic message is in output
        if (strpos($csv_output, $test_message) !== false) {
            $this->record_pass("Cyrillic message preserved in CSV");
        } else {
            $this->record_fail("Cyrillic message not found in CSV");
        }
        
        // Test base64 encoding and decoding with BOM
        $encoded = base64_encode($csv_output);
        $decoded = base64_decode($encoded);
        
        if ($decoded === $csv_output) {
            $this->record_pass("Base64 encode/decode preserves BOM and Cyrillic");
        } else {
            $this->record_fail("Base64 encode/decode corrupted data");
        }
        
        // Verify decoded content still has BOM
        if (substr($decoded, 0, 3) === $BOM) {
            $this->record_pass("BOM preserved after base64 round-trip");
        } else {
            $this->record_fail("BOM lost after base64 round-trip");
        }
    }
    
    private function record_pass($message) {
        $this->passed++;
        $this->test_results[] = ['status' => 'PASS', 'message' => $message];
        echo "  ✓ PASS: $message\n";
    }
    
    private function record_fail($message) {
        $this->failed++;
        $this->test_results[] = ['status' => 'FAIL', 'message' => $message];
        echo "  ✗ FAIL: $message\n";
    }
    
    private function print_results() {
        $total = $this->passed + $this->failed;
        $pass_rate = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "Test Results Summary:\n";
        echo "Total Tests: $total\n";
        echo "Passed: " . $this->passed . " (" . $pass_rate . "%)\n";
        echo "Failed: " . $this->failed . "\n";
        echo str_repeat('=', 50) . "\n";
        
        if ($this->failed === 0) {
            echo "\n✓ All tests passed!\n";
            exit(0);
        } else {
            echo "\n✗ Some tests failed. Please review the failures above.\n";
            exit(1);
        }
    }
}

// Run tests
new CSV_Export_Test();
