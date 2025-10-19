<?php
/**
 * Test: Accessibility and WCAG AA Compliance
 * 
 * This test verifies:
 * 1. Color contrast ratios meet WCAG AA standards (4.5:1 for normal text, 3:1 for large text)
 * 2. Proper ARIA attributes are used
 * 3. Keyboard navigation support
 * 4. Focus indicators are present
 */

class AccessibilityTest {
    private $results = array();
    private $passed = 0;
    private $failed = 0;
    
    public function run() {
        echo "<h1>Accessibility and WCAG AA Compliance Test</h1>\n";
        echo "<div style='font-family: monospace; padding: 20px;'>\n";
        
        $this->test_color_contrast();
        $this->test_aria_attributes();
        $this->test_keyboard_navigation();
        $this->test_focus_indicators();
        
        $this->display_results();
        
        echo "</div>\n";
    }
    
    private function test_color_contrast() {
        $test_name = "Color Contrast Ratios";
        
        // Define color combinations from CSS variables
        $light_theme_combos = array(
            // Background vs Primary Text
            array('bg' => '#ffffff', 'fg' => '#0f1724', 'context' => 'Light: Background vs Primary Text'),
            // Surface vs Primary Text
            array('bg' => '#f6f7fb', 'fg' => '#0f1724', 'context' => 'Light: Surface vs Primary Text'),
            // Background vs Secondary Text
            array('bg' => '#ffffff', 'fg' => '#475569', 'context' => 'Light: Background vs Secondary Text'),
            // Accent vs White (buttons)
            array('bg' => '#6b46d9', 'fg' => '#ffffff', 'context' => 'Light: Accent Button Text'),
        );
        
        $dark_theme_combos = array(
            // Background vs Primary Text
            array('bg' => '#0b1020', 'fg' => '#e6eef8', 'context' => 'Dark: Background vs Primary Text'),
            // Surface vs Primary Text
            array('bg' => '#0f1724', 'fg' => '#e6eef8', 'context' => 'Dark: Surface vs Primary Text'),
            // Background vs Secondary Text
            array('bg' => '#0b1020', 'fg' => '#9fb0c8', 'context' => 'Dark: Background vs Secondary Text'),
            // Accent vs White (buttons)
            array('bg' => '#7450ff', 'fg' => '#ffffff', 'context' => 'Dark: Accent Button Text'),
        );
        
        $all_combos = array_merge($light_theme_combos, $dark_theme_combos);
        $failed_contrasts = array();
        
        foreach ($all_combos as $combo) {
            $ratio = $this->calculate_contrast_ratio($combo['bg'], $combo['fg']);
            
            // WCAG AA requires 4.5:1 for normal text, 3:1 for large text
            if ($ratio < 4.5) {
                $failed_contrasts[] = "{$combo['context']}: {$ratio}:1 (minimum 4.5:1)";
            }
        }
        
        if (empty($failed_contrasts)) {
            $this->pass($test_name, "All color combinations meet WCAG AA contrast requirements");
        } else {
            $this->fail($test_name, "Some contrasts below WCAG AA: " . implode(', ', $failed_contrasts));
        }
    }
    
    private function calculate_contrast_ratio($bg, $fg) {
        // Convert hex to RGB
        $bg_rgb = $this->hex_to_rgb($bg);
        $fg_rgb = $this->hex_to_rgb($fg);
        
        // Calculate relative luminance
        $bg_lum = $this->get_relative_luminance($bg_rgb);
        $fg_lum = $this->get_relative_luminance($fg_rgb);
        
        // Calculate contrast ratio
        $lighter = max($bg_lum, $fg_lum);
        $darker = min($bg_lum, $fg_lum);
        
        $ratio = ($lighter + 0.05) / ($darker + 0.05);
        
        return round($ratio, 2);
    }
    
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return array(
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        );
    }
    
    private function get_relative_luminance($rgb) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;
        
        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
        
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
    
    private function test_aria_attributes() {
        $test_name = "ARIA Attributes";
        $template_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/admin-chat.php';
        
        if (!file_exists($template_file)) {
            $this->fail($test_name, "Template file not found");
            return;
        }
        
        $template_content = file_get_contents($template_file);
        
        // Check for ARIA attributes
        $has_role = strpos($template_content, 'role=') !== false;
        $has_aria_label = strpos($template_content, 'aria-label=') !== false;
        $has_aria_pressed = strpos($template_content, 'aria-pressed=') !== false;
        
        if ($has_role && $has_aria_label && $has_aria_pressed) {
            $this->pass($test_name, "Template includes proper ARIA attributes (role, aria-label, aria-pressed)");
        } else {
            $missing = array();
            if (!$has_role) $missing[] = 'role';
            if (!$has_aria_label) $missing[] = 'aria-label';
            if (!$has_aria_pressed) $missing[] = 'aria-pressed';
            $this->fail($test_name, "Missing ARIA attributes: " . implode(', ', $missing));
        }
    }
    
    private function test_keyboard_navigation() {
        $test_name = "Keyboard Navigation Support";
        $template_file = dirname(__FILE__) . '/../ai-multilingual-chat/templates/admin-chat.php';
        
        if (!file_exists($template_file)) {
            $this->fail($test_name, "Template file not found");
            return;
        }
        
        $template_content = file_get_contents($template_file);
        
        // Check for proper button elements (not divs with onclick)
        $button_count = substr_count($template_content, '<button');
        $onclick_count = substr_count($template_content, 'onclick=');
        
        if ($button_count >= 3 && $onclick_count === 0) {
            $this->pass($test_name, "Uses semantic button elements, no inline onclick handlers");
        } else {
            $this->fail($test_name, "May have accessibility issues with keyboard navigation");
        }
    }
    
    private function test_focus_indicators() {
        $test_name = "Focus Indicators";
        $css_file = dirname(__FILE__) . '/../ai-multilingual-chat/admin-style.css';
        
        if (!file_exists($css_file)) {
            $this->fail($test_name, "CSS file not found");
            return;
        }
        
        $css_content = file_get_contents($css_file);
        
        // Check for :focus styles
        $has_focus = strpos($css_content, ':focus') !== false;
        $has_outline = strpos($css_content, 'outline:') !== false || strpos($css_content, 'outline-offset:') !== false;
        
        if ($has_focus && $has_outline) {
            $this->pass($test_name, "Focus indicators are defined in CSS");
        } else {
            $this->fail($test_name, "Missing proper focus indicators");
        }
    }
    
    private function pass($test_name, $message = "") {
        $this->passed++;
        $this->results[] = array(
            'status' => 'PASS',
            'test' => $test_name,
            'message' => $message
        );
    }
    
    private function fail($test_name, $message = "") {
        $this->failed++;
        $this->results[] = array(
            'status' => 'FAIL',
            'test' => $test_name,
            'message' => $message
        );
    }
    
    private function display_results() {
        echo "<hr>\n";
        echo "<h2>Test Results</h2>\n";
        echo "<table style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='border: 1px solid #ccc; padding: 8px;'>Status</th>";
        echo "<th style='border: 1px solid #ccc; padding: 8px;'>Test</th>";
        echo "<th style='border: 1px solid #ccc; padding: 8px;'>Message</th>";
        echo "</tr>\n";
        
        foreach ($this->results as $result) {
            $color = $result['status'] === 'PASS' ? '#4caf50' : '#f44336';
            echo "<tr>";
            echo "<td style='border: 1px solid #ccc; padding: 8px; color: white; background: $color; font-weight: bold;'>{$result['status']}</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$result['test']}</td>";
            echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$result['message']}</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
        echo "<h3>Summary</h3>\n";
        echo "<p><strong>Passed:</strong> {$this->passed} | <strong>Failed:</strong> {$this->failed}</p>\n";
        
        if ($this->failed === 0) {
            echo "<p style='color: #4caf50; font-weight: bold;'>✓ All accessibility tests passed!</p>\n";
        } else {
            echo "<p style='color: #f44336; font-weight: bold;'>✗ Some accessibility tests failed. Please review.</p>\n";
        }
    }
}

$test = new AccessibilityTest();
$test->run();
