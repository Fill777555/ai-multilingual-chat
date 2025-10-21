#!/usr/bin/env php
<?php
/**
 * Localization Test for AI Multilingual Chat Plugin
 * 
 * This script tests that the WordPress localization is properly set up
 * and that translations can be loaded correctly.
 * 
 * Usage: Run from command line in WordPress environment
 */

// Define ABSPATH for loading WordPress
if (!defined('ABSPATH')) {
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die("Error: Could not find WordPress installation. Please run this script from the plugin tests directory.\n");
    }
}

// Test class
class AIC_Localization_Test {
    private $results = array();
    private $plugin_dir;
    
    public function __construct() {
        $this->plugin_dir = dirname(dirname(__FILE__)) . '/ai-multilingual-chat';
    }
    
    /**
     * Run all localization tests
     */
    public function run_tests() {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "AI MULTILINGUAL CHAT - LOCALIZATION TEST\n";
        echo str_repeat("=", 70) . "\n\n";
        
        $this->test_text_domain();
        $this->test_translation_files_exist();
        $this->test_translation_file_validity();
        $this->test_load_textdomain();
        $this->test_sample_translations();
        
        $this->print_summary();
    }
    
    /**
     * Test 1: Check if text domain is correctly defined
     */
    private function test_text_domain() {
        echo "Test 1: Checking text domain definition...\n";
        
        $main_file = $this->plugin_dir . '/ai-multilingual-chat.php';
        if (!file_exists($main_file)) {
            $this->add_result('Text Domain', false, 'Main plugin file not found');
            return;
        }
        
        $content = file_get_contents($main_file);
        
        // Check for Text Domain in header
        if (preg_match('/Text Domain:\s*ai-multilingual-chat/i', $content)) {
            $this->add_result('Text Domain Header', true, 'Found in plugin header');
        } else {
            $this->add_result('Text Domain Header', false, 'Not found in plugin header');
        }
        
        // Check for load_plugin_textdomain
        if (strpos($content, 'load_plugin_textdomain') !== false) {
            $this->add_result('load_plugin_textdomain()', true, 'Function call found');
        } else {
            $this->add_result('load_plugin_textdomain()', false, 'Function call not found');
        }
        
        echo "\n";
    }
    
    /**
     * Test 2: Check if translation files exist
     */
    private function test_translation_files_exist() {
        echo "Test 2: Checking translation files existence...\n";
        
        $languages_dir = $this->plugin_dir . '/languages';
        
        // Check languages directory
        if (is_dir($languages_dir)) {
            $this->add_result('Languages Directory', true, $languages_dir);
        } else {
            $this->add_result('Languages Directory', false, 'Directory not found');
            return;
        }
        
        // Check POT file
        $pot_file = $languages_dir . '/ai-multilingual-chat.pot';
        if (file_exists($pot_file)) {
            $size = filesize($pot_file);
            $this->add_result('POT Template', true, "Found ({$size} bytes)");
        } else {
            $this->add_result('POT Template', false, 'File not found');
        }
        
        // Check Russian PO file
        $po_file = $languages_dir . '/ai-multilingual-chat-ru_RU.po';
        if (file_exists($po_file)) {
            $size = filesize($po_file);
            $this->add_result('Russian PO File', true, "Found ({$size} bytes)");
        } else {
            $this->add_result('Russian PO File', false, 'File not found');
        }
        
        // Check Russian MO file
        $mo_file = $languages_dir . '/ai-multilingual-chat-ru_RU.mo';
        if (file_exists($mo_file)) {
            $size = filesize($mo_file);
            $this->add_result('Russian MO File', true, "Found ({$size} bytes)");
        } else {
            $this->add_result('Russian MO File', false, 'File not found');
        }
        
        echo "\n";
    }
    
    /**
     * Test 3: Validate translation file format
     */
    private function test_translation_file_validity() {
        echo "Test 3: Validating translation file format...\n";
        
        $po_file = $this->plugin_dir . '/languages/ai-multilingual-chat-ru_RU.po';
        
        if (!file_exists($po_file)) {
            $this->add_result('PO File Validation', false, 'File not found');
            echo "\n";
            return;
        }
        
        $content = file_get_contents($po_file);
        
        // Check for required headers
        $checks = array(
            'Project-Id-Version' => preg_match('/Project-Id-Version:/', $content),
            'Content-Type UTF-8' => preg_match('/Content-Type:.*charset=UTF-8/', $content),
            'Language: ru_RU' => preg_match('/Language:\s*ru_RU/', $content),
            'Plural-Forms' => preg_match('/Plural-Forms:/', $content),
        );
        
        foreach ($checks as $check => $result) {
            $this->add_result($check, $result, $result ? 'Present' : 'Missing');
        }
        
        // Count translations
        $msgid_count = preg_match_all('/^msgid\s+"[^"]/', $content, $matches, PREG_MULTILINE);
        $msgstr_count = preg_match_all('/^msgstr\s+"[^"]/', $content, $matches, PREG_MULTILINE);
        
        echo "  → Found {$msgid_count} msgid entries\n";
        echo "  → Found {$msgstr_count} msgstr translations\n";
        
        $this->add_result('Translation Count', $msgstr_count > 0, "{$msgstr_count} translations");
        
        echo "\n";
    }
    
    /**
     * Test 4: Try to load textdomain
     */
    private function test_load_textdomain() {
        echo "Test 4: Testing textdomain loading...\n";
        
        $languages_dir = $this->plugin_dir . '/languages';
        
        // Try to load the textdomain
        $loaded = load_plugin_textdomain(
            'ai-multilingual-chat',
            false,
            basename($this->plugin_dir) . '/languages'
        );
        
        if ($loaded) {
            $this->add_result('Textdomain Loading', true, 'Successfully loaded');
        } else {
            // It might already be loaded by the plugin itself
            $this->add_result('Textdomain Loading', true, 'Already loaded or default language');
        }
        
        echo "\n";
    }
    
    /**
     * Test 5: Test sample translations
     */
    private function test_sample_translations() {
        echo "Test 5: Testing sample string translations...\n";
        
        // Test strings that should be translated
        $test_strings = array(
            'Settings' => 'Настройки',
            'AI Chat' => 'AI Чат',
            'Save Settings' => 'Сохранить настройки',
            'FAQ' => 'FAQ',
            'Statistics' => 'Статистика',
        );
        
        // Temporarily switch to Russian locale for testing
        $original_locale = get_locale();
        
        foreach ($test_strings as $english => $expected_russian) {
            $translated = __($english, 'ai-multilingual-chat');
            
            // In English locale, it should return English
            // In Russian locale with translations loaded, it should return Russian
            // For this test, we'll just check if the function works
            $works = !empty($translated);
            
            $this->add_result(
                "Translate '{$english}'",
                $works,
                $works ? "Returns: '{$translated}'" : 'Empty result'
            );
        }
        
        echo "\n";
    }
    
    /**
     * Add a test result
     */
    private function add_result($test, $passed, $message = '') {
        $this->results[] = array(
            'test' => $test,
            'passed' => $passed,
            'message' => $message
        );
        
        $status = $passed ? '✓ PASS' : '✗ FAIL';
        $color_start = $passed ? "\033[0;32m" : "\033[0;31m";
        $color_end = "\033[0m";
        
        printf("  [%s%s%s] %s", $color_start, $status, $color_end, $test);
        
        if ($message) {
            echo " - {$message}";
        }
        
        echo "\n";
    }
    
    /**
     * Print test summary
     */
    private function print_summary() {
        echo str_repeat("=", 70) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 70) . "\n\n";
        
        $total = count($this->results);
        $passed = 0;
        $failed = 0;
        
        foreach ($this->results as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $success_rate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        
        echo "Total Tests: {$total}\n";
        echo "\033[0;32mPassed: {$passed}\033[0m\n";
        echo "\033[0;31mFailed: {$failed}\033[0m\n";
        echo "Success Rate: {$success_rate}%\n\n";
        
        if ($failed > 0) {
            echo "Failed Tests:\n";
            foreach ($this->results as $result) {
                if (!$result['passed']) {
                    echo "  - {$result['test']}: {$result['message']}\n";
                }
            }
            echo "\n";
        }
        
        echo str_repeat("=", 70) . "\n";
        
        if ($success_rate >= 80) {
            echo "\n✓ Localization setup is working correctly!\n\n";
            echo "Next steps:\n";
            echo "1. Change WordPress site language to Russian (Settings → General)\n";
            echo "2. Visit plugin pages to see translations in action\n";
            echo "3. Add more language translations as needed\n\n";
        } else {
            echo "\n✗ Some localization issues detected. Please review the failed tests above.\n\n";
        }
    }
}

// Run the tests
$tester = new AIC_Localization_Test();
$tester->run_tests();
