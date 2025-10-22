<?php
/**
 * Test: Settings Cache Clearing
 * 
 * This test verifies that the cache clearing mechanism works correctly
 * when settings are saved, ensuring that changes are immediately visible.
 * 
 * @package AI_Multilingual_Chat
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../');
}

class AIC_Settings_Cache_Clear_Test {
    
    private $test_results = array();
    private $passed = 0;
    private $failed = 0;
    
    /**
     * Run all tests
     */
    public function run_tests() {
        echo "=== AI Multilingual Chat - Settings Cache Clear Test ===\n\n";
        
        $this->test_cache_flush_called();
        $this->test_opcache_reset_called();
        $this->test_redirect_after_save();
        $this->test_cache_clear_in_template();
        $this->test_logging_implementation();
        
        $this->print_results();
    }
    
    /**
     * Test 1: Verify wp_cache_flush() is called in save_settings
     */
    private function test_cache_flush_called() {
        $test_name = "wp_cache_flush() Called in save_settings";
        
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        if (!file_exists($plugin_file)) {
            $this->record_result($test_name, false, "Plugin file not found");
            return;
        }
        
        $content = file_get_contents($plugin_file);
        
        // Check if wp_cache_flush() is called in save_settings method
        $pattern = '/private\s+function\s+save_settings.*?wp_cache_flush\s*\(\s*\)/s';
        
        if (preg_match($pattern, $content)) {
            $this->record_result($test_name, true, "wp_cache_flush() is properly called after saving settings");
        } else {
            $this->record_result($test_name, false, "wp_cache_flush() not found in save_settings method");
        }
    }
    
    /**
     * Test 2: Verify opcache_reset() is conditionally called
     */
    private function test_opcache_reset_called() {
        $test_name = "opcache_reset() Conditionally Called";
        
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($plugin_file);
        
        // Check if opcache_reset() is called with function_exists check
        $pattern = '/function_exists\s*\(\s*[\'"]opcache_reset[\'"]\s*\).*?opcache_reset\s*\(\s*\)/s';
        
        if (preg_match($pattern, $content)) {
            $this->record_result($test_name, true, "opcache_reset() is conditionally called when available");
        } else {
            $this->record_result($test_name, false, "opcache_reset() not properly implemented with function_exists check");
        }
    }
    
    /**
     * Test 3: Verify Post/Redirect/Get pattern is implemented
     */
    private function test_redirect_after_save() {
        $test_name = "Post/Redirect/Get Pattern Implementation";
        
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($plugin_file);
        
        // Check for wp_redirect with settings-updated parameter
        $has_redirect = strpos($content, 'wp_redirect') !== false;
        $has_settings_updated = strpos($content, 'settings-updated') !== false;
        $has_get_check = strpos($content, "\$_GET['settings-updated']") !== false;
        
        if ($has_redirect && $has_settings_updated && $has_get_check) {
            $this->record_result($test_name, true, "Post/Redirect/Get pattern is correctly implemented");
        } else {
            $details = array();
            if (!$has_redirect) $details[] = "wp_redirect not found";
            if (!$has_settings_updated) $details[] = "settings-updated parameter missing";
            if (!$has_get_check) $details[] = "\$_GET check missing";
            
            $this->record_result($test_name, false, "Missing components: " . implode(', ', $details));
        }
    }
    
    /**
     * Test 4: Verify cache clearing in settings template
     */
    private function test_cache_clear_in_template() {
        $test_name = "Cache Clearing in Settings Template";
        
        $template_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        if (!file_exists($template_file)) {
            $this->record_result($test_name, false, "Settings template not found");
            return;
        }
        
        $content = file_get_contents($template_file);
        
        // Check for wp_cache_delete before get_option calls
        $has_cache_delete = strpos($content, "wp_cache_delete('alloptions', 'options')") !== false;
        
        if ($has_cache_delete) {
            // Verify it comes before the first get_option
            $cache_pos = strpos($content, "wp_cache_delete");
            $option_pos = strpos($content, "get_option");
            
            if ($cache_pos < $option_pos) {
                $this->record_result($test_name, true, "Cache is cleared before loading settings");
            } else {
                $this->record_result($test_name, false, "Cache clearing happens after get_option calls");
            }
        } else {
            $this->record_result($test_name, false, "wp_cache_delete not found in settings template");
        }
    }
    
    /**
     * Test 5: Verify detailed logging is implemented
     */
    private function test_logging_implementation() {
        $test_name = "Detailed Logging in save_settings";
        
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($plugin_file);
        
        // Check for logging markers
        $has_start_log = strpos($content, '=== SAVING SETTINGS START ===') !== false;
        $has_end_log = strpos($content, '=== SAVING SETTINGS END ===') !== false;
        $has_update_log = strpos($content, 'Updating ') !== false;
        
        if ($has_start_log && $has_end_log && $has_update_log) {
            $this->record_result($test_name, true, "Detailed logging is properly implemented");
        } else {
            $details = array();
            if (!$has_start_log) $details[] = "START log missing";
            if (!$has_end_log) $details[] = "END log missing";
            if (!$has_update_log) $details[] = "Update logs missing";
            
            $this->record_result($test_name, false, "Incomplete logging: " . implode(', ', $details));
        }
    }
    
    /**
     * Record test result
     */
    private function record_result($test_name, $passed, $message) {
        $this->test_results[] = array(
            'name' => $test_name,
            'passed' => $passed,
            'message' => $message
        );
        
        if ($passed) {
            $this->passed++;
        } else {
            $this->failed++;
        }
    }
    
    /**
     * Print test results
     */
    private function print_results() {
        echo "\n=== Test Results ===\n\n";
        
        foreach ($this->test_results as $result) {
            $status = $result['passed'] ? '✓ PASS' : '✗ FAIL';
            echo sprintf("[%s] %s\n", $status, $result['name']);
            echo sprintf("    %s\n\n", $result['message']);
        }
        
        $total = $this->passed + $this->failed;
        echo "----------------------------------------\n";
        echo sprintf("Total: %d tests | Passed: %d | Failed: %d\n", $total, $this->passed, $this->failed);
        echo "----------------------------------------\n";
        
        if ($this->failed === 0) {
            echo "\n✓ All tests passed!\n";
            echo "\nThe cache clearing mechanism is properly implemented.\n";
            echo "Settings should now be immediately visible after saving.\n";
            exit(0);
        } else {
            echo "\n✗ Some tests failed.\n";
            echo "Please review the implementation.\n";
            exit(1);
        }
    }
}

// Run tests
$test = new AIC_Settings_Cache_Clear_Test();
$test->run_tests();
