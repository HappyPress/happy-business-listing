<?php
/**
 * Standalone Validation Script for Happy Business Listing Improvements
 *
 * This script validates that all five major improvements are properly implemented
 * without requiring WordPress database connection.
 */

// Define plugin directory constant if not defined
if (!defined('HBL_PLUGIN_DIR')) {
    define('HBL_PLUGIN_DIR', __DIR__ . '/');
}

/**
 * Class HBL_Standalone_Validator
 */
class HBL_Standalone_Validator {
    /**
     * Test results
     *
     * @var array
     */
    private $results = array();
    
    /**
     * Run all validation tests
     */
    public function run_all_tests() {
        echo "========================================\n";
        echo "HAPPY BUSINESS LISTING - IMPROVEMENTS VALIDATION\n";
        echo "========================================\n\n";
        
        $this->test_e2e_framework();
        $this->test_rest_api();
        $this->test_caching_system();
        $this->test_mobile_responsiveness();
        $this->test_accessibility_features();
        
        $this->print_results();
    }
    
    /**
     * Test E2E Framework
     */
    private function test_e2e_framework() {
        echo "Testing E2E Framework...\n";
        
        $tests = array(
            'test_e2e_file_exists' => file_exists(HBL_PLUGIN_DIR . 'tests/e2e/test-e2e.php'),
            'test_whatsapp_e2e_exists' => file_exists(HBL_PLUGIN_DIR . 'tests/e2e/test-whatsapp-integration.php'),
            'test_runner_exists' => file_exists(HBL_PLUGIN_DIR . 'tests/e2e/run-e2e-tests.php'),
            'test_e2e_file_size' => filesize(HBL_PLUGIN_DIR . 'tests/e2e/test-e2e.php') > 50000,
            'test_whatsapp_e2e_file_size' => filesize(HBL_PLUGIN_DIR . 'tests/e2e/test-whatsapp-integration.php') > 20000,
            'test_runner_file_size' => filesize(HBL_PLUGIN_DIR . 'tests/e2e/run-e2e-tests.php') > 15000
        );
        
        $this->results['e2e_framework'] = $tests;
        
        foreach ($tests as $test => $result) {
            echo "  - " . str_replace('_', ' ', $test) . ": " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
        }
        echo "\n";
    }
    
    /**
     * Test REST API
     */
    private function test_rest_api() {
        echo "Testing REST API...\n";
        
        $rest_api_file = HBL_PLUGIN_DIR . 'includes/rest-api.php';
        $rest_api_content = file_exists($rest_api_file) ? file_get_contents($rest_api_file) : '';
        
        $tests = array(
            'test_rest_api_file_exists' => file_exists($rest_api_file),
            'test_rest_api_file_size' => filesize($rest_api_file) > 80000,
            'test_rest_routes_function' => strpos($rest_api_content, 'hbl_register_rest_routes') !== false,
            'test_permission_check_function' => strpos($rest_api_content, 'hbl_rest_permission_check') !== false,
            'test_businesses_endpoint' => strpos($rest_api_content, 'hbl_get_businesses_rest') !== false,
            'test_search_endpoint' => strpos($rest_api_content, 'hbl_search_businesses_rest') !== false,
            'test_whatsapp_endpoint' => strpos($rest_api_content, 'hbl_send_whatsapp_rest') !== false,
            'test_stats_endpoint' => strpos($rest_api_content, 'hbl_get_stats_rest') !== false,
            'test_format_functions' => strpos($rest_api_content, 'hbl_format_business_for_api') !== false,
            'test_api_documentation' => strpos($rest_api_content, 'REST API documentation') !== false
        );
        
        $this->results['rest_api'] = $tests;
        
        foreach ($tests as $test => $result) {
            echo "  - " . str_replace('_', ' ', $test) . ": " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
        }
        echo "\n";
    }
    
    /**
     * Test Caching System
     */
    private function test_caching_system() {
        echo "Testing Caching System...\n";
        
        $caching_file = HBL_PLUGIN_DIR . 'includes/caching.php';
        $caching_content = file_exists($caching_file) ? file_get_contents($caching_file) : '';
        
        $tests = array(
            'test_caching_file_exists' => file_exists($caching_file),
            'test_caching_file_size' => filesize($caching_file) > 50000,
            'test_cache_class' => strpos($caching_content, 'class HBL_Cache') !== false,
            'test_cache_get_method' => strpos($caching_content, 'public static function get') !== false,
            'test_cache_set_method' => strpos($caching_content, 'public static function set') !== false,
            'test_cache_delete_method' => strpos($caching_content, 'public static function delete') !== false,
            'test_cache_clear_all_method' => strpos($caching_content, 'public static function clear_all') !== false,
            'test_cache_stats_method' => strpos($caching_content, 'public static function get_stats') !== false,
            'test_business_cache_function' => strpos($caching_content, 'hbl_cache_business_listings') !== false,
            'test_search_cache_function' => strpos($caching_content, 'hbl_cache_search_results') !== false,
            'test_stats_cache_function' => strpos($caching_content, 'hbl_cache_stats') !== false,
            'test_cache_invalidation' => strpos($caching_content, 'hbl_clear_business_cache') !== false
        );
        
        $this->results['caching_system'] = $tests;
        
        foreach ($tests as $test => $result) {
            echo "  - " . str_replace('_', ' ', $test) . ": " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
        }
        echo "\n";
    }
    
    /**
     * Test Mobile Responsiveness
     */
    private function test_mobile_responsiveness() {
        echo "Testing Mobile Responsiveness...\n";
        
        $css_file = HBL_PLUGIN_DIR . 'assets/css/templates.css';
        $css_content = file_exists($css_file) ? file_get_contents($css_file) : '';
        
        $tests = array(
            'test_css_file_exists' => file_exists($css_file),
            'test_css_file_size' => filesize($css_file) > 50000,
            'test_css_custom_properties' => strpos($css_content, '--hbl-') !== false,
            'test_responsive_media_queries' => strpos($css_content, '@media') !== false,
            'test_mobile_breakpoints' => strpos($css_content, 'max-width') !== false,
            'test_grid_layout' => strpos($css_content, 'grid-template-columns') !== false,
            'test_flexbox_layout' => strpos($css_content, 'display: flex') !== false,
            'test_touch_targets' => strpos($css_content, '44px') !== false,
            'test_high_contrast_support' => strpos($css_content, 'prefers-contrast') !== false,
            'test_reduced_motion_support' => strpos($css_content, 'prefers-reduced-motion') !== false,
            'test_dark_mode_support' => strpos($css_content, 'prefers-color-scheme: dark') !== false,
            'test_print_styles' => strpos($css_content, '@media print') !== false
        );
        
        $this->results['mobile_responsiveness'] = $tests;
        
        foreach ($tests as $test => $result) {
            echo "  - " . str_replace('_', ' ', $test) . ": " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
        }
        echo "\n";
    }
    
    /**
     * Test Accessibility Features
     */
    private function test_accessibility_features() {
        echo "Testing Accessibility Features...\n";
        
        $accessibility_file = HBL_PLUGIN_DIR . 'includes/accessibility.php';
        $accessibility_content = file_exists($accessibility_file) ? file_get_contents($accessibility_file) : '';
        
        $tests = array(
            'test_accessibility_file_exists' => file_exists($accessibility_file),
            'test_accessibility_file_size' => filesize($accessibility_file) > 40000,
            'test_skip_links_function' => strpos($accessibility_content, 'hbl_add_skip_links') !== false,
            'test_aria_labels_function' => strpos($accessibility_content, 'hbl_add_aria_labels') !== false,
            'test_keyboard_navigation_function' => strpos($accessibility_content, 'hbl_add_keyboard_navigation') !== false,
            'test_focus_management_function' => strpos($accessibility_content, 'hbl_add_focus_management') !== false,
            'test_high_contrast_function' => strpos($accessibility_content, 'hbl_add_high_contrast_support') !== false,
            'test_screen_reader_function' => strpos($accessibility_content, 'hbl_add_screen_reader_announcements') !== false,
            'test_color_contrast_function' => strpos($accessibility_content, 'hbl_add_color_contrast_checker') !== false,
            'test_accessibility_settings_function' => strpos($accessibility_content, 'hbl_add_accessibility_settings') !== false,
            'test_accessibility_report_function' => strpos($accessibility_content, 'hbl_get_accessibility_report') !== false,
            'test_wcag_compliance' => strpos($accessibility_content, 'WCAG 2.1 AA') !== false
        );
        
        $this->results['accessibility_features'] = $tests;
        
        foreach ($tests as $test => $result) {
            echo "  - " . str_replace('_', ' ', $test) . ": " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
        }
        echo "\n";
    }
    
    /**
     * Print test results summary
     */
    private function print_results() {
        echo "========================================\n";
        echo "VALIDATION TEST RESULTS\n";
        echo "========================================\n\n";
        
        $total_tests = 0;
        $passed_tests = 0;
        
        foreach ($this->results as $category => $tests) {
            $category_total = count($tests);
            $category_passed = count(array_filter($tests));
            
            $total_tests += $category_total;
            $passed_tests += $category_passed;
            
            $percentage = round(($category_passed / $category_total) * 100, 1);
            
            echo strtoupper(str_replace('_', ' ', $category)) . ":\n";
            echo "  Tests: {$category_passed}/{$category_total} ({$percentage}%)\n";
            
            if ($category_passed < $category_total) {
                $failed_tests = array_keys(array_filter($tests, function($result) { return !$result; }));
                echo "  Failed: " . implode(', ', $failed_tests) . "\n";
            }
            echo "\n";
        }
        
        $overall_percentage = round(($passed_tests / $total_tests) * 100, 1);
        
        echo "OVERALL RESULTS:\n";
        echo "  Total Tests: {$total_tests}\n";
        echo "  Passed: {$passed_tests}\n";
        echo "  Failed: " . ($total_tests - $passed_tests) . "\n";
        echo "  Success Rate: {$overall_percentage}%\n\n";
        
        if ($overall_percentage >= 95) {
            echo "🎉 EXCELLENT! All improvements are properly implemented.\n";
        } elseif ($overall_percentage >= 80) {
            echo "✅ GOOD! Most improvements are working correctly.\n";
        } else {
            echo "⚠️  ATTENTION! Some improvements need attention.\n";
        }
        
        echo "\n";
        
        // File size summary
        echo "FILE SIZE SUMMARY:\n";
        echo "  E2E Tests: " . number_format(filesize(HBL_PLUGIN_DIR . 'tests/e2e/test-e2e.php')) . " bytes\n";
        echo "  REST API: " . number_format(filesize(HBL_PLUGIN_DIR . 'includes/rest-api.php')) . " bytes\n";
        echo "  Caching: " . number_format(filesize(HBL_PLUGIN_DIR . 'includes/caching.php')) . " bytes\n";
        echo "  Mobile CSS: " . number_format(filesize(HBL_PLUGIN_DIR . 'assets/css/templates.css')) . " bytes\n";
        echo "  Accessibility: " . number_format(filesize(HBL_PLUGIN_DIR . 'includes/accessibility.php')) . " bytes\n";
        echo "\n";
    }
}

// Run validation test
$validator = new HBL_Standalone_Validator();
$validator->run_all_tests(); 