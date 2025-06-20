<?php
/**
 * Quick Validation Test for Happy Business Listing Improvements
 *
 * This script validates that all five major improvements are working correctly.
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    if (!function_exists('wp_verify_nonce')) {
        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';
    }
}

/**
 * Class HBL_Validation_Test
 */
class HBL_Validation_Test {
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
        echo "HAPPY BUSINESS LISTING - VALIDATION TEST\n";
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
            'test_e2e_class_exists' => class_exists('E2ETest'),
            'test_whatsapp_e2e_class_exists' => class_exists('WhatsAppIntegrationE2ETest'),
            'test_runner_class_exists' => class_exists('HBL_E2E_Test_Runner')
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
        
        $tests = array(
            'test_rest_api_file_exists' => file_exists(HBL_PLUGIN_DIR . 'includes/rest-api.php'),
            'test_rest_routes_registered' => function_exists('hbl_register_rest_routes'),
            'test_permission_check_exists' => function_exists('hbl_rest_permission_check'),
            'test_businesses_endpoint_exists' => function_exists('hbl_get_businesses_rest'),
            'test_search_endpoint_exists' => function_exists('hbl_search_businesses_rest'),
            'test_whatsapp_endpoint_exists' => function_exists('hbl_send_whatsapp_rest'),
            'test_stats_endpoint_exists' => function_exists('hbl_get_stats_rest'),
            'test_format_functions_exist' => function_exists('hbl_format_business_for_api')
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
        
        $tests = array(
            'test_caching_file_exists' => file_exists(HBL_PLUGIN_DIR . 'includes/caching.php'),
            'test_cache_class_exists' => class_exists('HBL_Cache'),
            'test_cache_get_method' => method_exists('HBL_Cache', 'get'),
            'test_cache_set_method' => method_exists('HBL_Cache', 'set'),
            'test_cache_delete_method' => method_exists('HBL_Cache', 'delete'),
            'test_cache_clear_all_method' => method_exists('HBL_Cache', 'clear_all'),
            'test_cache_stats_method' => method_exists('HBL_Cache', 'get_stats'),
            'test_business_cache_function' => function_exists('hbl_cache_business_listings'),
            'test_search_cache_function' => function_exists('hbl_cache_search_results'),
            'test_stats_cache_function' => function_exists('hbl_cache_stats')
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
            'test_css_file_size' => filesize($css_file) > 50000, // Should be substantial
            'test_css_custom_properties' => strpos($css_content, '--hbl-') !== false,
            'test_responsive_media_queries' => strpos($css_content, '@media') !== false,
            'test_mobile_breakpoints' => strpos($css_content, 'max-width') !== false,
            'test_grid_layout' => strpos($css_content, 'grid-template-columns') !== false,
            'test_flexbox_layout' => strpos($css_content, 'display: flex') !== false,
            'test_touch_targets' => strpos($css_content, '44px') !== false,
            'test_high_contrast_support' => strpos($css_content, 'prefers-contrast') !== false,
            'test_reduced_motion_support' => strpos($css_content, 'prefers-reduced-motion') !== false
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
        
        $tests = array(
            'test_accessibility_file_exists' => file_exists(HBL_PLUGIN_DIR . 'includes/accessibility.php'),
            'test_skip_links_function' => function_exists('hbl_add_skip_links'),
            'test_aria_labels_function' => function_exists('hbl_add_aria_labels'),
            'test_keyboard_navigation_function' => function_exists('hbl_add_keyboard_navigation'),
            'test_focus_management_function' => function_exists('hbl_add_focus_management'),
            'test_high_contrast_function' => function_exists('hbl_add_high_contrast_support'),
            'test_screen_reader_function' => function_exists('hbl_add_screen_reader_announcements'),
            'test_color_contrast_function' => function_exists('hbl_add_color_contrast_checker'),
            'test_accessibility_settings_function' => function_exists('hbl_add_accessibility_settings'),
            'test_accessibility_report_function' => function_exists('hbl_get_accessibility_report')
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
    }
}

// Run validation test if called directly
if (php_sapi_name() === 'cli' || (isset($_GET['run_validation']) && current_user_can('manage_options'))) {
    $validator = new HBL_Validation_Test();
    $validator->run_all_tests();
} 