#!/usr/bin/env php
<?php
/**
 * Simple test to verify admin avatar feature
 * Run this from WordPress root: php tests/test-admin-avatar-feature.php
 */

// Simulate WordPress environment
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('WPINC', 'wp-includes');

echo "=== Admin Avatar Feature Test ===\n\n";

$basePath = dirname(__DIR__) . '/';

// Test 1: Check version is updated to 2.0.5
echo "Test 1: Check version is updated to 2.0.5\n";
$phpContent = file_get_contents($basePath . 'ai-multilingual-chat/ai-multilingual-chat.php');
if (preg_match('/Version:\s*([0-9.]+)/', $phpContent, $matches)) {
    $version = $matches[1];
    echo "  - Plugin version: $version\n";
    if ($version === '2.0.5') {
        echo "  ✓ Version correctly set to 2.0.5\n";
    } else {
        echo "  ✗ Version is $version, expected 2.0.5\n";
    }
}

if (preg_match('/define\([\'"]AIC_VERSION[\'"],\s*[\'"]([0-9.]+)[\'"]\)/', $phpContent, $matches)) {
    $constVersion = $matches[1];
    echo "  - AIC_VERSION constant: $constVersion\n";
    if ($constVersion === '2.0.5') {
        echo "  ✓ Constant correctly set to 2.0.5\n";
    } else {
        echo "  ✗ Constant is $constVersion, expected 2.0.5\n";
    }
}

// Test 2: Check default option for admin avatar
echo "\nTest 2: Check default option for admin avatar\n";
if (strpos($phpContent, "'aic_admin_avatar' => ''") !== false) {
    echo "  ✓ Default admin avatar option added\n";
} else {
    echo "  ✗ Default admin avatar option not found\n";
}

// Test 3: Check admin avatar in save_settings
echo "\nTest 3: Check admin avatar in save_settings\n";
if (strpos($phpContent, "'aic_admin_avatar'") !== false && 
    strpos($phpContent, "save_settings") !== false) {
    echo "  ✓ Admin avatar included in save_settings\n";
} else {
    echo "  ✗ Admin avatar not included in save_settings\n";
}

// Test 4: Check admin avatar passed to frontend scripts
echo "\nTest 4: Check admin avatar passed to scripts\n";
if (preg_match("/'admin_avatar'\s*=>\s*get_option\('aic_admin_avatar'/", $phpContent)) {
    echo "  ✓ Admin avatar passed to frontend scripts\n";
} else {
    echo "  ✗ Admin avatar not passed to frontend scripts\n";
}

// Test 5: Check settings template has upload field
echo "\nTest 5: Check settings template has upload field\n";
$settingsContent = file_get_contents($basePath . 'ai-multilingual-chat/templates/settings.php');
if (strpos($settingsContent, 'aic_upload_avatar') !== false) {
    echo "  ✓ Upload button found in settings\n";
} else {
    echo "  ✗ Upload button not found in settings\n";
}

if (strpos($settingsContent, 'wp.media') !== false) {
    echo "  ✓ WordPress media library integration found\n";
} else {
    echo "  ✗ WordPress media library integration not found\n";
}

if (strpos($settingsContent, 'aic_admin_avatar_preview') !== false) {
    echo "  ✓ Avatar preview element found\n";
} else {
    echo "  ✗ Avatar preview element not found\n";
}

// Test 6: Check frontend script renders avatar
echo "\nTest 6: Check frontend script renders avatar\n";
$frontendContent = file_get_contents($basePath . 'ai-multilingual-chat/frontend-script.js');
if (strpos($frontendContent, 'aicFrontend.admin_avatar') !== false) {
    echo "  ✓ Frontend script checks for admin avatar\n";
} else {
    echo "  ✗ Frontend script doesn't check for admin avatar\n";
}

if (strpos($frontendContent, 'aic-admin-avatar') !== false) {
    echo "  ✓ Frontend script renders avatar with class\n";
} else {
    echo "  ✗ Frontend script doesn't render avatar with class\n";
}

// Test 7: Check admin script renders avatar
echo "\nTest 7: Check admin script renders avatar\n";
$adminScriptContent = file_get_contents($basePath . 'ai-multilingual-chat/admin-script.js');
if (strpos($adminScriptContent, 'aicAdmin.admin_avatar') !== false) {
    echo "  ✓ Admin script checks for admin avatar\n";
} else {
    echo "  ✗ Admin script doesn't check for admin avatar\n";
}

// Test 8: Check CSS styling for avatar
echo "\nTest 8: Check CSS styling for avatar\n";
$chatWidgetContent = file_get_contents($basePath . 'ai-multilingual-chat/templates/chat-widget.php');
if (strpos($chatWidgetContent, '.aic-admin-avatar') !== false) {
    echo "  ✓ Avatar CSS class found\n";
} else {
    echo "  ✗ Avatar CSS class not found\n";
}

if (strpos($chatWidgetContent, 'border-radius: 50%') !== false) {
    echo "  ✓ Avatar has circular styling\n";
} else {
    echo "  ✗ Avatar doesn't have circular styling\n";
}

// Test 9: Check media uploader is enqueued
echo "\nTest 9: Check media uploader is enqueued\n";
if (strpos($phpContent, 'wp_enqueue_media()') !== false) {
    echo "  ✓ WordPress media library is enqueued\n";
} else {
    echo "  ✗ WordPress media library is not enqueued\n";
}

echo "\n=== Test Complete ===\n";
