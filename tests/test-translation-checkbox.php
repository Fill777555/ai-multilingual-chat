#!/usr/bin/env php
<?php
/**
 * Test for aic_enable_translation checkbox functionality
 * Verifies that the checkbox properly saves, loads, and controls translation behavior
 * 
 * Usage: Run this script from plugin directory: php tests/test-translation-checkbox.php
 */

class TranslationCheckboxTest {
    
    private $passed = 0;
    private $failed = 0;
    private $errors = array();
    private $basePath;
    
    public function __construct() {
        $this->basePath = dirname(__DIR__) . '/';
    }
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "\n=== Translation Checkbox Functionality Tests ===\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Test 1: Check if checkbox exists in settings template
        $this->test_checkbox_in_settings();
        
        // Test 2: Check if save handler exists
        $this->test_save_handler_exists();
        
        // Test 3: Check if option is passed to frontend script
        $this->test_frontend_localization();
        
        // Test 4: Check if option is passed to admin script
        $this->test_admin_localization();
        
        // Test 5: Check if translation function checks the option
        $this->test_translation_check();
        
        // Test 6: Check default value in plugin activation
        $this->test_default_value_on_activation();
        
        $this->print_summary();
    }
    
    /**
     * Test 1: Check if checkbox exists in settings template
     */
    private function test_checkbox_in_settings() {
        echo "Test 1: Check if aic_enable_translation checkbox exists in settings\n";
        
        $settingsFile = $this->basePath . 'ai-multilingual-chat/templates/settings.php';
        
        if (!file_exists($settingsFile)) {
            echo "❌ FAILED: Settings file not found\n\n";
            $this->failed++;
            $this->errors[] = "Settings file not found: {$settingsFile}";
            return;
        }
        
        $content = file_get_contents($settingsFile);
        
        // Check for checkbox input
        if (strpos($content, 'name="aic_enable_translation"') !== false &&
            strpos($content, 'id="aic_enable_translation"') !== false &&
            strpos($content, 'type="checkbox"') !== false) {
            echo "✅ PASSED: Checkbox exists in settings template\n\n";
            $this->passed++;
        } else {
            echo "❌ FAILED: Checkbox not found in settings template\n\n";
            $this->failed++;
            $this->errors[] = "Checkbox aic_enable_translation not found in settings.php";
        }
    }
    
    /**
     * Test 2: Check if save handler exists
     */
    private function test_save_handler_exists() {
        echo "Test 2: Check if save handler for aic_enable_translation exists\n";
        
        $pluginFile = $this->basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';
        
        if (!file_exists($pluginFile)) {
            echo "❌ FAILED: Plugin file not found\n\n";
            $this->failed++;
            $this->errors[] = "Plugin file not found: {$pluginFile}";
            return;
        }
        
        $content = file_get_contents($pluginFile);
        
        // Check for update_option call for aic_enable_translation
        if (strpos($content, "update_option('aic_enable_translation'") !== false ||
            strpos($content, 'update_option("aic_enable_translation"') !== false) {
            echo "✅ PASSED: Save handler exists for aic_enable_translation\n\n";
            $this->passed++;
        } else {
            echo "❌ FAILED: Save handler not found for aic_enable_translation\n\n";
            $this->failed++;
            $this->errors[] = "update_option for aic_enable_translation not found";
        }
    }
    
    /**
     * Test 3: Check if option is passed to frontend script
     */
    private function test_frontend_localization() {
        echo "Test 3: Check if aic_enable_translation is passed to frontend JavaScript\n";
        
        $pluginFile = $this->basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($pluginFile);
        
        // Find the wp_localize_script for aicFrontend
        $pattern = '/wp_localize_script\s*\(\s*[\'"]aic-frontend-script[\'"]\s*,\s*[\'"]aicFrontend[\'"]\s*,\s*array\s*\((.*?)\)\s*\);/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $localized_data = $matches[1];
            
            if (strpos($localized_data, "'enable_translation'") !== false ||
                strpos($localized_data, '"enable_translation"') !== false) {
                echo "✅ PASSED: enable_translation is passed to frontend script\n\n";
                $this->passed++;
            } else {
                echo "❌ FAILED: enable_translation NOT found in aicFrontend localization\n\n";
                $this->failed++;
                $this->errors[] = "enable_translation not found in wp_localize_script for aicFrontend";
            }
        } else {
            echo "❌ FAILED: Could not find aicFrontend localization\n\n";
            $this->failed++;
            $this->errors[] = "wp_localize_script for aicFrontend not found";
        }
    }
    
    /**
     * Test 4: Check if option is passed to admin script
     */
    private function test_admin_localization() {
        echo "Test 4: Check if aic_enable_translation is passed to admin JavaScript\n";
        
        $pluginFile = $this->basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($pluginFile);
        
        // Find the wp_localize_script for aicAdmin
        $pattern = '/wp_localize_script\s*\(\s*[\'"]aic-admin-script[\'"]\s*,\s*[\'"]aicAdmin[\'"]\s*,\s*array\s*\((.*?)\)\s*\);/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $localized_data = $matches[1];
            
            if (strpos($localized_data, "'enable_translation'") !== false ||
                strpos($localized_data, '"enable_translation"') !== false) {
                echo "✅ PASSED: enable_translation is passed to admin script\n\n";
                $this->passed++;
            } else {
                echo "❌ FAILED: enable_translation NOT found in aicAdmin localization\n\n";
                $this->failed++;
                $this->errors[] = "enable_translation not found in wp_localize_script for aicAdmin";
            }
        } else {
            echo "❌ FAILED: Could not find aicAdmin localization\n\n";
            $this->failed++;
            $this->errors[] = "wp_localize_script for aicAdmin not found";
        }
    }
    
    /**
     * Test 5: Check if translation function checks the option
     */
    private function test_translation_check() {
        echo "Test 5: Check if translation function checks aic_enable_translation option\n";
        
        $pluginFile = $this->basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($pluginFile);
        
        // Check if translation functions check the option before translating
        $pattern = "/get_option\s*\(\s*['\"]aic_enable_translation['\"]/";
        
        if (preg_match($pattern, $content)) {
            echo "✅ PASSED: Translation code checks aic_enable_translation option\n\n";
            $this->passed++;
        } else {
            echo "❌ FAILED: Translation code does not check aic_enable_translation\n\n";
            $this->failed++;
            $this->errors[] = "get_option('aic_enable_translation') not found in translation code";
        }
    }
    
    /**
     * Test 6: Check default value in plugin activation
     */
    private function test_default_value_on_activation() {
        echo "Test 6: Check if default value is set on plugin activation\n";
        
        $pluginFile = $this->basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';
        $content = file_get_contents($pluginFile);
        
        // Look for default value setting in activation or set_default_options
        $pattern = "/['\"]aic_enable_translation['\"]\s*=>\s*['\"]1['\"]/";
        
        if (preg_match($pattern, $content)) {
            echo "✅ PASSED: Default value '1' (enabled) is set on activation\n\n";
            $this->passed++;
        } else {
            echo "❌ FAILED: Default value not found or incorrect\n\n";
            $this->failed++;
            $this->errors[] = "Default value for aic_enable_translation not set to '1'";
        }
    }
    
    /**
     * Print test summary
     */
    private function print_summary() {
        $total = $this->passed + $this->failed;
        
        echo "\n=== Test Summary ===\n";
        echo "Total Tests: {$total}\n";
        echo "✅ Passed: {$this->passed}\n";
        echo "❌ Failed: {$this->failed}\n";
        
        $success_rate = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        echo "Success Rate: {$success_rate}%\n";
        
        if (!empty($this->errors)) {
            echo "\n=== Errors ===\n";
            foreach ($this->errors as $i => $error) {
                echo ($i + 1) . ". {$error}\n";
            }
        }
        
        echo "\n";
        
        if ($this->failed === 0) {
            echo "🎉 All tests passed! The translation checkbox is working correctly.\n\n";
            exit(0);
        } else {
            echo "⚠️  Some tests failed. Please review the errors above.\n\n";
            exit(1);
        }
    }
}

// Run the tests
echo "\n";
echo "============================================\n";
echo "  Translation Checkbox Functionality Test  \n";
echo "============================================\n";

$tester = new TranslationCheckboxTest();
$tester->run_all_tests();
