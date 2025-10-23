#!/usr/bin/env php
<?php
/**
 * Test to verify FAQ AJAX add/delete operations functionality
 * Run this from repository root: php tests/test-faq-ajax-operations.php
 */

echo "=== FAQ AJAX Operations Test ===\n\n";

$basePath = dirname(__DIR__) . '/ai-multilingual-chat/';
$passed = 0;
$failed = 0;

// Test 1: Check if ajax_add_faq method exists in main plugin file
echo "Test 1: Check if ajax_add_faq method exists in main plugin file\n";
$mainFile = $basePath . 'ai-multilingual-chat.php';
if (!file_exists($mainFile)) {
    echo "  ✗ Main plugin file not found\n";
    $failed++;
} else {
    $content = file_get_contents($mainFile);
    if (strpos($content, 'public function ajax_add_faq()') !== false) {
        echo "  ✓ ajax_add_faq method found\n";
        $passed++;
    } else {
        echo "  ✗ ajax_add_faq method not found\n";
        $failed++;
    }
}

// Test 2: Check if ajax_delete_faq method exists
echo "\nTest 2: Check if ajax_delete_faq method exists\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (strpos($content, 'public function ajax_delete_faq()') !== false) {
        echo "  ✓ ajax_delete_faq method found\n";
        $passed++;
    } else {
        echo "  ✗ ajax_delete_faq method not found\n";
        $failed++;
    }
}

// Test 3: Check if AJAX action for add_faq is registered
echo "\nTest 3: Check if AJAX action 'aic_add_faq' is registered\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (strpos($content, "add_action('wp_ajax_aic_add_faq'") !== false) {
        echo "  ✓ AJAX action 'aic_add_faq' is registered\n";
        $passed++;
    } else {
        echo "  ✗ AJAX action 'aic_add_faq' not registered\n";
        $failed++;
    }
}

// Test 4: Check if AJAX action for delete_faq is registered
echo "\nTest 4: Check if AJAX action 'aic_delete_faq' is registered\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (strpos($content, "add_action('wp_ajax_aic_delete_faq'") !== false) {
        echo "  ✓ AJAX action 'aic_delete_faq' is registered\n";
        $passed++;
    } else {
        echo "  ✗ AJAX action 'aic_delete_faq' not registered\n";
        $failed++;
    }
}

// Test 5: Check if FAQ template has form ID for AJAX
echo "\nTest 5: Check if FAQ template has form ID for AJAX\n";
$faqFile = $basePath . 'templates/faq.php';
if (!file_exists($faqFile)) {
    echo "  ✗ FAQ template file not found\n";
    $failed++;
} else {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'id="aic-add-faq-form"') !== false) {
        echo "  ✓ FAQ form has ID for AJAX\n";
        $passed++;
    } else {
        echo "  ✗ FAQ form doesn't have ID for AJAX\n";
        $failed++;
    }
}

// Test 6: Check if FAQ template has AJAX add handler in JavaScript
echo "\nTest 6: Check if FAQ template has AJAX add handler in JavaScript\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, "action: 'aic_add_faq'") !== false) {
        echo "  ✓ AJAX add handler found in FAQ template\n";
        $passed++;
    } else {
        echo "  ✗ AJAX add handler not found in FAQ template\n";
        $failed++;
    }
}

// Test 7: Check if FAQ template has AJAX delete handler in JavaScript
echo "\nTest 7: Check if FAQ template has AJAX delete handler in JavaScript\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, "action: 'aic_delete_faq'") !== false) {
        echo "  ✓ AJAX delete handler found in FAQ template\n";
        $passed++;
    } else {
        echo "  ✗ AJAX delete handler not found in FAQ template\n";
        $failed++;
    }
}

// Test 8: Check if delete button is now a button (not a form submit)
echo "\nTest 8: Check if delete button uses AJAX (not form submission)\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'class="aic-btn primary aic-faq-delete"') !== false) {
        echo "  ✓ Delete button uses AJAX class\n";
        $passed++;
    } else {
        echo "  ✗ Delete button doesn't use AJAX class\n";
        $failed++;
    }
}

// Test 9: Check if FAQ template table body has ID for dynamic updates
echo "\nTest 9: Check if FAQ template table body has ID for dynamic updates\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'id="aic-faq-list"') !== false) {
        echo "  ✓ FAQ list table body has ID\n";
        $passed++;
    } else {
        echo "  ✗ FAQ list table body doesn't have ID\n";
        $failed++;
    }
}

// Test 10: Check if ajax_add_faq has proper security checks
echo "\nTest 10: Check if ajax_add_faq has proper security checks\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    // Look for nonce check in ajax_add_faq
    preg_match('/public function ajax_add_faq\(\).*?(?=public function|\z)/s', $content, $matches);
    if (!empty($matches[0])) {
        $methodContent = $matches[0];
        if (strpos($methodContent, 'check_ajax_referer') !== false && 
            strpos($methodContent, 'current_user_can') !== false) {
            echo "  ✓ ajax_add_faq has security checks (nonce and capability)\n";
            $passed++;
        } else {
            echo "  ✗ ajax_add_faq missing security checks\n";
            $failed++;
        }
    } else {
        echo "  ✗ Could not extract ajax_add_faq method\n";
        $failed++;
    }
}

// Test 11: Check if ajax_delete_faq has proper security checks
echo "\nTest 11: Check if ajax_delete_faq has proper security checks\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    // Look for nonce check in ajax_delete_faq
    preg_match('/public function ajax_delete_faq\(\).*?(?=public function|\z)/s', $content, $matches);
    if (!empty($matches[0])) {
        $methodContent = $matches[0];
        if (strpos($methodContent, 'check_ajax_referer') !== false && 
            strpos($methodContent, 'current_user_can') !== false) {
            echo "  ✓ ajax_delete_faq has security checks (nonce and capability)\n";
            $passed++;
        } else {
            echo "  ✗ ajax_delete_faq missing security checks\n";
            $failed++;
        }
    } else {
        echo "  ✗ Could not extract ajax_delete_faq method\n";
        $failed++;
    }
}

// Test 12: Check if POST handler comments indicate AJAX is primary method
echo "\nTest 12: Check if POST handler indicates AJAX is now primary\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'Add and Delete are now handled via AJAX') !== false || 
        strpos($content, 'backward compatibility') !== false) {
        echo "  ✓ POST handler indicates AJAX is primary method\n";
        $passed++;
    } else {
        echo "  ✗ POST handler doesn't indicate AJAX transition\n";
        $failed++;
    }
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "Tests passed: $passed\n";
echo "Tests failed: $failed\n";
echo str_repeat("=", 50) . "\n";

if ($failed === 0) {
    echo "✓ All tests passed!\n";
    exit(0);
} else {
    echo "✗ Some tests failed.\n";
    exit(1);
}
