<?php
/**
 * Test: Theme Switching Functionality
 * 
 * This test verifies:
 * 1. CSS variables are properly defined for light and dark themes
 * 2. Theme toggle script is enqueued correctly
 * 3. Theme mode option is saved and retrieved correctly
 * 4. Admin template contains theme toggle buttons
 */

class ThemeSwitchingTest {
    private $results = array();
    private $passed = 0;
    private $failed = 0;
    
    public function run() {
        echo "<h1>Theme Switching Functionality Test</h1>\n";
        echo "<div style='font-family: monospace; padding: 20px;'>\n";
        
        $this->test_css_variables_exist();
        $this->test_theme_toggle_script_exists();
        $this->test_theme_mode_option();
        $this->test_admin_template_has_toggle();
        $this->test_settings_has_dropdown();
        $this->test_theme_mode_in_localized_script();
        
        $this->display_results();
        
        echo "</div>\n";
    }
    
    private function test_css_variables_exist() {
        $test_name = "CSS Variables Existence";
        $css_file = dirname(__FILE__) . '/../ai-multilingual-chat/admin-style.css';
        
        if (!file_exists($css_file)) {
            $this->fail($test_name, "CSS file not found");
            return;
        }
        
        $css_content = file_get_contents($css_file);
        
        $required_vars = array(
            '--aic-bg',
            '--aic-surface',
            '--aic-text-primary',
            '--aic-text-secondary',
            '--aic-accent',
            '--aic-card-radius',
            '--aic-transition'
        );
        
        $missing_vars = array();
        foreach ($required_vars as $var) {
            if (strpos($css_content, $var) === false) {
                $missing_vars[] = $var;
            }
        }
        
        if (empty($missing_vars)) {
            // Check for dark theme attribute
            if (strpos($css_content, '[data-theme="dark"]') !== false) {
                $this->pass($test_name, "All CSS variables defined for both themes");
            } else {
                $this->fail($test_name, "Dark theme attribute not found");
            }
        } else {
            $this->fail($test_name, "Missing CSS variables: " . implode(', ', $missing_vars));
        }
    }
    
    private function test_theme_toggle_script_exists() {
        $test_name = "Theme Toggle Script Existence";
        $js_file = dirname(__FILE__) . '/../ai-multilingual-chat/assets/theme-toggle.js';
        
        if (!file_exists($js_file)) {
            $this->fail($test_name, "Theme toggle script not found");
            return;
        }
        
        $js_content = file_get_contents($js_file);
        
        // Check for required functions
        $required_elements = array(
            'localStorage',
            'data-theme',
            'prefersDark',
            'applyTheme',
            'saveMode',
            'loadMode'
        );
        
        $missing_elements = array();
        foreach ($required_elements as $element) {
            if (strpos($js_content, $element) === false) {
                $missing_elements[] = $element;
            }
        }
        
        if (empty($missing_elements)) {
            $this->pass($test_name, "Theme toggle script has all required functionality");
        } else {
            $this->fail($test_name, "Missing elements: " . implode(', ', $missing_elements));
        }
    }
    
    private function test_theme_mode_option() {
        $test_name = "Theme Mode Option in Plugin";
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        
        if (!file_exists($plugin_file)) {
            $this->fail($test_name, "Main plugin file not found");
            return;
        }
        
        $plugin_content = file_get_contents($plugin_file);
        
        // Check if theme_mode is in defaults
        if (strpos($plugin_content, "'aic_theme_mode' => 'auto'") !== false) {
            $this->pass($test_name, "Theme mode default option found in plugin");
        } else {
            $this->fail($test_name, "Theme mode default option not found in plugin");
        }
    }
    
    private function test_admin_template_has_toggle() {
        $test_name = "Admin Template Theme Toggle";
        $template_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/admin-chat.php';
        
        if (!file_exists($template_file)) {
            $this->fail($test_name, "Admin template not found");
            return;
        }
        
        $template_content = file_get_contents($template_file);
        
        // Check for toggle buttons
        if (strpos($template_content, 'data-aic-theme-toggle') !== false &&
            strpos($template_content, 'aic-controls') !== false &&
            strpos($template_content, 'aria-pressed') !== false) {
            $this->pass($test_name, "Admin template has accessible theme toggle buttons");
        } else {
            $this->fail($test_name, "Admin template missing theme toggle elements");
        }
    }
    
    private function test_settings_has_dropdown() {
        $test_name = "Settings Page Theme Dropdown";
        $settings_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/settings.php';
        
        if (!file_exists($settings_file)) {
            $this->fail($test_name, "Settings template not found");
            return;
        }
        
        $settings_content = file_get_contents($settings_file);
        
        // Check for theme dropdown
        if (strpos($settings_content, 'aic_theme_mode') !== false &&
            strpos($settings_content, 'Светлая') !== false &&
            strpos($settings_content, 'Тёмная') !== false &&
            strpos($settings_content, 'Авто') !== false) {
            $this->pass($test_name, "Settings page has theme dropdown with all options");
        } else {
            $this->fail($test_name, "Settings page missing theme dropdown or options");
        }
    }
    
    private function test_theme_mode_in_localized_script() {
        $test_name = "Theme Mode in Localized Script";
        $plugin_file = dirname(__FILE__) . '/../ai-multilingual-chat/ai-multilingual-chat.php';
        
        if (!file_exists($plugin_file)) {
            $this->fail($test_name, "Main plugin file not found");
            return;
        }
        
        $plugin_content = file_get_contents($plugin_file);
        
        // Check if theme_mode is in localized script
        if (strpos($plugin_content, "'theme_mode'") !== false &&
            strpos($plugin_content, "get_option('aic_theme_mode'") !== false) {
            $this->pass($test_name, "Theme mode is included in localized script data");
        } else {
            $this->fail($test_name, "Theme mode not found in localized script data");
        }
    }
    
    private function pass($test_name, $message = "") {
        $this->passed++;
        $this->results[] = array(
            'status' => 'PASS',
            'test' => $test_name,
            'message' => $message
        );
    }
    
    private function fail($test_name, $message = "") {
        $this->failed++;
        $this->results[] = array(
            'status' => 'FAIL',
            'test' => $test_name,
            'message' => $message
        );
    }
    
    private function display_results() {
        echo "<hr>\n";
        echo "<h2>Test Results</h2>\n";
        echo "<table style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='border: 1px solid #ccc; padding: 8px;'>Status</th>";
        echo "<th style='border: 1px solid #ccc; padding: 8px;'>Test</th>";
        echo "<th style='border: 1px solid #ccc; padding: 8px;'>Message</th>";
        echo "</tr>\n";
        
        foreach ($this->results as $result) {
            $color = $result['status'] === 'PASS' ? '#4caf50' : '#f44336';
            echo "<tr>";
            echo "<td style='border: 1px solid #ccc; padding: 8px; color: white; background: $color; font-weight: bold;'>{$result['status']}</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$result['test']}</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$result['message']}</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
        echo "<h3>Summary</h3>\n";
        echo "<p><strong>Passed:</strong> {$this->passed} | <strong>Failed:</strong> {$this->failed}</p>\n";
        
        if ($this->failed === 0) {
            echo "<p style='color: #4caf50; font-weight: bold;'>✓ All tests passed!</p>\n";
        } else {
            echo "<p style='color: #f44336; font-weight: bold;'>✗ Some tests failed. Please review.</p>\n";
        }
    }
}

$test = new ThemeSwitchingTest();
$test->run();
