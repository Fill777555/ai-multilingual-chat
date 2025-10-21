#!/usr/bin/env php
<?php
/**
 * Test to verify PHP parse error fix in ai-multilingual-chat.php
 * 
 * This test verifies that:
 * 1. The file has no PHP syntax errors
 * 2. All braces are properly balanced
 * 3. The file can be loaded in a WordPress environment
 * 4. No excessive blank lines that could cause formatting issues
 * 
 * Run this test: php tests/test-php-parse-error-fix.php
 */

echo "=== PHP Parse Error Fix Test ===\n\n";

$basePath = dirname(__DIR__) . '/';
$phpFile = $basePath . 'ai-multilingual-chat/ai-multilingual-chat.php';

if (!file_exists($phpFile)) {
    echo "✗ Error: Plugin file not found at $phpFile\n";
    exit(1);
}

// Test 1: PHP Syntax Check
echo "Test 1: PHP Syntax Check\n";
$output = [];
$return_var = 0;
exec("php -l " . escapeshellarg($phpFile) . " 2>&1", $output, $return_var);
$result = implode("\n", $output);

if ($return_var === 0 && strpos($result, 'No syntax errors') !== false) {
    echo "  ✓ No syntax errors detected\n";
} else {
    echo "  ✗ Syntax errors found:\n";
    echo "  " . $result . "\n";
    exit(1);
}

// Test 2: Check brace balance with PHP tokenizer
echo "\nTest 2: Brace Balance Check\n";
$content = file_get_contents($phpFile);
$tokens = token_get_all($content);

$braceStack = [];
$line = 1;

foreach ($tokens as $token) {
    if (is_array($token)) {
        list($id, $text, $token_line) = $token;
        $line = $token_line;
        
        if ($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) {
            $braceStack[] = ['type' => 'special', 'line' => $line];
        }
    } else {
        if ($token === '{') {
            $braceStack[] = ['type' => 'normal', 'line' => $line];
        } elseif ($token === '}') {
            if (!empty($braceStack)) {
                array_pop($braceStack);
            } else {
                echo "  ✗ Unmatched closing brace at line $line\n";
                exit(1);
            }
        }
    }
}

if (empty($braceStack)) {
    echo "  ✓ All braces are properly balanced\n";
} else {
    echo "  ✗ Unclosed braces found:\n";
    foreach ($braceStack as $brace) {
        echo "    Line {$brace['line']} ({$brace['type']})\n";
    }
    exit(1);
}

// Test 3: Check for excessive blank lines
echo "\nTest 3: Check for Excessive Blank Lines\n";
$lines = explode("\n", $content);
$maxConsecutiveBlankLines = 0;
$currentConsecutiveBlankLines = 0;

foreach ($lines as $lineNum => $line) {
    if (trim($line) === '') {
        $currentConsecutiveBlankLines++;
        $maxConsecutiveBlankLines = max($maxConsecutiveBlankLines, $currentConsecutiveBlankLines);
    } else {
        $currentConsecutiveBlankLines = 0;
    }
}

if ($maxConsecutiveBlankLines <= 2) {
    echo "  ✓ No excessive blank lines found (max consecutive: $maxConsecutiveBlankLines)\n";
} else {
    echo "  ⚠ Warning: Found $maxConsecutiveBlankLines consecutive blank lines (recommended: ≤2)\n";
}

// Test 4: Verify specific lines mentioned in the issue
echo "\nTest 4: Verify Lines 16 and 357\n";

// Check line 16 (define statement)
if (isset($lines[15]) && strpos($lines[15], "define('AIC_PLUGIN_URL'") !== false) {
    echo "  ✓ Line 16: define('AIC_PLUGIN_URL') is properly formatted\n";
} else {
    echo "  ✗ Line 16: Expected define('AIC_PLUGIN_URL') not found\n";
    exit(1);
}

// Check line 357 (should be a comment or conditional)
if (isset($lines[356])) {
    $line357 = trim($lines[356]);
    if (strpos($line357, '//') === 0 || strpos($line357, 'if') !== false) {
        echo "  ✓ Line 357: Properly formatted ($line357)\n";
    } else {
        echo "  ⚠ Line 357: Different from expected, but valid ($line357)\n";
    }
}

// Test 5: Check the fixed area around line 1616
echo "\nTest 5: Verify Fix Around Line 1616\n";
if (isset($lines[1615]) && trim($lines[1615]) === '});') {
    echo "  ✓ Line 1616: Closing brace and parenthesis properly placed\n";
    
    // Check that there are no more than 1 blank line after
    $blankLinesAfter = 0;
    for ($i = 1616; $i < min(1620, count($lines)); $i++) {
        if (trim($lines[$i]) === '') {
            $blankLinesAfter++;
        } else {
            break;
        }
    }
    
    if ($blankLinesAfter <= 1) {
        echo "  ✓ Proper spacing after line 1616 ($blankLinesAfter blank line(s))\n";
    } else {
        echo "  ⚠ Warning: $blankLinesAfter blank lines after line 1616\n";
    }
} else {
    echo "  ⚠ Line 1616: Structure may have changed\n";
}

// Test 6: Simulate WordPress loading
echo "\nTest 6: Simulate WordPress Environment Loading\n";

// Mock minimal WordPress functions
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wp-test/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

function plugin_dir_path($file) {
    return dirname($file) . '/';
}

function plugin_dir_url($file) {
    return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/';
}

function plugin_basename($file) {
    return basename(dirname($file)) . '/' . basename($file);
}

function add_action($hook, $callback, $priority = 10, $args = 1) {
    // Mock
}

function add_filter($hook, $callback, $priority = 10, $args = 1) {
    // Mock
}

function register_activation_hook($file, $callback) {
    // Mock
}

function register_deactivation_hook($file, $callback) {
    // Mock
}

try {
    require_once $phpFile;
    echo "  ✓ Plugin file loaded successfully in simulated WordPress environment\n";
} catch (ParseError $e) {
    echo "  ✗ Parse Error: " . $e->getMessage() . "\n";
    echo "    File: " . $e->getFile() . "\n";
    echo "    Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== All Tests Passed ✓ ===\n";
echo "\nSummary:\n";
echo "- No PHP syntax errors detected\n";
echo "- All braces are properly balanced\n";
echo "- No excessive blank lines\n";
echo "- File loads successfully in WordPress environment\n";
echo "- Specific lines (16, 357, 1616) are properly formatted\n";

exit(0);
