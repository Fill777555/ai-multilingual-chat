<?php
/**
 * Test for Settings Logging Functionality
 * 
 * This test verifies that the detailed logging added to save_settings()
 * and the settings loading template correctly logs all operations.
 * 
 * @package AI_Multilingual_Chat
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../');
}

class AIC_Settings_Logging_Test {
    
    private $test_results = array();
    private $passed = 0;
    private $failed = 0;
    private $log_entries = array();
    
    /**
     * Run all tests
     */
    public function run_tests() {
        echo "=== AI Multilingual Chat - Settings Logging Test ===\n\n";
        
        $this->test_save_settings_logging_structure();
        $this->test_load_settings_logging_structure();
        $this->test_logging_guards();
        $this->test_sensitive_data_handling();
        
        $this->print_results();
    }
    
    /**
     * Test 1: Verify save_settings logging structure
     */
    private function test_save_settings_logging_structure() {
        $test_name = "Save Settings Logging Structure";
        
        // Read the main plugin file
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($plugin_file);
        
        $checks = array(
            'Start marker' => strpos($content, '=== SAVING SETTINGS START ===') !== false,
            'End marker' => strpos($content, '=== SAVING SETTINGS END ===') !== false,
            'Setting value log' => strpos($content, 'Saving {$setting} = {$value}') !== false,
            'Verification log' => strpos($content, 'verification: {$saved_value}') !== false,
            'Error handling' => strpos($content, 'FAILED to save {$setting}') !== false,
            'Update result check' => strpos($content, 'if ($result === false)') !== false,
        );
        
        $all_passed = true;
        foreach ($checks as $check_name => $result) {
            if (!$result) {
                $all_passed = false;
                echo "  ✗ Missing: {$check_name}\n";
            } else {
                echo "  ✓ Found: {$check_name}\n";
            }
        }
        
        if ($all_passed) {
            $this->passed++;
            echo "✓ PASS - {$test_name}\n\n";
        } else {
            $this->failed++;
            echo "✗ FAIL - {$test_name}\n\n";
        }
    }
    
    /**
     * Test 2: Verify load settings logging structure
     */
    private function test_load_settings_logging_structure() {
        $test_name = "Load Settings Logging Structure";
        
        // Read the settings template
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        $checks = array(
            'Start marker' => strpos($content, '=== LOADING SETTINGS START ===') !== false,
            'End marker' => strpos($content, '=== LOADING SETTINGS END ===') !== false,
            'Loading log pattern' => strpos($content, 'Loaded aic_') !== false,
            'WP_DEBUG guard' => strpos($content, "if (defined('WP_DEBUG') && WP_DEBUG)") !== false,
            'Multiple settings logged' => substr_count($content, 'Loaded aic_') >= 10,
        );
        
        $all_passed = true;
        foreach ($checks as $check_name => $result) {
            if (!$result) {
                $all_passed = false;
                echo "  ✗ Missing: {$check_name}\n";
            } else {
                echo "  ✓ Found: {$check_name}\n";
            }
        }
        
        if ($all_passed) {
            $this->passed++;
            echo "✓ PASS - {$test_name}\n\n";
        } else {
            $this->failed++;
            echo "✗ FAIL - {$test_name}\n\n";
        }
    }
    
    /**
     * Test 3: Verify logging guards are in place
     */
    private function test_logging_guards() {
        $test_name = "Logging Guards (WP_DEBUG check)";
        
        // Read the settings template
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        // Count WP_DEBUG guards in settings.php
        $guard_count = substr_count($content, "if (defined('WP_DEBUG') && WP_DEBUG)");
        
        // Read the main plugin file for log method
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        $plugin_content = file_get_contents($plugin_file);
        
        // Check if log method has WP_DEBUG guard
        preg_match('/private function log\([^)]*\).*?\{(.*?)\}/s', $plugin_content, $matches);
        $log_method_has_guard = false;
        if (isset($matches[1])) {
            $log_method_has_guard = strpos($matches[1], "if (defined('WP_DEBUG') && WP_DEBUG)") !== false;
        }
        
        echo "  - WP_DEBUG guards in settings.php: {$guard_count}\n";
        echo "  - Log method has WP_DEBUG guard: " . ($log_method_has_guard ? "YES" : "NO") . "\n";
        
        if ($guard_count >= 5 && $log_method_has_guard) {
            $this->passed++;
            echo "✓ PASS - {$test_name}\n\n";
        } else {
            $this->failed++;
            echo "✗ FAIL - {$test_name}\n\n";
        }
    }
    
    /**
     * Test 4: Verify sensitive data is handled properly
     */
    private function test_sensitive_data_handling() {
        $test_name = "Sensitive Data Handling";
        
        // Read the settings template
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        $checks = array(
            'API key hidden' => strpos($content, '***HIDDEN***') !== false,
            'Empty check for API key' => strpos($content, "empty(\$api_key) ? '(empty)' : '***HIDDEN***'") !== false,
            'Empty check for mobile key' => strpos($content, "empty(\$mobile_api_key) ? '(empty)' : '***HIDDEN***'") !== false,
        );
        
        $all_passed = true;
        foreach ($checks as $check_name => $result) {
            if (!$result) {
                $all_passed = false;
                echo "  ✗ Missing: {$check_name}\n";
            } else {
                echo "  ✓ Found: {$check_name}\n";
            }
        }
        
        if ($all_passed) {
            $this->passed++;
            echo "✓ PASS - {$test_name}\n\n";
        } else {
            $this->failed++;
            echo "✗ FAIL - {$test_name}\n\n";
        }
    }
    
    /**
     * Print test results
     */
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
            echo "\nThe logging implementation correctly:\n";
            echo "  - Logs settings save operations with detailed information\n";
            echo "  - Logs settings load operations with all values\n";
            echo "  - Guards logging behind WP_DEBUG checks\n";
            echo "  - Hides sensitive data (API keys) from logs\n";
        } else {
            echo "✗ Some tests failed. Please review the implementation.\n";
        }
    }
}

// Run the test
$test = new AIC_Settings_Logging_Test();
$test->run_tests();
