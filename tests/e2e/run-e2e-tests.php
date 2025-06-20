<?php
/**
 * E2E Test Runner for Happy Business Listing
 *
 * This file runs all E2E tests with proper WordPress environment setup.
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HBL_E2E_Test_Runner
 */
class HBL_E2E_Test_Runner {
    /**
     * Test results
     *
     * @var array
     */
    private $results = array();
    
    /**
     * Test files
     *
     * @var array
     */
    private $test_files = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_test_files();
    }
    
    /**
     * Load all test files
     */
    private function load_test_files() {
        $test_dir = HBL_PLUGIN_DIR . 'tests/e2e/';
        
        $this->test_files = array(
            $test_dir . 'test-e2e.php',
            $test_dir . 'test-whatsapp-integration.php'
        );
        
        // Load each test file
        foreach ($this->test_files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
    
    /**
     * Run all E2E tests
     *
     * @return array Test results
     */
    public function run_all_tests() {
        $this->results = array(
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => array()
        );
        
        // Test classes to run
        $test_classes = array(
            'E2ETest',
            'WhatsAppIntegrationE2ETest'
        );
        
        foreach ($test_classes as $class_name) {
            if (class_exists($class_name)) {
                $this->run_test_class($class_name);
            }
        }
        
        return $this->results;
    }
    
    /**
     * Run a specific test class
     *
     * @param string $class_name Test class name
     */
    private function run_test_class($class_name) {
        $test_instance = new $class_name();
        
        // Get all test methods
        $test_methods = $this->get_test_methods($test_instance);
        
        foreach ($test_methods as $method_name) {
            $this->run_single_test($test_instance, $method_name);
        }
    }
    
    /**
     * Get test methods from a test class
     *
     * @param object $test_instance Test class instance
     * @return array Test method names
     */
    private function get_test_methods($test_instance) {
        $methods = get_class_methods($test_instance);
        $test_methods = array();
        
        foreach ($methods as $method) {
            if (strpos($method, 'test_') === 0) {
                $test_methods[] = $method;
            }
        }
        
        return $test_methods;
    }
    
    /**
     * Run a single test method
     *
     * @param object $test_instance Test class instance
     * @param string $method_name Test method name
     */
    private function run_single_test($test_instance, $method_name) {
        $this->results['total']++;
        
        try {
            // Set up test environment
            if (method_exists($test_instance, 'setUp')) {
                $test_instance->setUp();
            }
            
            // Run the test
            $test_instance->$method_name();
            
            // Test passed
            $this->results['passed']++;
            
        } catch (Exception $e) {
            // Test failed
            $this->results['failed']++;
            $this->results['errors'][] = array(
                'class' => get_class($test_instance),
                'method' => $method_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        } catch (Error $e) {
            // Test failed with fatal error
            $this->results['failed']++;
            $this->results['errors'][] = array(
                'class' => get_class($test_instance),
                'method' => $method_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        } finally {
            // Clean up test environment
            if (method_exists($test_instance, 'tearDown')) {
                $test_instance->tearDown();
            }
        }
    }
    
    /**
     * Print test results
     *
     * @param array $results Test results
     */
    public function print_results($results) {
        echo "\n";
        echo "========================================\n";
        echo "HAPPY BUSINESS LISTING - E2E TEST RESULTS\n";
        echo "========================================\n";
        echo "Total Tests: " . $results['total'] . "\n";
        echo "Passed: " . $results['passed'] . "\n";
        echo "Failed: " . $results['failed'] . "\n";
        echo "Skipped: " . $results['skipped'] . "\n";
        echo "Success Rate: " . round(($results['passed'] / $results['total']) * 100, 2) . "%\n";
        echo "========================================\n";
        
        if (!empty($results['errors'])) {
            echo "\nFAILED TESTS:\n";
            echo "=============\n";
            
            foreach ($results['errors'] as $error) {
                echo "Class: " . $error['class'] . "\n";
                echo "Method: " . $error['method'] . "\n";
                echo "Error: " . $error['error'] . "\n";
                echo "---\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Generate test report
     *
     * @param array $results Test results
     * @return string HTML report
     */
    public function generate_html_report($results) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>HBL E2E Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f0f0f0; padding: 20px; border-radius: 5px; }
        .summary { margin: 20px 0; }
        .error { background: #ffe6e6; padding: 10px; margin: 10px 0; border-left: 4px solid #ff0000; }
        .success { color: #008000; }
        .failure { color: #ff0000; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Happy Business Listing - E2E Test Results</h1>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
    </div>
    
    <div class="summary">
        <h2>Test Summary</h2>
        <table>
            <tr>
                <th>Metric</th>
                <th>Count</th>
            </tr>
            <tr>
                <td>Total Tests</td>
                <td>' . $results['total'] . '</td>
            </tr>
            <tr>
                <td>Passed</td>
                <td class="success">' . $results['passed'] . '</td>
            </tr>
            <tr>
                <td>Failed</td>
                <td class="failure">' . $results['failed'] . '</td>
            </tr>
            <tr>
                <td>Skipped</td>
                <td>' . $results['skipped'] . '</td>
            </tr>
            <tr>
                <td>Success Rate</td>
                <td>' . round(($results['passed'] / $results['total']) * 100, 2) . '%</td>
            </tr>
        </table>
    </div>';
        
        if (!empty($results['errors'])) {
            $html .= '
    <div class="errors">
        <h2>Failed Tests</h2>';
            
            foreach ($results['errors'] as $error) {
                $html .= '
        <div class="error">
            <h3>' . $error['class'] . '::' . $error['method'] . '</h3>
            <p><strong>Error:</strong> ' . esc_html($error['error']) . '</p>
            <pre>' . esc_html($error['trace']) . '</pre>
        </div>';
            }
            
            $html .= '
    </div>';
        }
        
        $html .= '
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Save test results to file
     *
     * @param array $results Test results
     * @param string $format Output format (html, json, txt)
     */
    public function save_results($results, $format = 'html') {
        $output_dir = HBL_PLUGIN_DIR . 'tests/reports/';
        
        // Create reports directory if it doesn't exist
        if (!file_exists($output_dir)) {
            wp_mkdir_p($output_dir);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'html':
                $content = $this->generate_html_report($results);
                $filename = $output_dir . 'e2e-test-results-' . $timestamp . '.html';
                break;
                
            case 'json':
                $content = json_encode($results, JSON_PRETTY_PRINT);
                $filename = $output_dir . 'e2e-test-results-' . $timestamp . '.json';
                break;
                
            case 'txt':
            default:
                ob_start();
                $this->print_results($results);
                $content = ob_get_clean();
                $filename = $output_dir . 'e2e-test-results-' . $timestamp . '.txt';
                break;
        }
        
        file_put_contents($filename, $content);
        
        return $filename;
    }
}

/**
 * Run E2E tests from command line
 */
if (php_sapi_name() === 'cli') {
    // Load WordPress
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';
    
    // Initialize test runner
    $runner = new HBL_E2E_Test_Runner();
    
    // Run tests
    $results = $runner->run_all_tests();
    
    // Print results
    $runner->print_results($results);
    
    // Save results
    $report_file = $runner->save_results($results, 'html');
    echo "Test report saved to: " . $report_file . "\n";
    
    // Exit with appropriate code
    exit($results['failed'] > 0 ? 1 : 0);
} 