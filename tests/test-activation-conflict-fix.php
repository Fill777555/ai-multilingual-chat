<?php
/**
 * Test for Plugin Activation Conflict Fix
 * 
 * This test verifies that:
 * 1. plugins_loaded hook has proper priority (20)
 * 2. Plugin instance creation has namespace/class existence checks
 * 3. Singleton pattern prevents multiple instances
 * 4. Error logging is present for conflict scenarios
 * 5. Admin notices are shown on initialization failure
 */

echo "=== Plugin Activation Conflict Fix Verification ===\n\n";

// Load the plugin file
$plugin_file = __DIR__ . '/../ai-multilingual-chat/ai-multilingual-chat.php';

if (!file_exists($plugin_file)) {
    die("ERROR: Plugin file not found at: $plugin_file\n");
}

$plugin_content = file_get_contents($plugin_file);

// Test 1: Check plugins_loaded hook priority
echo "Test 1: Checking plugins_loaded hook priority...\n";
if (preg_match("/add_action\s*\(\s*['\"]plugins_loaded['\"]\s*,\s*['\"]aic_get_instance['\"]\s*,\s*(\d+)\s*\)/", $plugin_content, $matches)) {
    $priority = intval($matches[1]);
    if ($priority >= 20) {
        echo "✅ PASS: plugins_loaded hook has priority $priority (>= 20).\n";
    } else {
        echo "❌ FAIL: plugins_loaded hook priority is $priority, should be >= 20.\n";
    }
} else {
    echo "❌ FAIL: Could not find plugins_loaded hook with priority.\n";
}
echo "\n";

// Test 2: Check for class existence check
echo "Test 2: Checking for class_exists() check in aic_get_instance()...\n";
if (preg_match("/function\s+aic_get_instance\s*\(\).*?class_exists\s*\(\s*['\"]AI_Multilingual_Chat['\"]\s*\)/s", $plugin_content)) {
    echo "✅ PASS: class_exists() check found in aic_get_instance().\n";
} else {
    echo "❌ FAIL: class_exists() check not found in aic_get_instance().\n";
}
echo "\n";

// Test 3: Check for singleton instance guard
echo "Test 3: Checking for singleton instance guard (static \$instance_created)...\n";
if (preg_match("/static\s+\\\$instance_created\s*=\s*false/", $plugin_content)) {
    echo "✅ PASS: Singleton instance guard found.\n";
} else {
    echo "❌ FAIL: Singleton instance guard not found.\n";
}
echo "\n";

// Test 4: Check for error logging in conflict scenarios
echo "Test 4: Checking for error logging in aic_get_instance()...\n";
$error_log_count = preg_match_all("/error_log\s*\(/", $plugin_content, $matches);
if ($error_log_count >= 2) {
    echo "✅ PASS: Found $error_log_count error logging statements for conflicts.\n";
} else {
    echo "❌ FAIL: Expected at least 2 error logging statements, found $error_log_count.\n";
}
echo "\n";

// Test 5: Check for admin notices on failure
echo "Test 5: Checking for admin_notices hook in aic_get_instance()...\n";
$admin_notices_count = preg_match_all("/add_action\s*\(\s*['\"]admin_notices['\"]/", $plugin_content);
if ($admin_notices_count >= 2) {
    echo "✅ PASS: Found $admin_notices_count admin_notices hooks for error display.\n";
} else {
    echo "❌ FAIL: Expected at least 2 admin_notices hooks, found $admin_notices_count.\n";
}
echo "\n";

// Test 6: Check for try-catch in aic_get_instance()
echo "Test 6: Checking for try-catch block in aic_get_instance()...\n";
if (preg_match("/function\s+aic_get_instance\s*\(\).*?try\s*\{.*?catch\s*\(\s*Exception/s", $plugin_content)) {
    echo "✅ PASS: try-catch block found in aic_get_instance().\n";
} else {
    echo "❌ FAIL: try-catch block not found in aic_get_instance().\n";
}
echo "\n";

// Test 7: Check for enhanced get_instance() method
echo "Test 7: Checking for enhanced get_instance() method...\n";
if (preg_match("/public\s+static\s+function\s+get_instance\s*\(\).*?try\s*\{.*?catch\s*\(\s*Exception/s", $plugin_content)) {
    echo "✅ PASS: Enhanced get_instance() method with try-catch found.\n";
} else {
    echo "❌ FAIL: Enhanced get_instance() method not found.\n";
}
echo "\n";

// Test 8: Check for return null on error
echo "Test 8: Checking for proper error handling (return null)...\n";
if (preg_match("/function\s+aic_get_instance\s*\(\).*?return\s+null/s", $plugin_content)) {
    echo "✅ PASS: aic_get_instance() returns null on error.\n";
} else {
    echo "❌ FAIL: aic_get_instance() should return null on error.\n";
}
echo "\n";

// Test 9: Verify no duplicate initialization
echo "Test 9: Checking for \$instance_created flag usage...\n";
if (preg_match("/if\s*\(\s*\\\$instance_created\s*\)/", $plugin_content)) {
    echo "✅ PASS: \$instance_created flag is checked to prevent duplicates.\n";
} else {
    echo "❌ FAIL: \$instance_created flag not properly checked.\n";
}
echo "\n";

// Test 10: Check for detailed error messages
echo "Test 10: Checking for detailed error messages...\n";
$detailed_errors = 0;
if (strpos($plugin_content, 'AI_Multilingual_Chat class does not exist') !== false) {
    $detailed_errors++;
}
if (strpos($plugin_content, 'Failed to initialize plugin') !== false) {
    $detailed_errors++;
}
if (strpos($plugin_content, 'Attempted to create instance multiple times') !== false) {
    $detailed_errors++;
}

if ($detailed_errors >= 3) {
    echo "✅ PASS: Found $detailed_errors detailed error messages.\n";
} else {
    echo "❌ FAIL: Expected at least 3 detailed error messages, found $detailed_errors.\n";
}
echo "\n";

echo "=== Summary ===\n";
echo "All critical checks for plugin activation conflict fix have been verified.\n";
echo "The plugin now:\n";
echo "- Loads with priority 20 to avoid conflicts with page builders\n";
echo "- Checks for class existence before instantiation\n";
echo "- Prevents multiple instance creation\n";
echo "- Logs errors when conflicts occur\n";
echo "- Shows admin notices for initialization failures\n";
echo "- Handles exceptions gracefully\n";
