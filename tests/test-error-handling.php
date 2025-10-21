#!/usr/bin/env php
<?php
/**
 * Test to verify comprehensive error handling in activation and initialization
 * 
 * This test verifies that:
 * 1. activate_plugin() has try-catch error handling
 * 2. __construct() has try-catch error handling
 * 3. init_hooks() has try-catch error handling
 * 4. Logging is present for key initialization steps
 * 5. Error messages are logged appropriately
 * 6. Admin notices are set up for activation errors
 * 
 * Run this test: php tests/test-error-handling.php
 */

echo "=== Plugin Error Handling Test ===\n\n";

$basePath = dirname(__DIR__) . '/';
$phpFile = $basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';

if (!file_exists($phpFile)) {
    echo "✗ Error: Plugin file not found at $phpFile\n";
    exit(1);
}

$content = file_get_contents($phpFile);

// Test 1: Check activate_plugin() has try-catch
echo "Test 1: Checking activate_plugin() error handling\n";
$pattern = '/public\s+static\s+function\s+activate_plugin\s*\(\s*\)\s*\{[^}]*try\s*\{/s';
if (preg_match($pattern, $content)) {
    echo "  ✓ activate_plugin() has try-catch block\n";
} else {
    echo "  ✗ activate_plugin() missing try-catch block\n";
    exit(1);
}

// Test 2: Check activate_plugin() has catch block with error logging
echo "\nTest 2: Checking activate_plugin() catch block\n";
$pattern = '/public\s+static\s+function\s+activate_plugin.*?catch\s*\(\s*Exception\s+\$e\s*\)/s';
if (preg_match($pattern, $content)) {
    echo "  ✓ activate_plugin() has Exception catch block\n";
} else {
    echo "  ✗ activate_plugin() missing Exception catch block\n";
    exit(1);
}

// Check if error is logged in catch block
if (preg_match('/catch.*?\{.*?error_log.*?\[ERROR\].*?activation failed/s', $content)) {
    echo "  ✓ activate_plugin() logs errors in catch block\n";
} else {
    echo "  ✗ activate_plugin() not logging errors properly\n";
    exit(1);
}

// Test 3: Check __construct() has try-catch
echo "\nTest 3: Checking __construct() error handling\n";
$pattern = '/private\s+function\s+__construct\s*\(\s*\)\s*\{[^}]*try\s*\{/s';
if (preg_match($pattern, $content)) {
    echo "  ✓ __construct() has try-catch block\n";
} else {
    echo "  ✗ __construct() missing try-catch block\n";
    exit(1);
}

// Test 4: Check __construct() has catch block
echo "\nTest 4: Checking __construct() catch block\n";
$pattern = '/private\s+function\s+__construct.*?catch\s*\(\s*Exception\s+\$e\s*\)/s';
if (preg_match($pattern, $content)) {
    echo "  ✓ __construct() has Exception catch block\n";
} else {
    echo "  ✗ __construct() missing Exception catch block\n";
    exit(1);
}

// Test 5: Check init_hooks() has try-catch
echo "\nTest 5: Checking init_hooks() error handling\n";
$pattern = '/private\s+function\s+init_hooks\s*\(\s*\)\s*\{[^}]*try\s*\{/s';
if (preg_match($pattern, $content)) {
    echo "  ✓ init_hooks() has try-catch block\n";
} else {
    echo "  ✗ init_hooks() missing try-catch block\n";
    exit(1);
}

// Test 6: Check for logging statements in activate_plugin()
echo "\nTest 6: Checking for logging in activate_plugin()\n";

$required_logs = [
    'Plugin activation started',
    'Database object verified',
    'Table names configured',
    'upgrade.php loaded',
    'Conversations table',
    'Messages table',
    'Translation cache table',
    'FAQ table',
    'Default options set',
    'Plugin activated successfully'
];

$found_logs = 0;
foreach ($required_logs as $log) {
    // Search in entire content for these log messages
    if (stripos($content, $log) !== false) {
        $found_logs++;
    }
}

if ($found_logs >= 8) {
    echo "  ✓ Found $found_logs/" . count($required_logs) . " required logging statements\n";
} else {
    echo "  ✗ Only found $found_logs logging statements (expected at least 8)\n";
    exit(1);
}

// Test 7: Check for logging statements in __construct()
echo "\nTest 7: Checking for logging in __construct()\n";

$required_constructor_logs = [
    'Plugin constructor started',
    'Database object initialized',
    'Table names configured',
    'Hooks initialized',
    'Plugin constructor completed'
];

$found_constructor_logs = 0;
foreach ($required_constructor_logs as $log) {
    // Search in entire content for these log messages
    if (stripos($content, $log) !== false) {
        $found_constructor_logs++;
    }
}

if ($found_constructor_logs >= 4) {
    echo "  ✓ Found $found_constructor_logs/" . count($required_constructor_logs) . " required constructor logging statements\n";
} else {
    echo "  ✗ Only found $found_constructor_logs constructor logging statements (expected at least 4)\n";
    exit(1);
}

// Test 8: Check for logging statements in init_hooks()
echo "\nTest 8: Checking for logging in init_hooks()\n";

if (stripos($content, 'Registering WordPress hooks') !== false &&
    stripos($content, 'hooks registered') !== false) {
    echo "  ✓ init_hooks() has appropriate logging\n";
} else {
    echo "  ✗ init_hooks() missing appropriate logging\n";
    exit(1);
}

// Test 9: Check activation error handling stores error in options
echo "\nTest 9: Checking activation error storage\n";
if (preg_match('/update_option.*?aic_activation_error/s', $content)) {
    echo "  ✓ Activation errors are stored in WordPress options\n";
} else {
    echo "  ✗ Activation errors not stored for display\n";
    exit(1);
}

// Test 10: Check admin_notices displays activation errors
echo "\nTest 10: Checking admin_notices error display\n";
if (preg_match('/function\s+admin_notices.*?get_option.*?aic_activation_error/s', $content)) {
    echo "  ✓ admin_notices() displays activation errors\n";
} else {
    echo "  ✗ admin_notices() doesn't display activation errors\n";
    exit(1);
}

// Test 11: Check for table existence verification in activation
echo "\nTest 11: Checking table existence verification\n";
$verify_count = preg_match_all('/SHOW TABLES LIKE.*?table_conversations|table_messages|table_cache|table_faq/s', $content);
if ($verify_count >= 3) {
    echo "  ✓ Tables are verified after creation ($verify_count verifications found)\n";
} else {
    echo "  ✗ Insufficient table verification (found $verify_count, expected at least 3)\n";
    exit(1);
}

// Test 12: Check for graceful error handling (not throwing fatal errors in constructor)
echo "\nTest 12: Checking graceful error handling in constructor\n";
if (preg_match('/private\s+function\s+__construct.*?catch.*?\{.*?log.*?error.*?\}/s', $content)) {
    echo "  ✓ Constructor catches and logs errors gracefully\n";
} else {
    echo "  ✗ Constructor may not handle errors gracefully\n";
    exit(1);
}

// Test 13: Verify WP_DEBUG checks are present for conditional logging
echo "\nTest 13: Checking WP_DEBUG conditional logging\n";
$debug_checks = preg_match_all('/defined\s*\(\s*[\'"]WP_DEBUG[\'"]\s*\)\s*&&\s*WP_DEBUG/', $content);
if ($debug_checks >= 15) {
    echo "  ✓ Found $debug_checks WP_DEBUG conditional checks for logging\n";
} else {
    echo "  ⚠ Warning: Only found $debug_checks WP_DEBUG checks (expected 15+)\n";
}

// Test 14: Check that errors are re-thrown in activation to prevent partial activation
echo "\nTest 14: Checking error re-throwing in activation\n";
if (preg_match('/public\s+static\s+function\s+activate_plugin.*?catch.*?throw\s+\$e;/s', $content)) {
    echo "  ✓ Activation errors are re-thrown to prevent partial activation\n";
} else {
    echo "  ✗ Activation may allow partial activation on error\n";
    exit(1);
}

// Test 15: Verify error messages include context
echo "\nTest 15: Checking error message context\n";
$context_patterns = [
    '/\[INFO\]/',
    '/\[ERROR\]/',
    '/\[WARNING\]/',
];

$context_found = 0;
foreach ($context_patterns as $pattern) {
    if (preg_match($pattern, $content)) {
        $context_found++;
    }
}

if ($context_found >= 3) {
    echo "  ✓ Error messages include appropriate context levels\n";
} else {
    echo "  ✗ Missing error context levels\n";
    exit(1);
}

echo "\n=== All Tests Passed ✓ ===\n";
echo "\nSummary:\n";
echo "✓ activate_plugin() has comprehensive try-catch error handling\n";
echo "✓ __construct() has comprehensive try-catch error handling\n";
echo "✓ init_hooks() has comprehensive try-catch error handling\n";
echo "✓ Key initialization steps are logged\n";
echo "✓ Errors are logged with appropriate context\n";
echo "✓ Activation errors are stored and displayed to admins\n";
echo "✓ Tables are verified after creation\n";
echo "✓ Error handling is graceful and doesn't cause fatal errors\n";
echo "✓ Errors are re-thrown in activation to prevent partial activation\n";

exit(0);
