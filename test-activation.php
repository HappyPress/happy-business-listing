<?php
/**
 * Test script to simulate WordPress plugin activation
 * This helps identify fatal errors during activation
 */

// Simulate WordPress environment
define('ABSPATH', dirname(dirname(dirname(dirname(__DIR__)))) . '/');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

// Mock WordPress functions that might be called during activation
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) { return true; }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) { return true; }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) { return $default; }
}

if (!function_exists('add_option')) {
    function add_option($option, $value, $deprecated = '', $autoload = 'yes') { return true; }
}

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules($hard = true) { return true; }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) { return true; }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '', $filter = 'raw') { return '5.6'; }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) { return basename(dirname($file)) . '/' . basename($file); }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') { return 'http://localhost/wp-admin/' . $path; }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') { return $text; }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') { echo $text; }
}

// Mock action/filter functions
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) { return true; }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) { return true; }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) { return true; }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) { return true; }
}

// Mock post type functions
if (!function_exists('register_post_type')) {
    function register_post_type($post_type, $args = array()) { return true; }
}

if (!function_exists('wp_create_user')) {
    function wp_create_user($username, $password, $email) { return 1; }
}

if (!function_exists('username_exists')) {
    function username_exists($username) { return false; }
}

if (!function_exists('email_exists')) {
    function email_exists($email) { return false; }
}

if (!function_exists('get_role')) {
    function get_role($role) { return (object)['name' => $role]; }
}

if (!function_exists('add_role')) {
    function add_role($role, $display_name, $capabilities = array()) { return true; }
}

// Mock REST API functions
if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = array(), $override = false) { return true; }
}

if (!function_exists('rest_url')) {
    function rest_url($path = '') { return 'http://localhost/wp-json/' . $path; }
}

// Mock cache functions
if (!function_exists('get_transient')) {
    function get_transient($transient) { return false; }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) { return true; }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) { return true; }
}

if (!function_exists('wp_cache_get')) {
    function wp_cache_get($key, $group = '') { return false; }
}

if (!function_exists('wp_cache_set')) {
    function wp_cache_set($key, $data, $group = '', $expire = 0) { return true; }
}

if (!function_exists('wp_cache_delete')) {
    function wp_cache_delete($key, $group = '') { return true; }
}

// Mock file functions
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data, $flags = 0, $context = null) { return true; }
}

if (!function_exists('file_exists')) {
    function file_exists($filename) { return false; }
}

// Mock database functions
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) { return ''; }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') { return true; }
}

if (!function_exists('update_user_meta')) {
    function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') { return true; }
}

// Mock WP_Error class
if (!class_exists('WP_Error')) {
    class WP_Error {
        public function __construct($code = '', $message = '', $data = '') {}
        public function get_error_code() { return ''; }
        public function get_error_message() { return ''; }
        public function get_error_data() { return ''; }
    }
}

// Mock WP_REST_Response class
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        public function __construct($data = null, $status = 200, $headers = array()) {}
    }
}

// Mock WP_User class
if (!class_exists('WP_User')) {
    class WP_User {
        public function __construct($id = 0) {}
        public function set_role($role) { return true; }
    }
}

// Mock WP_REST_Server class
if (!class_exists('WP_REST_Server')) {
    class WP_REST_Server {
        const READABLE = 'GET';
        const CREATABLE = 'POST';
        const EDITABLE = 'PUT';
        const DELETABLE = 'DELETE';
    }
}

// Mock WP_REST_Request class
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        public function get_param($key) { return ''; }
        public function get_params() { return array(); }
    }
}

// Test the plugin activation
echo "Testing plugin activation...\n";

try {
    // Include the main plugin file
    require_once 'happy-business-listing.php';
    
    // Try to get the plugin instance
    $plugin = Happy_Business_Listing::get_instance();
    
    echo "✅ Plugin loaded successfully!\n";
    echo "✅ Plugin instance created!\n";
    
    // Test activation method
    try {
        $plugin->activate();
        echo "✅ Plugin activation completed!\n";
    } catch (Exception $e) {
        echo "❌ Activation error: " . $e->getMessage() . "\n";
    }
    
} catch (ParseError $e) {
    echo "❌ Parse error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nTest completed.\n"; 