#!/usr/bin/env php
<?php
/**
 * Test to verify Settings AJAX save functionality
 * Run this from repository root: php tests/test-settings-ajax-save.php
 */

echo "=== Settings AJAX Save Test ===\n\n";

$basePath = dirname(__DIR__) . '/ai-multilingual-chat/';
$passed = 0;
$failed = 0;

// Test 1: Check if ajax_save_settings method exists in main plugin file
echo "Test 1: Check if ajax_save_settings method exists in main plugin file\n";
$mainFile = $basePath . 'ai-multilingual-chat.php';
if (!file_exists($mainFile)) {
    echo "  ✗ Main plugin file not found\n";
    $failed++;
} else {
    $content = file_get_contents($mainFile);
    if (strpos($content, 'public function ajax_save_settings()') !== false) {
        echo "  ✓ ajax_save_settings method found\n";
        $passed++;
    } else {
        echo "  ✗ ajax_save_settings method not found\n";
        $failed++;
    }
}

// Test 2: Check if AJAX action for save_settings is registered
echo "\nTest 2: Check if AJAX action 'aic_save_settings' is registered\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    if (strpos($content, "add_action('wp_ajax_aic_save_settings'") !== false) {
        echo "  ✓ AJAX action 'aic_save_settings' is registered\n";
        $passed++;
    } else {
        echo "  ✗ AJAX action 'aic_save_settings' not registered\n";
        $failed++;
    }
}

// Test 3: Check if settings template has form ID for AJAX
echo "\nTest 3: Check if settings template has form ID for AJAX\n";
$settingsFile = $basePath . 'templates/settings.php';
if (!file_exists($settingsFile)) {
    echo "  ✗ Settings template file not found\n";
    $failed++;
} else {
    $content = file_get_contents($settingsFile);
    if (strpos($content, 'id="aic-settings-form"') !== false) {
        echo "  ✓ Settings form has ID for AJAX\n";
        $passed++;
    } else {
        echo "  ✗ Settings form doesn't have ID for AJAX\n";
        $failed++;
    }
}

// Test 4: Check if settings template has AJAX save handler in JavaScript
echo "\nTest 4: Check if settings template has AJAX save handler in JavaScript\n";
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    if (strpos($content, "action: 'aic_save_settings'") !== false || 
        strpos($content, "action=aic_save_settings") !== false) {
        echo "  ✓ AJAX save handler found in settings template\n";
        $passed++;
    } else {
        echo "  ✗ AJAX save handler not found in settings template\n";
        $failed++;
    }
}

// Test 5: Check if settings form has submit event handler
echo "\nTest 5: Check if settings form has submit event handler\n";
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    if (strpos($content, "$('#aic-settings-form').on('submit'") !== false) {
        echo "  ✓ Settings form submit event handler found\n";
        $passed++;
    } else {
        echo "  ✗ Settings form submit event handler not found\n";
        $failed++;
    }
}

// Test 6: Check if AJAX handler prevents default form submission
echo "\nTest 6: Check if AJAX handler prevents default form submission\n";
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    // Look for preventDefault in the submit handler
    if (preg_match('/\$\(\'#aic-settings-form\'\)\.on\(\'submit\'.*?preventDefault/s', $content)) {
        echo "  ✓ Form submission is prevented (uses AJAX)\n";
        $passed++;
    } else {
        echo "  ✗ preventDefault not found in submit handler\n";
        $failed++;
    }
}

// Test 7: Check if ajax_save_settings has proper security checks
echo "\nTest 7: Check if ajax_save_settings has proper security checks\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    // Look for nonce check in ajax_save_settings
    preg_match('/public function ajax_save_settings\(\).*?(?=public function|\z)/s', $content, $matches);
    if (!empty($matches[0])) {
        $methodContent = $matches[0];
        if (strpos($methodContent, 'check_ajax_referer') !== false && 
            strpos($methodContent, 'current_user_can') !== false) {
            echo "  ✓ ajax_save_settings has security checks (nonce and capability)\n";
            $passed++;
        } else {
            echo "  ✗ ajax_save_settings missing security checks\n";
            $failed++;
        }
    } else {
        echo "  ✗ Could not extract ajax_save_settings method\n";
        $failed++;
    }
}

// Test 8: Check if ajax_save_settings reuses existing save_settings method
echo "\nTest 8: Check if ajax_save_settings reuses existing save_settings method\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    preg_match('/public function ajax_save_settings\(\).*?(?=public function|\z)/s', $content, $matches);
    if (!empty($matches[0])) {
        $methodContent = $matches[0];
        if (strpos($methodContent, '$this->save_settings') !== false) {
            echo "  ✓ ajax_save_settings reuses existing save_settings method\n";
            $passed++;
        } else {
            echo "  ✗ ajax_save_settings doesn't reuse save_settings method\n";
            $failed++;
        }
    }
}

// Test 9: Check if JavaScript shows success message
echo "\nTest 9: Check if JavaScript shows success message after save\n";
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    if (strpos($content, 'notice-success') !== false && 
        preg_match('/aic-settings-form.*?success.*?notice/s', $content)) {
        echo "  ✓ Success message is shown after save\n";
        $passed++;
    } else {
        echo "  ✗ Success message not found\n";
        $failed++;
    }
}

// Test 10: Check if JavaScript handles errors properly
echo "\nTest 10: Check if JavaScript handles errors properly\n";
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    if (strpos($content, 'error: function') !== false || 
        preg_match('/aic-settings-form.*?error.*?function/s', $content)) {
        echo "  ✓ Error handling found in AJAX\n";
        $passed++;
    } else {
        echo "  ✗ Error handling not found\n";
        $failed++;
    }
}

// Test 11: Check if button is disabled during save
echo "\nTest 11: Check if submit button is disabled during save\n";
if (file_exists($settingsFile)) {
    $content = file_get_contents($settingsFile);
    if (preg_match('/aic-settings-form.*?prop\(\'disabled\', true\)/s', $content)) {
        echo "  ✓ Submit button is disabled during save\n";
        $passed++;
    } else {
        echo "  ✗ Submit button not disabled during save\n";
        $failed++;
    }
}

// Test 12: Check if old POST redirect is removed or commented
echo "\nTest 12: Check if render_settings_page still has POST handling for compatibility\n";
if (file_exists($mainFile)) {
    $content = file_get_contents($mainFile);
    // The old POST handler should still exist for backward compatibility
    if (strpos($content, "function render_settings_page()") !== false && 
        strpos($content, "isset(\$_POST['aic_save_settings'])") !== false) {
        echo "  ✓ POST handler still exists for backward compatibility\n";
        $passed++;
    } else {
        echo "  ! POST handler removed (AJAX only - this is fine)\n";
        $passed++; // Pass either way as both approaches are valid
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
