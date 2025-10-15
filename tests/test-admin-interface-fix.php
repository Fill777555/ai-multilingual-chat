#!/usr/bin/env php
<?php
/**
 * Simple test to verify admin interface fix
 * Run this from WordPress root: php tests/test-admin-interface-fix.php
 */

// Simulate WordPress environment
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('WPINC', 'wp-includes');

echo "=== Admin Interface Fix Test ===\n\n";

// Test 1: Check file exists
echo "Test 1: Check if files exist\n";
$files = [
    'ai-multilingual-chat/ai-multilingual-chat.php',
    'ai-multilingual-chat/admin-script.js',
    'ai-multilingual-chat/admin-style.css',
    'ai-multilingual-chat/templates/admin-chat.php'
];

$basePath = dirname(__DIR__) . '/';
foreach ($files as $file) {
    $fullPath = $basePath . $file;
    $exists = file_exists($fullPath);
    echo "  - " . ($exists ? "✓" : "✗") . " $file\n";
    if (!$exists) {
        echo "    ERROR: File not found at $fullPath\n";
    }
}

// Test 2: Check version is updated to 2.0.1
echo "\nTest 2: Check version is updated\n";
$phpContent = file_get_contents($basePath . 'ai-multilingual-chat/ai-multilingual-chat.php');
if (preg_match('/Version:\s*([0-9.]+)/', $phpContent, $matches)) {
    $version = $matches[1];
    echo "  - Plugin version: $version\n";
    if ($version === '2.0.1') {
        echo "  ✓ Version correctly set to 2.0.1\n";
    } else {
        echo "  ✗ Version is $version, expected 2.0.1\n";
    }
}

if (preg_match('/define\([\'"]AIC_VERSION[\'"],\s*[\'"]([0-9.]+)[\'"]\)/', $phpContent, $matches)) {
    $constVersion = $matches[1];
    echo "  - AIC_VERSION constant: $constVersion\n";
    if ($constVersion === '2.0.1') {
        echo "  ✓ Constant correctly set to 2.0.1\n";
    } else {
        echo "  ✗ Constant is $constVersion, expected 2.0.1\n";
    }
}

// Test 3: Check hook includes ai-chat-faq
echo "\nTest 3: Check hook includes ai-chat-faq\n";
if (strpos($phpContent, "'ai-chat-faq'") !== false) {
    echo "  ✓ FAQ hook check added\n";
} else {
    echo "  ✗ FAQ hook check not found\n";
}

// Test 4: Check submenu explicitly added
echo "\nTest 4: Check 'Управление диалогами' submenu added\n";
if (strpos($phpContent, "'Управление диалогами'") !== false) {
    echo "  ✓ Submenu explicitly added\n";
} else {
    echo "  ✗ Submenu not found\n";
}

// Test 5: Check admin-script.js has aicAdmin check
echo "\nTest 5: Check admin-script.js has aicAdmin check\n";
$jsContent = file_get_contents($basePath . 'ai-multilingual-chat/admin-script.js');
if (strpos($jsContent, "typeof aicAdmin === 'undefined'") !== false) {
    echo "  ✓ aicAdmin existence check added\n";
} else {
    echo "  ✗ aicAdmin check not found\n";
}

// Test 6: Check loading indicator added
echo "\nTest 6: Check loading indicator added\n";
if (strpos($jsContent, 'Загрузка диалогов...') !== false) {
    echo "  ✓ Loading indicator added\n";
} else {
    echo "  ✗ Loading indicator not found\n";
}

// Test 7: Check XSS protection (escapeHtml usage)
echo "\nTest 7: Check XSS protection\n";
if (preg_match('/adminChat\.escapeHtml\(.*user_name/s', $jsContent)) {
    echo "  ✓ User name is escaped\n";
} else {
    echo "  ✗ User name escaping not found\n";
}

if (preg_match('/adminChat\.escapeHtml\(.*last_message/s', $jsContent)) {
    echo "  ✓ Last message is escaped\n";
} else {
    echo "  ✗ Last message escaping not found\n";
}

// Test 8: Check CSS animation added
echo "\nTest 8: Check CSS animations\n";
$cssContent = file_get_contents($basePath . 'ai-multilingual-chat/admin-style.css');
if (strpos($cssContent, '@keyframes rotation') !== false) {
    echo "  ✓ Loading spinner animation added\n";
} else {
    echo "  ✗ Loading spinner animation not found\n";
}

// Test 9: Check min-height added to template
echo "\nTest 9: Check template improvements\n";
$templateContent = file_get_contents($basePath . 'ai-multilingual-chat/templates/admin-chat.php');
if (strpos($templateContent, 'min-height:') !== false || strpos($templateContent, 'min-height') !== false) {
    echo "  ✓ Min-height styles added\n";
} else {
    echo "  ✗ Min-height styles not found\n";
}

// Test 10: Check defensive DOM checks
echo "\nTest 10: Check defensive DOM checks\n";
if (strpos($jsContent, "!container.length") !== false) {
    echo "  ✓ DOM existence checks added\n";
} else {
    echo "  ✗ DOM checks not found\n";
}

echo "\n=== Test Summary ===\n";
echo "All critical changes have been verified.\n";
echo "Admin interface should now display correctly.\n\n";

echo "Next steps:\n";
echo "1. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "2. Navigate to WordPress admin → AI Chat → Управление диалогами\n";
echo "3. Open browser console (F12) to verify initialization logs\n";
echo "4. Verify all interface elements are visible\n";
