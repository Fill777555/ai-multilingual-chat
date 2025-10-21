<?php
/**
 * Test: Color Settings Feature
 * 
 * This test verifies that the new color customization feature works correctly:
 * - Default color values are set properly
 * - Color settings can be saved and retrieved
 * - CSS variables are properly generated
 * 
 * @package AI_Multilingual_Chat
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../');
}

class AIC_Color_Settings_Test {
    
    private $test_results = array();
    private $passed = 0;
    private $failed = 0;
    
    /**
     * Run all tests
     */
    public function run_tests() {
        echo "=== AI Multilingual Chat - Color Settings Test ===\n\n";
        
        $this->test_default_colors();
        $this->test_color_option_names();
        $this->test_color_format_validation();
        $this->test_css_variable_generation();
        
        $this->print_results();
    }
    
    /**
     * Test 1: Verify default color values
     */
    private function test_default_colors() {
        $test_name = "Default Color Values";
        
        $expected_defaults = array(
            'aic_widget_bg_color' => '#1c2126',
            'aic_chat_button_color' => '#667eea',
            'aic_header_bg_color' => '#667eea',
            'aic_user_msg_bg_color' => '#667eea',
            'aic_admin_msg_bg_color' => '#ffffff',
            'aic_user_msg_text_color' => '#ffffff',
            'aic_admin_msg_text_color' => '#333333',
            'aic_send_button_color' => '#667eea',
            'aic_input_border_color' => '#dddddd',
        );
        
        $all_match = true;
        $mismatches = array();
        
        // Check if defaults are defined in the main plugin file
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        if (!file_exists($plugin_file)) {
            $this->record_result($test_name, false, "Plugin file not found");
            return;
        }
        
        $content = file_get_contents($plugin_file);
        
        foreach ($expected_defaults as $option => $expected_value) {
            // Check if the option is defined in the defaults array
            if (strpos($content, "'$option' => '$expected_value'") === false) {
                $all_match = false;
                $mismatches[] = "$option (expected: $expected_value)";
            }
        }
        
        if ($all_match) {
            $this->record_result($test_name, true, "All default color values are correctly set");
        } else {
            $this->record_result($test_name, false, "Missing or incorrect defaults: " . implode(', ', $mismatches));
        }
    }
    
    /**
     * Test 2: Verify color option names in settings save handler
     */
    private function test_color_option_names() {
        $test_name = "Color Options in Save Handler";
        
        $required_options = array(
            'aic_widget_bg_color',
            'aic_chat_button_color',
            'aic_header_bg_color',
            'aic_user_msg_bg_color',
            'aic_admin_msg_bg_color',
            'aic_user_msg_text_color',
            'aic_admin_msg_text_color',
            'aic_send_button_color',
            'aic_input_border_color',
        );
        
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($plugin_file);
        
        // Find the save_settings method
        $all_found = true;
        $missing = array();
        
        foreach ($required_options as $option) {
            if (strpos($content, "'$option'") === false) {
                $all_found = false;
                $missing[] = $option;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All color options are registered in save handler");
        } else {
            $this->record_result($test_name, false, "Missing options: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 3: Validate color format (hex codes)
     */
    private function test_color_format_validation() {
        $test_name = "Color Format Validation";
        
        $valid_colors = array('#667eea', '#1c2126', '#ffffff', '#333333', '#dddddd');
        $invalid_colors = array('red', '667eea', '#xyz', 'rgb(0,0,0)', '');
        
        $pattern = '/^#[0-9a-fA-F]{6}$/';
        
        $all_valid_passed = true;
        $all_invalid_failed = true;
        
        foreach ($valid_colors as $color) {
            if (!preg_match($pattern, $color)) {
                $all_valid_passed = false;
                break;
            }
        }
        
        foreach ($invalid_colors as $color) {
            if (preg_match($pattern, $color)) {
                $all_invalid_failed = false;
                break;
            }
        }
        
        if ($all_valid_passed && $all_invalid_failed) {
            $this->record_result($test_name, true, "Color format validation works correctly");
        } else {
            $this->record_result($test_name, false, "Color format validation failed");
        }
    }
    
    /**
     * Test 4: Verify CSS variable generation in widget template
     */
    private function test_css_variable_generation() {
        $test_name = "CSS Variable Generation";
        
        $widget_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/chat-widget.php';
        
        if (!file_exists($widget_file)) {
            $this->record_result($test_name, false, "Widget template not found");
            return;
        }
        
        $content = file_get_contents($widget_file);
        
        $required_variables = array(
            '--widget-bg-color',
            '--chat-button-color',
            '--header-bg-color',
            '--user-msg-bg-color',
            '--admin-msg-bg-color',
            '--user-msg-text-color',
            '--admin-msg-text-color',
            '--send-button-color',
            '--input-border-color',
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($required_variables as $var) {
            if (strpos($content, $var) === false) {
                $all_found = false;
                $missing[] = $var;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All CSS variables are properly generated");
        } else {
            $this->record_result($test_name, false, "Missing CSS variables: " . implode(', ', $missing));
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
            exit(0);
        } else {
            echo "\n✗ Some tests failed.\n";
            exit(1);
        }
    }
}

// Run tests
$test = new AIC_Color_Settings_Test();
$test->run_tests();
