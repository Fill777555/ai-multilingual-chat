<?php
/**
 * Test: HEX Input Field Feature
 * 
 * This test verifies the new HEX input field functionality:
 * - HEX input fields are present in settings.php
 * - JavaScript synchronization is implemented
 * - CSS styling for HEX input is defined
 * - New header color settings are present
 * 
 * @package AI_Multilingual_Chat
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../');
}

class AIC_HEX_Input_Test {
    
    private $test_results = array();
    private $passed = 0;
    private $failed = 0;
    
    /**
     * Run all tests
     */
    public function run_tests() {
        echo "=== AI Multilingual Chat - HEX Input Feature Test ===\n\n";
        
        $this->test_hex_input_fields();
        $this->test_hex_input_css();
        $this->test_hex_validation_js();
        $this->test_header_color_settings();
        $this->test_organized_sections();
        $this->test_reset_colors_updated();
        
        $this->print_results();
    }
    
    /**
     * Test 1: Verify HEX input fields replace span elements
     */
    private function test_hex_input_fields() {
        $test_name = "HEX Input Fields Replace Spans";
        
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        
        if (!file_exists($settings_file)) {
            $this->record_result($test_name, false, "Settings file not found");
            return;
        }
        
        $content = file_get_contents($settings_file);
        
        // Count HEX input fields
        $hex_input_count = substr_count($content, 'class="aic-color-hex-input"');
        
        // Should have at least 13 HEX inputs (9 original + 4 new header colors)
        $expected_count = 13;
        
        if ($hex_input_count >= $expected_count) {
            $this->record_result($test_name, true, "Found $hex_input_count HEX input fields (expected at least $expected_count)");
        } else {
            $this->record_result($test_name, false, "Found only $hex_input_count HEX input fields (expected at least $expected_count)");
        }
    }
    
    /**
     * Test 2: Verify CSS styling for HEX input
     */
    private function test_hex_input_css() {
        $test_name = "HEX Input CSS Styling";
        
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        $required_css = array(
            '.aic-color-hex-input',
            'font-family:',
            'text-transform: uppercase',
            'transition: border-color'
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($required_css as $css) {
            if (strpos($content, $css) === false) {
                $all_found = false;
                $missing[] = $css;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All required CSS properties are defined");
        } else {
            $this->record_result($test_name, false, "Missing CSS: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 3: Verify JavaScript validation logic
     */
    private function test_hex_validation_js() {
        $test_name = "HEX Validation JavaScript";
        
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        $required_js = array(
            "('.aic-color-hex-input').on('input'",
            "('.aic-color-hex-input').on('blur'",
            "/^#[0-9A-Fa-f]{6}$/",
            "border-color', '#4CAF50'",
            "border-color', '#f44336'"
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($required_js as $js) {
            if (strpos($content, $js) === false) {
                $all_found = false;
                $missing[] = $js;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "JavaScript validation logic is implemented");
        } else {
            $this->record_result($test_name, false, "Missing JS: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 4: Verify new header color settings
     */
    private function test_header_color_settings() {
        $test_name = "New Header Color Settings";
        
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        $required_settings = array(
            'aic_header_text_color',
            'aic_header_status_color',
            'aic_header_icons_color',
            'aic_header_close_color'
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($required_settings as $setting) {
            if (strpos($content, $setting) === false) {
                $all_found = false;
                $missing[] = $setting;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All 4 new header color settings are present");
        } else {
            $this->record_result($test_name, false, "Missing settings: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 5: Verify organized sections with h4 headers
     */
    private function test_organized_sections() {
        $test_name = "Organized Color Sections";
        
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        $required_sections = array(
            '<h4>Основные цвета виджета</h4>',
            '<h4>Цвета заголовка чата</h4>',
            '<h4>Цвета сообщений</h4>',
            '<h4>Цвета элементов управления</h4>'
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($required_sections as $section) {
            if (strpos($content, $section) === false) {
                $all_found = false;
                $missing[] = $section;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All 4 subsections are properly organized");
        } else {
            $this->record_result($test_name, false, "Missing sections: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 6: Verify reset colors function includes new settings
     */
    private function test_reset_colors_updated() {
        $test_name = "Reset Colors Function Updated";
        
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        $content = file_get_contents($settings_file);
        
        // Check if reset function includes new header colors
        $has_header_text = strpos($content, "'aic_header_text_color': '#ffffff'") !== false;
        $has_header_status = strpos($content, "'aic_header_status_color': '#ffffff'") !== false;
        $has_header_icons = strpos($content, "'aic_header_icons_color': '#ffffff'") !== false;
        $has_header_close = strpos($content, "'aic_header_close_color': '#ffffff'") !== false;
        
        if ($has_header_text && $has_header_status && $has_header_icons && $has_header_close) {
            $this->record_result($test_name, true, "Reset colors function includes all new settings");
        } else {
            $this->record_result($test_name, false, "Reset colors function missing new settings");
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
$test = new AIC_HEX_Input_Test();
$test->run_tests();
