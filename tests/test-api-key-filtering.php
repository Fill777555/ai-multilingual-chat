<?php
/**
 * Test for API Key Filtering Functionality
 * 
 * This test validates that the contains_api_key() function properly detects
 * various API key patterns and prevents them from being translated.
 */

// Mock WordPress functions if not in WordPress environment
if (!function_exists('get_option')) {
    function get_option($option, $default = '') { return $default; }
}

// Simple test class
class API_Key_Filter_Test {
    private $test_cases = array();
    private $passed = 0;
    private $failed = 0;
    
    public function __construct() {
        echo "=== API Key Filtering Test Suite ===\n\n";
        $this->setup_test_cases();
        $this->run_tests();
        $this->print_results();
    }
    
    private function setup_test_cases() {
        // Test cases that SHOULD be detected as API keys
        $this->test_cases['should_detect'] = array(
            'sk-1234567890abcdefghijklmnopqrstuvwxyz' => 'OpenAI API key',
            'aic_abcdefghij1234567890xyz' => 'Plugin mobile API key',
            'AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567' => 'Google API key',
            'Bearer sk-1234567890abcdefghijklmnopqrstuvwxyz' => 'Bearer token with OpenAI key',
            'api_key: sk-1234567890abcdefghijklmnopqrstuvwxyz' => 'Explicit API key mention',
            'api-key=1234567890abcdefghijklmnopqrstuvwxyz' => 'API key assignment',
            '1234567890abcdefghijklmnopqrstuvwxyz1234567890' => 'Long alphanumeric string (40 chars)',
        );
        
        // Test cases that SHOULD NOT be detected as API keys
        $this->test_cases['should_not_detect'] = array(
            'Hello, how are you?' => 'Normal greeting',
            'This is a test message' => 'Normal message',
            'Password123' => 'Short password-like string',
            'Email: user@example.com' => 'Email address',
            'http://example.com/api/endpoint' => 'API endpoint URL',
            'The API documentation is here' => 'Text mentioning API',
        );
    }
    
    private function run_tests() {
        echo "Testing strings that SHOULD be detected as API keys:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->test_cases['should_detect'] as $text => $description) {
            $result = $this->test_contains_api_key($text);
            $expected = true;
            $passed = ($result === $expected);
            
            $this->print_test_result($description, $text, $result, $expected, $passed);
            
            if ($passed) {
                $this->passed++;
            } else {
                $this->failed++;
            }
        }
        
        echo "\n\nTesting strings that SHOULD NOT be detected as API keys:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->test_cases['should_not_detect'] as $text => $description) {
            $result = $this->test_contains_api_key($text);
            $expected = false;
            $passed = ($result === $expected);
            
            $this->print_test_result($description, $text, $result, $expected, $passed);
            
            if ($passed) {
                $this->passed++;
            } else {
                $this->failed++;
            }
        }
    }
    
    private function test_contains_api_key($text) {
        // Replicate the contains_api_key logic from the plugin
        $api_key_patterns = array(
            '/sk-[a-zA-Z0-9]{32,}/',           // OpenAI keys (sk-...)
            '/aic_[a-zA-Z0-9]{20,}/',          // Plugin mobile API keys (aic_...)
            '/AIzaSy[a-zA-Z0-9_-]{33}/',       // Google API keys
            '/[a-zA-Z0-9]{32,64}/',            // Generic long alphanumeric strings (potential keys)
            '/Bearer\s+[a-zA-Z0-9._-]+/i',     // Bearer tokens
            '/api[_-]?key[:\s=]+[a-zA-Z0-9]+/i', // Explicit API key mentions
        );
        
        foreach ($api_key_patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function print_test_result($description, $text, $result, $expected, $passed) {
        $status = $passed ? '✓ PASS' : '✗ FAIL';
        $color = $passed ? '' : '';
        
        echo sprintf(
            "%s - %s\n  Text: %s\n  Result: %s, Expected: %s\n\n",
            $status,
            $description,
            substr($text, 0, 50) . (strlen($text) > 50 ? '...' : ''),
            $result ? 'DETECTED' : 'NOT DETECTED',
            $expected ? 'DETECTED' : 'NOT DETECTED'
        );
    }
    
    private function print_results() {
        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TEST RESULTS:\n";
        echo str_repeat("=", 60) . "\n";
        echo sprintf("Total Tests: %d\n", $total);
        echo sprintf("Passed: %d\n", $this->passed);
        echo sprintf("Failed: %d\n", $this->failed);
        echo sprintf("Success Rate: %s%%\n", $percentage);
        echo str_repeat("=", 60) . "\n";
        
        if ($this->failed === 0) {
            echo "✓ All tests passed!\n";
        } else {
            echo "✗ Some tests failed. Please review the implementation.\n";
        }
    }
}

// Run the test
new API_Key_Filter_Test();
