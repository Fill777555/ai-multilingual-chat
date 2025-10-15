#!/usr/bin/env php
<?php
/**
 * Simple Translation Test Runner
 * Demonstrates the test scenarios without requiring actual API calls
 * 
 * Usage: php tests/test-runner-demo.php
 */

class TranslationTestDemo {
    
    private $test_scenarios = array();
    
    public function __construct() {
        $this->init_scenarios();
    }
    
    private function init_scenarios() {
        $this->test_scenarios = array(
            array(
                'id' => 1,
                'category' => 'Simple greeting',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'Hello, how can I help you today?',
                'expected' => 'Привет, чем я могу помочь вам сегодня?',
                'keywords' => array('Привет', 'помочь', 'сегодня')
            ),
            array(
                'id' => 2,
                'category' => 'E-commerce',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'I would like to order a product from your website.',
                'expected' => 'Я хотел бы заказать продукт с вашего сайта.',
                'keywords' => array('заказать', 'продукт', 'сайт')
            ),
            array(
                'id' => 3,
                'category' => 'Customer support',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'What is your return policy for defective items?',
                'expected' => 'Какова ваша политика возврата для дефектных товаров?',
                'keywords' => array('возврат', 'политика', 'дефект')
            ),
            array(
                'id' => 4,
                'category' => 'Simple greeting',
                'from' => 'ru',
                'to' => 'en',
                'original' => 'Здравствуйте! Я хочу оформить заказ.',
                'expected' => 'Hello! I want to place an order.',
                'keywords' => array('Hello', 'order', 'place')
            ),
            array(
                'id' => 5,
                'category' => 'Technical support',
                'from' => 'ru',
                'to' => 'en',
                'original' => 'Мне нужна техническая поддержка с установкой плагина.',
                'expected' => 'I need technical support with plugin installation.',
                'keywords' => array('technical', 'support', 'plugin', 'install')
            ),
            array(
                'id' => 6,
                'category' => 'Gratitude',
                'from' => 'en',
                'to' => 'uk',
                'original' => 'Thank you for your assistance!',
                'expected' => 'Дякую за вашу допомогу!',
                'keywords' => array('Дякую', 'допомог')
            ),
            array(
                'id' => 7,
                'category' => 'Delivery inquiry',
                'from' => 'ru',
                'to' => 'uk',
                'original' => 'Когда я могу ожидать доставку?',
                'expected' => 'Коли я можу очікувати доставку?',
                'keywords' => array('Коли', 'очікувати', 'доставк')
            ),
            array(
                'id' => 8,
                'category' => 'Account support',
                'from' => 'en',
                'to' => 'de',
                'original' => 'I need help with my account.',
                'expected' => 'Ich brauche Hilfe mit meinem Konto.',
                'keywords' => array('Hilfe', 'Konto')
            ),
            array(
                'id' => 9,
                'category' => 'Information request',
                'from' => 'en',
                'to' => 'fr',
                'original' => 'Where can I find more information?',
                'expected' => 'Où puis-je trouver plus d\'informations?',
                'keywords' => array('où', 'trouver', 'information')
            ),
            array(
                'id' => 10,
                'category' => 'Pricing inquiry',
                'from' => 'en',
                'to' => 'es',
                'original' => 'I have a question about pricing.',
                'expected' => 'Tengo una pregunta sobre los precios.',
                'keywords' => array('pregunta', 'precio')
            ),
            array(
                'id' => 11,
                'category' => 'Complex inquiry',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'Could you please provide more details about the shipping options available for international orders?',
                'expected' => 'Не могли бы вы предоставить больше подробностей о вариантах доставки для международных заказов?',
                'keywords' => array('подробност', 'доставк', 'международн', 'заказ')
            ),
            array(
                'id' => 12,
                'category' => 'Technical terminology',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'The API endpoint is not responding correctly.',
                'expected' => 'Конечная точка API не отвечает корректно.',
                'keywords' => array('API', 'конечн', 'отвеча')
            ),
            array(
                'id' => 13,
                'category' => 'Emotional expression',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'Really?! That\'s amazing! :)',
                'expected' => 'Правда?! Это удивительно! :)',
                'keywords' => array('Правда', 'Это', 'удивительн')
            ),
            array(
                'id' => 14,
                'category' => 'Numbers and measurements',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'The package weighs 2.5 kg and costs $49.99.',
                'expected' => 'Посылка весит 2.5 кг и стоит $49.99.',
                'keywords' => array('2.5', 'кг', '49.99')
            ),
            array(
                'id' => 15,
                'category' => 'Multi-sentence complaint',
                'from' => 'en',
                'to' => 'ru',
                'original' => 'Good morning. I received my order yesterday. However, one item is missing. Can you help me?',
                'expected' => 'Доброе утро. Я получил свой заказ вчера. Однако одного товара не хватает. Можете ли вы мне помочь?',
                'keywords' => array('Доброе утро', 'получил', 'вчера', 'отсутствует', 'помочь')
            )
        );
    }
    
    public function display_test_scenarios() {
        echo "\n╔════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║          AI Multilingual Chat - Translation Test Scenarios               ║\n";
        echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Total test scenarios: " . count($this->test_scenarios) . "\n\n";
        
        foreach ($this->test_scenarios as $scenario) {
            $this->display_scenario($scenario);
        }
        
        $this->display_summary();
    }
    
    private function display_scenario($scenario) {
        echo "─────────────────────────────────────────────────────────────────────────────\n";
        echo "Test #{$scenario['id']}: {$scenario['category']}\n";
        echo "─────────────────────────────────────────────────────────────────────────────\n";
        echo "Direction:        {$scenario['from']} → {$scenario['to']}\n";
        echo "Original text:    \"{$scenario['original']}\"\n";
        echo "Expected result:  \"{$scenario['expected']}\"\n";
        echo "Keywords to check: " . implode(', ', $scenario['keywords']) . "\n";
        echo "\n";
    }
    
    private function display_summary() {
        echo "═════════════════════════════════════════════════════════════════════════════\n";
        echo "Test Coverage Summary\n";
        echo "═════════════════════════════════════════════════════════════════════════════\n\n";
        
        // Language pairs
        $pairs = array();
        foreach ($this->test_scenarios as $scenario) {
            $pair = $scenario['from'] . ' → ' . $scenario['to'];
            if (!isset($pairs[$pair])) {
                $pairs[$pair] = 0;
            }
            $pairs[$pair]++;
        }
        
        echo "Language Pairs Tested:\n";
        foreach ($pairs as $pair => $count) {
            echo "  • {$pair}: {$count} test(s)\n";
        }
        
        // Categories
        $categories = array();
        foreach ($this->test_scenarios as $scenario) {
            if (!isset($categories[$scenario['category']])) {
                $categories[$scenario['category']] = 0;
            }
            $categories[$scenario['category']]++;
        }
        
        echo "\nCategories Covered:\n";
        foreach ($categories as $category => $count) {
            echo "  • {$category}: {$count} test(s)\n";
        }
        
        echo "\n";
        echo "═════════════════════════════════════════════════════════════════════════════\n";
        echo "To run actual tests with API calls, use:\n";
        echo "  php tests/test-translation.php\n";
        echo "═════════════════════════════════════════════════════════════════════════════\n\n";
    }
    
    public function generate_test_matrix() {
        $filename = dirname(__FILE__) . '/test-matrix.csv';
        $fp = fopen($filename, 'w');
        
        // Header
        fputcsv($fp, array('ID', 'Category', 'From', 'To', 'Original', 'Expected', 'Keywords'));
        
        // Data
        foreach ($this->test_scenarios as $scenario) {
            fputcsv($fp, array(
                $scenario['id'],
                $scenario['category'],
                $scenario['from'],
                $scenario['to'],
                $scenario['original'],
                $scenario['expected'],
                implode('; ', $scenario['keywords'])
            ));
        }
        
        fclose($fp);
        echo "Test matrix exported to: {$filename}\n";
    }
}

// Run demo
$demo = new TranslationTestDemo();
$demo->display_test_scenarios();
$demo->generate_test_matrix();
