#!/usr/bin/env php
<?php
/**
 * Test to verify FAQ AJAX toggle functionality
 * Run this from repository root: php tests/test-faq-ajax-toggle.php
 */

echo "=== FAQ AJAX Toggle Test ===\n\n";

$basePath = dirname(__DIR__) . '/ai-multilingual-chat/';
$passed = 0;
$failed = 0;

// Test 1: Check if main plugin file has ajax_toggle_faq method
echo "Test 1: Check if ajax_toggle_faq method exists in main plugin file\n";
$mainFile = $basePath . 'ai-multilingual-chat.php';
if (!file_exists($mainFile)) {
    echo "  ✗ Main plugin file not found\n";
    $failed++;
} else {
    $content = file_get_contents($mainFile);
    if (strpos($content, 'public function ajax_toggle_faq()') !== false) {
        echo "  ✓ ajax_toggle_faq method found\n";
        $passed++;
    } else {
        echo "  ✗ ajax_toggle_faq method not found\n";
        $failed++;
    }
}

// Test 2: Check if AJAX action is registered
echo "\nTest 2: Check if AJAX action is registered\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (strpos($content, "add_action('wp_ajax_aic_toggle_faq'") !== false) {
        echo "  ✓ AJAX action 'aic_toggle_faq' is registered\n";
        $passed++;
    } else {
        echo "  ✗ AJAX action 'aic_toggle_faq' not registered\n";
        $failed++;
    }
}

// Test 3: Check if FAQ template has AJAX toggle button
echo "\nTest 3: Check if FAQ template has AJAX toggle button\n";
$faqFile = $basePath . 'templates/faq.php';
if (!file_exists($faqFile)) {
    echo "  ✗ FAQ template file not found\n";
    $failed++;
} else {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'aic-faq-toggle') !== false) {
        echo "  ✓ AJAX toggle button class found\n";
        $passed++;
    } else {
        echo "  ✗ AJAX toggle button class not found\n";
        $failed++;
    }
}

// Test 4: Check if JavaScript AJAX handler exists in FAQ template
echo "\nTest 4: Check if JavaScript AJAX handler exists\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, "action: 'aic_toggle_faq'") !== false) {
        echo "  ✓ JavaScript AJAX handler found\n";
        $passed++;
    } else {
        echo "  ✗ JavaScript AJAX handler not found\n";
        $failed++;
    }
}

// Test 5: Check if form submission is replaced with button
echo "\nTest 5: Check if form submission is replaced with button\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    // Check that toggle button is type="button" not type="submit"
    if (preg_match('/type="button".*aic-faq-toggle/s', $content)) {
        echo "  ✓ Toggle button uses type='button' (not form submission)\n";
        $passed++;
    } else {
        echo "  ✗ Toggle button may still use form submission\n";
        $failed++;
    }
}

// Test 6: Verify security - nonce check in AJAX handler
echo "\nTest 6: Verify security - nonce check in AJAX handler\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (preg_match('/function ajax_toggle_faq\(\).*check_ajax_referer.*aic_admin_nonce/s', $content)) {
        echo "  ✓ Nonce check is present in AJAX handler\n";
        $passed++;
    } else {
        echo "  ✗ Nonce check not found in AJAX handler\n";
        $failed++;
    }
}

// Test 7: Verify permission check in AJAX handler
echo "\nTest 7: Verify permission check in AJAX handler\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (preg_match('/function ajax_toggle_faq\(\).*current_user_can.*manage_options/s', $content)) {
        echo "  ✓ Permission check is present in AJAX handler\n";
        $passed++;
    } else {
        echo "  ✗ Permission check not found in AJAX handler\n";
        $failed++;
    }
}

// Test 8: Check if UI updates without page reload
echo "\nTest 8: Check if JavaScript updates UI without page reload\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, '$button.data(\'is-active\', newState)') !== false &&
        strpos($content, '$statusCell.html') !== false) {
        echo "  ✓ JavaScript updates button state and status cell\n";
        $passed++;
    } else {
        echo "  ✗ JavaScript UI update logic not found\n";
        $failed++;
    }
}

// Test 9: Check if success message is shown
echo "\nTest 9: Check if success message is shown\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'notice notice-success') !== false &&
        strpos($content, 'Статус FAQ успешно обновлён') !== false) {
        echo "  ✓ Success message display found\n";
        $passed++;
    } else {
        echo "  ✗ Success message display not found\n";
        $failed++;
    }
}

// Test 10: Check if error handling is implemented
echo "\nTest 10: Check if error handling is implemented\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    if (strpos($content, 'error: function(xhr, status, error)') !== false) {
        echo "  ✓ Error handling is implemented\n";
        $passed++;
    } else {
        echo "  ✗ Error handling not found\n";
        $failed++;
    }
}

// Summary
echo "\n=== Test Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed!\n";
    exit(1);
}
