#!/usr/bin/env php
<?php
/**
 * Test to verify FAQ add functionality doesn't cause empty page
 * This tests the fix for the issue where adding a new FAQ block caused
 * the page to become empty until manual reload.
 * 
 * Run this from repository root: php tests/test-faq-add-empty-page-fix.php
 */

echo "=== FAQ Add Empty Page Fix Test ===\n\n";

$basePath = dirname(__DIR__) . '/ai-multilingual-chat/';
$passed = 0;
$failed = 0;

// Test 1: Check that render_faq_page doesn't handle POST directly
echo "Test 1: Check that render_faq_page doesn't handle POST directly\n";
$mainFile = $basePath . 'ai-multilingual-chat.php';
if (!file_exists($mainFile)) {
    echo "  ✗ Main plugin file not found\n";
    $failed++;
} else {
    $content = file_get_contents($mainFile);
    
    // Extract the render_faq_page method
    if (preg_match('/function render_faq_page\(\)\s*\{([^}]+)\}/s', $content, $matches)) {
        $methodContent = $matches[1];
        
        // Check that it doesn't have POST handling
        if (strpos($methodContent, "isset(\$_POST['aic_add_faq'])") !== false) {
            echo "  ✗ render_faq_page still handles POST directly (duplicate handling)\n";
            $failed++;
        } else {
            echo "  ✓ render_faq_page doesn't handle POST (template handles it)\n";
            $passed++;
        }
        
        // Check that it doesn't echo notices
        if (strpos($methodContent, 'echo \'<div class="notice') !== false ||
            strpos($methodContent, 'echo "<div class=\"notice') !== false) {
            echo "  ✗ render_faq_page echoes notices (will prevent redirect)\n";
            $failed++;
        } else {
            echo "  ✓ render_faq_page doesn't echo notices\n";
            $passed++;
        }
        
        // Check that it only includes the template
        if (strpos($methodContent, 'include') !== false && 
            strpos($methodContent, 'faq.php') !== false) {
            echo "  ✓ render_faq_page includes the template\n";
            $passed++;
        } else {
            echo "  ✗ render_faq_page doesn't include the template properly\n";
            $failed++;
        }
    } else {
        echo "  ✗ Could not extract render_faq_page method\n";
        $failed++;
    }
}

// Test 2: Check that template handles POST with redirect
echo "\nTest 2: Check that template handles POST with redirect\n";
$faqFile = $basePath . 'templates/faq.php';
if (!file_exists($faqFile)) {
    echo "  ✗ FAQ template file not found\n";
    $failed++;
} else {
    $content = file_get_contents($faqFile);
    
    // Check for POST handling
    if (strpos($content, "isset(\$_POST['aic_add_faq'])") !== false) {
        echo "  ✓ Template handles aic_add_faq POST\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't handle aic_add_faq POST\n";
        $failed++;
    }
    
    // Check for redirect after POST
    if (strpos($content, 'wp_safe_redirect') !== false) {
        echo "  ✓ Template uses wp_safe_redirect after POST\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't redirect after POST\n";
        $failed++;
    }
    
    // Check for exit after redirect
    if (preg_match('/wp_safe_redirect.*?exit/s', $content)) {
        echo "  ✓ Template calls exit after redirect\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't call exit after redirect\n";
        $failed++;
    }
}

// Test 3: Verify Post/Redirect/Get pattern
echo "\nTest 3: Verify Post/Redirect/Get pattern is implemented\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    
    // Check for query parameter in redirect
    if (strpos($content, "add_query_arg('aic_msg'") !== false) {
        echo "  ✓ Template adds message query parameter to redirect URL\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't add message query parameter\n";
        $failed++;
    }
    
    // Check for message display from query parameter
    if (preg_match('/\$aic_msg\s*=.*\$_GET\[.aic_msg.\]/s', $content)) {
        echo "  ✓ Template reads message from query parameter\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't read message from query parameter\n";
        $failed++;
    }
}

// Test 4: Check that template handles database operations
echo "\nTest 4: Check that template handles database operations\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    
    // Check for FAQ insert function
    if (strpos($content, 'function aic_insert_faq') !== false) {
        echo "  ✓ Template defines aic_insert_faq function\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't define aic_insert_faq function\n";
        $failed++;
    }
    
    // Check for FAQ get function
    if (strpos($content, 'function aic_get_faqs') !== false) {
        echo "  ✓ Template defines aic_get_faqs function\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't define aic_get_faqs function\n";
        $failed++;
    }
    
    // Check for created_at timestamp
    if (strpos($content, 'current_time(\'mysql\')') !== false) {
        echo "  ✓ Template sets created_at timestamp\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't set created_at timestamp\n";
        $failed++;
    }
}

// Test 5: Security checks
echo "\nTest 5: Verify security checks in template POST handler\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    
    // Check for nonce verification
    if (strpos($content, 'wp_verify_nonce') !== false) {
        echo "  ✓ Template verifies nonce\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't verify nonce\n";
        $failed++;
    }
    
    // Check for capability check
    if (strpos($content, "current_user_can('manage_options')") !== false) {
        echo "  ✓ Template checks user capabilities\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't check user capabilities\n";
        $failed++;
    }
    
    // Check for input sanitization
    if (strpos($content, 'sanitize_text_field') !== false) {
        echo "  ✓ Template sanitizes input\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't sanitize input\n";
        $failed++;
    }
}

// Test 6: Check correct menu slug usage
echo "\nTest 6: Check correct menu slug usage in redirects\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    
    // Check that redirect uses the correct menu slug
    if (strpos($content, "menu_page_url('ai-chat-faq'") !== false) {
        echo "  ✓ Template uses correct menu slug 'ai-chat-faq'\n";
        $passed++;
    } else {
        echo "  ✗ Template doesn't use correct menu slug\n";
        $failed++;
    }
}

// Test 7: Verify no output before redirect
echo "\nTest 7: Verify no output before redirect\n";
if (file_exists($faqFile)) {
    $content = file_get_contents($faqFile);
    
    // Extract POST handling section
    if (preg_match('/if.*\$_SERVER\[.REQUEST_METHOD.\].*POST.*?\{(.+?)\/\* =+ Загрузка/s', $content, $matches)) {
        $postSection = $matches[1];
        
        // Check that there's no echo/print before redirect
        if (!preg_match('/echo|print(?!.*wp_safe_redirect)/s', $postSection)) {
            echo "  ✓ No output before redirect (allows headers to be sent)\n";
            $passed++;
        } else {
            echo "  ✗ Output found before redirect (will prevent redirect)\n";
            $failed++;
        }
    } else {
        echo "  ⚠ Could not extract POST handling section for analysis\n";
    }
}

// Summary
echo "\n=== Test Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed! FAQ add should work without empty page issue.\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed! Empty page issue may still occur.\n";
    exit(1);
}
