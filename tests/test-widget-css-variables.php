<?php
/**
 * Test: Widget CSS Variables for Header Colors
 * 
 * This test verifies the widget template properly applies the new header color CSS variables
 * 
 * @package AI_Multilingual_Chat
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../');
}

class AIC_Widget_CSS_Test {
    
    private $test_results = array();
    private $passed = 0;
    private $failed = 0;
    
    /**
     * Run all tests
     */
    public function run_tests() {
        echo "=== AI Multilingual Chat - Widget CSS Variables Test ===\n\n";
        
        $this->test_css_variables_defined();
        $this->test_css_variables_applied();
        $this->test_header_elements_use_variables();
        
        $this->print_results();
    }
    
    /**
     * Test 1: Verify CSS variables are defined in widget template
     */
    private function test_css_variables_defined() {
        $test_name = "CSS Variables Defined in Widget";
        
        $widget_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/chat-widget.php';
        
        if (!file_exists($widget_file)) {
            $this->record_result($test_name, false, "Widget file not found");
            return;
        }
        
        $content = file_get_contents($widget_file);
        
        $required_variables = array(
            '--header-text-color',
            '--header-status-color',
            '--header-icons-color',
            '--header-close-color'
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
            $this->record_result($test_name, true, "All 4 new CSS variables are defined");
        } else {
            $this->record_result($test_name, false, "Missing CSS variables: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 2: Verify CSS variables are assigned values from PHP
     */
    private function test_css_variables_applied() {
        $test_name = "CSS Variables Get PHP Values";
        
        $widget_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/chat-widget.php';
        $content = file_get_contents($widget_file);
        
        $required_assignments = array(
            'get_option(\'aic_header_text_color\'',
            'get_option(\'aic_header_status_color\'',
            'get_option(\'aic_header_icons_color\'',
            'get_option(\'aic_header_close_color\''
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($required_assignments as $assignment) {
            if (strpos($content, $assignment) === false) {
                $all_found = false;
                $missing[] = $assignment;
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All CSS variables get values from database options");
        } else {
            $this->record_result($test_name, false, "Missing get_option calls: " . implode(', ', $missing));
        }
    }
    
    /**
     * Test 3: Verify header elements use CSS variables
     */
    private function test_header_elements_use_variables() {
        $test_name = "Header Elements Use CSS Variables";
        
        $widget_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/chat-widget.php';
        $content = file_get_contents($widget_file);
        
        // Check if elements use the variables
        $checks = array(
            'h3' => array(
                'selector' => '.aic-chat-header h3',
                'property' => 'color: var(--header-text-color)'
            ),
            'status' => array(
                'selector' => '.aic-chat-status',
                'property' => 'color: var(--header-status-color)'
            ),
            'icons' => array(
                'selector' => '.aic-icon-button',
                'property' => 'color: var(--header-icons-color)'
            ),
            'close' => array(
                'selector' => '.aic-chat-close',
                'property' => 'color: var(--header-close-color)'
            )
        );
        
        $all_found = true;
        $missing = array();
        
        foreach ($checks as $name => $check) {
            // Check if selector exists
            if (strpos($content, $check['selector']) === false) {
                $all_found = false;
                $missing[] = "Selector: " . $check['selector'];
                continue;
            }
            
            // Check if property is applied
            if (strpos($content, $check['property']) === false) {
                $all_found = false;
                $missing[] = "Property: " . $check['property'];
            }
        }
        
        if ($all_found) {
            $this->record_result($test_name, true, "All header elements properly use CSS variables");
        } else {
            $this->record_result($test_name, false, "Missing: " . implode(', ', $missing));
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
$test = new AIC_Widget_CSS_Test();
$test->run_tests();
