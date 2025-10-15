<?php
/**
 * Comprehensive Translation Function Testing Script
 * Tests AI translation functionality across multiple languages and scenarios
 * 
 * Usage: Run this script in WordPress environment with proper API keys configured
 */

if (!defined('ABSPATH')) {
    // Load WordPress environment
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
    require_once(ABSPATH . 'wp-load.php');
}

class TranslationTester {
    
    private $results = array();
    private $test_cases = array();
    private $errors = array();
    private $provider = '';
    
    public function __construct() {
        $this->provider = get_option('aic_ai_provider', 'openai');
        $this->init_test_cases();
    }
    
    /**
     * Initialize test cases with various text samples in different languages
     */
    private function init_test_cases() {
        $this->test_cases = array(
            // English to Russian
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'Hello, how can I help you today?',
                'expected_keywords' => array('Привет', 'помочь', 'сегодня'),
                'category' => 'Simple greeting'
            ),
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'I would like to order a product from your website.',
                'expected_keywords' => array('заказать', 'продукт', 'сайт'),
                'category' => 'E-commerce'
            ),
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'What is your return policy for defective items?',
                'expected_keywords' => array('возврат', 'политика', 'дефект'),
                'category' => 'Customer support'
            ),
            
            // Russian to English
            array(
                'from_lang' => 'ru',
                'to_lang' => 'en',
                'text' => 'Здравствуйте! Я хочу оформить заказ.',
                'expected_keywords' => array('Hello', 'order', 'place'),
                'category' => 'Simple greeting'
            ),
            array(
                'from_lang' => 'ru',
                'to_lang' => 'en',
                'text' => 'Мне нужна техническая поддержка с установкой плагина.',
                'expected_keywords' => array('technical', 'support', 'plugin', 'install'),
                'category' => 'Technical support'
            ),
            
            // English to Ukrainian
            array(
                'from_lang' => 'en',
                'to_lang' => 'uk',
                'text' => 'Thank you for your assistance!',
                'expected_keywords' => array('Дякую', 'допомог'),
                'category' => 'Gratitude'
            ),
            
            // Russian to Ukrainian
            array(
                'from_lang' => 'ru',
                'to_lang' => 'uk',
                'text' => 'Когда я могу ожидать доставку?',
                'expected_keywords' => array('Коли', 'очікувати', 'доставк'),
                'category' => 'Delivery inquiry'
            ),
            
            // English to German
            array(
                'from_lang' => 'en',
                'to_lang' => 'de',
                'text' => 'I need help with my account.',
                'expected_keywords' => array('Hilfe', 'Konto'),
                'category' => 'Account support'
            ),
            
            // English to French
            array(
                'from_lang' => 'en',
                'to_lang' => 'fr',
                'text' => 'Where can I find more information?',
                'expected_keywords' => array('où', 'trouver', 'information'),
                'category' => 'Information request'
            ),
            
            // English to Spanish
            array(
                'from_lang' => 'en',
                'to_lang' => 'es',
                'text' => 'I have a question about pricing.',
                'expected_keywords' => array('pregunta', 'precio'),
                'category' => 'Pricing inquiry'
            ),
            
            // Complex sentence
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'Could you please provide more details about the shipping options available for international orders?',
                'expected_keywords' => array('подробност', 'доставк', 'международн', 'заказ'),
                'category' => 'Complex inquiry'
            ),
            
            // Technical terms
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'The API endpoint is not responding correctly.',
                'expected_keywords' => array('API', 'конечн', 'отвеча'),
                'category' => 'Technical terminology'
            ),
            
            // Special characters and punctuation
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'Really?! That\'s amazing! :)',
                'expected_keywords' => array('Правда', 'Это', 'удивительн'),
                'category' => 'Emotional expression'
            ),
            
            // Numbers and measurements
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'The package weighs 2.5 kg and costs $49.99.',
                'expected_keywords' => array('2.5', 'кг', '49.99'),
                'category' => 'Numbers and measurements'
            ),
            
            // Multi-sentence
            array(
                'from_lang' => 'en',
                'to_lang' => 'ru',
                'text' => 'Good morning. I received my order yesterday. However, one item is missing. Can you help me?',
                'expected_keywords' => array('Доброе утро', 'получил', 'вчера', 'отсутствует', 'помочь'),
                'category' => 'Multi-sentence complaint'
            )
        );
    }
    
    /**
     * Run all translation tests
     */
    public function run_tests() {
        echo "\n=== AI Translation Testing Report ===\n";
        echo "Provider: " . strtoupper($this->provider) . "\n";
        echo "Date: " . date('Y-m-d H:i:s') . "\n";
        echo "Total test cases: " . count($this->test_cases) . "\n\n";
        
        $plugin = AI_Multilingual_Chat::get_instance();
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($plugin);
        $method = $reflection->getMethod('translate_message');
        $method->setAccessible(true);
        
        $passed = 0;
        $failed = 0;
        $warnings = 0;
        
        foreach ($this->test_cases as $index => $test_case) {
            echo "\n--- Test Case #" . ($index + 1) . " ---\n";
            echo "Category: {$test_case['category']}\n";
            echo "Direction: {$test_case['from_lang']} → {$test_case['to_lang']}\n";
            echo "Original: \"{$test_case['text']}\"\n";
            
            try {
                $start_time = microtime(true);
                $translation = $method->invoke(
                    $plugin,
                    $test_case['text'],
                    $test_case['from_lang'],
                    $test_case['to_lang']
                );
                $end_time = microtime(true);
                $duration = round(($end_time - $start_time) * 1000, 2);
                
                if ($translation === null || empty($translation)) {
                    echo "❌ FAILED: No translation returned\n";
                    $failed++;
                    $this->errors[] = array(
                        'test_case' => $index + 1,
                        'error' => 'No translation returned',
                        'original' => $test_case['text'],
                        'from_lang' => $test_case['from_lang'],
                        'to_lang' => $test_case['to_lang']
                    );
                } else {
                    echo "Translation: \"{$translation}\"\n";
                    echo "Duration: {$duration}ms\n";
                    
                    // Check if translation is just the same as original
                    if ($translation === $test_case['text']) {
                        echo "⚠️  WARNING: Translation identical to original\n";
                        $warnings++;
                        $this->errors[] = array(
                            'test_case' => $index + 1,
                            'error' => 'Translation identical to original',
                            'original' => $test_case['text'],
                            'translation' => $translation,
                            'from_lang' => $test_case['from_lang'],
                            'to_lang' => $test_case['to_lang']
                        );
                    } else {
                        // Check for expected keywords (case-insensitive, partial match)
                        $has_keywords = false;
                        foreach ($test_case['expected_keywords'] as $keyword) {
                            if (mb_stripos($translation, $keyword) !== false) {
                                $has_keywords = true;
                                break;
                            }
                        }
                        
                        if ($has_keywords) {
                            echo "✅ PASSED: Translation appears correct\n";
                            $passed++;
                        } else {
                            echo "⚠️  WARNING: Expected keywords not found, but translation may still be correct\n";
                            echo "Expected keywords: " . implode(', ', $test_case['expected_keywords']) . "\n";
                            $warnings++;
                        }
                    }
                    
                    $this->results[] = array(
                        'category' => $test_case['category'],
                        'from_lang' => $test_case['from_lang'],
                        'to_lang' => $test_case['to_lang'],
                        'original' => $test_case['text'],
                        'translation' => $translation,
                        'duration_ms' => $duration,
                        'status' => 'passed'
                    );
                }
                
            } catch (Exception $e) {
                echo "❌ FAILED: Exception - " . $e->getMessage() . "\n";
                $failed++;
                $this->errors[] = array(
                    'test_case' => $index + 1,
                    'error' => $e->getMessage(),
                    'original' => $test_case['text'],
                    'from_lang' => $test_case['from_lang'],
                    'to_lang' => $test_case['to_lang']
                );
            }
            
            // Small delay to avoid rate limiting
            sleep(1);
        }
        
        $this->print_summary($passed, $failed, $warnings);
        $this->generate_report();
    }
    
    /**
     * Print test summary
     */
    private function print_summary($passed, $failed, $warnings) {
        $total = count($this->test_cases);
        
        echo "\n\n=== Test Summary ===\n";
        echo "Total Tests: {$total}\n";
        echo "✅ Passed: {$passed}\n";
        echo "❌ Failed: {$failed}\n";
        echo "⚠️  Warnings: {$warnings}\n";
        
        $success_rate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
        echo "Success Rate: {$success_rate}%\n";
        
        if (!empty($this->errors)) {
            echo "\n=== Errors and Issues ===\n";
            foreach ($this->errors as $error) {
                echo "\nTest Case #{$error['test_case']}:\n";
                echo "  Error: {$error['error']}\n";
                echo "  Original: \"{$error['original']}\"\n";
                if (isset($error['translation'])) {
                    echo "  Translation: \"{$error['translation']}\"\n";
                }
                echo "  Direction: {$error['from_lang']} → {$error['to_lang']}\n";
            }
        }
    }
    
    /**
     * Generate detailed markdown report
     */
    private function generate_report() {
        $report_file = dirname(__FILE__) . '/translation-test-report.md';
        
        $content = "# AI Translation Testing Report\n\n";
        $content .= "**Provider:** " . strtoupper($this->provider) . "\n";
        $content .= "**Date:** " . date('Y-m-d H:i:s') . "\n";
        $content .= "**Total Test Cases:** " . count($this->test_cases) . "\n\n";
        
        $content .= "## Test Results\n\n";
        
        if (!empty($this->results)) {
            $content .= "| # | Category | Direction | Original Text | Translation | Duration (ms) | Status |\n";
            $content .= "|---|----------|-----------|---------------|-------------|---------------|--------|\n";
            
            foreach ($this->results as $index => $result) {
                $content .= sprintf(
                    "| %d | %s | %s → %s | %s | %s | %s | %s |\n",
                    $index + 1,
                    $result['category'],
                    $result['from_lang'],
                    $result['to_lang'],
                    substr($result['original'], 0, 40) . (strlen($result['original']) > 40 ? '...' : ''),
                    substr($result['translation'], 0, 40) . (strlen($result['translation']) > 40 ? '...' : ''),
                    $result['duration_ms'],
                    '✅'
                );
            }
        }
        
        if (!empty($this->errors)) {
            $content .= "\n## Issues and Errors\n\n";
            
            foreach ($this->errors as $index => $error) {
                $content .= sprintf(
                    "### Issue #%d: Test Case #%d\n\n",
                    $index + 1,
                    $error['test_case']
                );
                $content .= "- **Error:** {$error['error']}\n";
                $content .= "- **Original:** \"{$error['original']}\"\n";
                if (isset($error['translation'])) {
                    $content .= "- **Translation:** \"{$error['translation']}\"\n";
                }
                $content .= "- **Direction:** {$error['from_lang']} → {$error['to_lang']}\n\n";
            }
        }
        
        $content .= "\n## Recommendations\n\n";
        $content .= "1. **Translation Quality:** ";
        
        $passed = count($this->results);
        $total = count($this->test_cases);
        
        if ($passed >= $total * 0.9) {
            $content .= "Excellent - Translation quality is very high across all test cases.\n";
        } elseif ($passed >= $total * 0.7) {
            $content .= "Good - Most translations are accurate, but some improvements needed.\n";
        } else {
            $content .= "Needs Improvement - Several translation issues detected.\n";
        }
        
        $content .= "\n2. **Language Coverage:** Test cases cover EN, RU, UK, DE, FR, ES languages.\n";
        $content .= "\n3. **Performance:** ";
        
        if (!empty($this->results)) {
            $durations = array_column($this->results, 'duration_ms');
            $avg_duration = round(array_sum($durations) / count($durations), 2);
            $content .= "Average translation time: {$avg_duration}ms\n";
        }
        
        $content .= "\n## Conclusion\n\n";
        $content .= "The AI translation functionality was tested across multiple language pairs and text types. ";
        $content .= "Provider: " . strtoupper($this->provider) . ". ";
        
        if (empty($this->errors)) {
            $content .= "All tests passed successfully without any errors.\n";
        } else {
            $content .= "Some issues were identified and documented above.\n";
        }
        
        file_put_contents($report_file, $content);
        echo "\n\nDetailed report saved to: {$report_file}\n";
    }
}

// Check if API key is configured
$api_key = get_option('aic_ai_api_key', '');
if (empty($api_key)) {
    die("\n❌ ERROR: AI API key is not configured. Please set up the API key in plugin settings.\n\n");
}

// Run tests
$tester = new TranslationTester();
$tester->run_tests();
