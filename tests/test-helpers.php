<?php
/**
 * Test helpers for Happy Business Listing
 *
 * @package Happy_Business_Listing
 */

/**
 * Class HBL_Test_Helpers
 */
class HBL_Test_Helpers {
    /**
     * Create a test business listing
     *
     * @param array $args Custom arguments for the business listing
     * @return int Post ID of the created business listing
     */
    public static function create_test_business($args = array()) {
        $defaults = array(
            'post_title' => 'Test Business',
            'post_content' => 'This is a test business listing.',
            'post_status' => 'publish',
            'post_type' => 'business_listing',
            'meta_input' => array(
                'business_name' => 'Test Business',
                'company_type' => 'Pvt Ltd',
                'gst_no' => 'GST12345678',
                'tan_pan' => 'TAN12345678',
                'location' => 'Test Location',
                'website' => 'https://example.com',
                'social_media_handles' => 'facebook: https://facebook.com/testbusiness, twitter: https://twitter.com/testbusiness',
                'whatsapp_number' => '+1234567890',
                'email' => 'test@example.com',
                'contact_name' => 'Test Contact',
                'phone' => '+1234567890',
                'verification_status' => 'verified'
            )
        );

        $args = wp_parse_args($args, $defaults);
        
        return wp_insert_post($args);
    }
    
    /**
     * Create a test user
     *
     * @param array $args Custom arguments for the user
     * @return int User ID of the created user
     */
    public static function create_test_user($args = array()) {
        $defaults = array(
            'user_login' => 'testuser' . time(),
            'user_pass' => 'password',
            'user_email' => 'testuser' . time() . '@example.com',
            'role' => 'business_user'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return wp_insert_user($args);
    }
    
    /**
     * Create a test service/product
     *
     * @param int $business_id The business ID
     * @param array $args Custom arguments for the service/product
     * @return int Post ID of the created service/product
     */
    public static function create_test_service($business_id, $args = array()) {
        $defaults = array(
            'post_title' => 'Test Service',
            'post_content' => 'This is a test service.',
            'post_status' => 'publish',
            'post_type' => 'service_product',
            'meta_input' => array(
                'business_id' => $business_id,
                'price' => '100',
                'service_type' => 'service'
            )
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return wp_insert_post($args);
    }
    
    /**
     * Create a test lead
     *
     * @param int $business_id The business ID
     * @param array $args Custom arguments for the lead
     * @return int Post ID of the created lead
     */
    public static function create_test_lead($business_id, $args = array()) {
        $defaults = array(
            'post_title' => 'Test Lead',
            'post_content' => 'This is a test lead.',
            'post_status' => 'publish',
            'post_type' => 'lead',
            'meta_input' => array(
                'business_id' => $business_id,
                'lead_name' => 'Test Lead',
                'lead_email' => 'lead@example.com',
                'lead_phone' => '+1234567890',
                'lead_details' => 'This is a test lead.',
                'lead_status' => 'new'
            )
        );
        
        $args = wp_parse_args($args, $defaults);
        
        return wp_insert_post($args);
    }
    
    /**
     * Mock the WordPress functions that interact with the database
     */
    public static function mock_wp_functions() {
        // Mock get_option
        if (!function_exists('get_option')) {
            function get_option($option, $default = false) {
                static $options = array(
                    'hbl_enable_logging' => '1',
                    'hbl_enable_subsite_creation' => '1',
                    'hbl_twilio_account_sid' => 'test_account_sid',
                    'hbl_twilio_auth_token' => 'test_auth_token',
                    'hbl_twilio_from_number' => '+1234567890',
                    'hbl_whatsapp_business_api' => 'test_api_key',
                    'hbl_whatsapp_business_phone_id' => 'test_phone_id',
                    'hbl_welcome_message' => 'Welcome to our business listing service!',
                    'hbl_enable_rate_limiting' => '1',
                    'hbl_rate_limit_threshold' => '10',
                    'hbl_rate_limit_period' => '60',
                    'hbl_enable_security_headers' => '1',
                    'hbl_enable_security_logging' => '1',
                    'hbl_archive_posts_per_page' => '12',
                    'hbl_archive_orderby' => 'date',
                    'hbl_archive_order' => 'DESC',
                    'hbl_currency' => 'USD'
                );
                
                return isset($options[$option]) ? $options[$option] : $default;
            }
        }
        
        // Mock update_option
        if (!function_exists('update_option')) {
            function update_option($option, $value, $autoload = null) {
                return true;
            }
        }
        
        // Mock get_post_meta
        if (!function_exists('get_post_meta')) {
            function get_post_meta($post_id, $key, $single = false) {
                static $post_meta = array();
                
                if (!isset($post_meta[$post_id])) {
                    $post_meta[$post_id] = array();
                }
                
                if (!isset($post_meta[$post_id][$key])) {
                    return $single ? '' : array();
                }
                
                return $single ? $post_meta[$post_id][$key] : array($post_meta[$post_id][$key]);
            }
        }
        
        // Mock update_post_meta
        if (!function_exists('update_post_meta')) {
            function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') {
                static $post_meta = array();
                
                if (!isset($post_meta[$post_id])) {
                    $post_meta[$post_id] = array();
                }
                
                $post_meta[$post_id][$meta_key] = $meta_value;
                
                return true;
            }
        }
        
        // Mock get_user_meta
        if (!function_exists('get_user_meta')) {
            function get_user_meta($user_id, $key, $single = false) {
                static $user_meta = array();
                
                if (!isset($user_meta[$user_id])) {
                    $user_meta[$user_id] = array();
                }
                
                if (!isset($user_meta[$user_id][$key])) {
                    return $single ? '' : array();
                }
                
                return $single ? $user_meta[$user_id][$key] : array($user_meta[$user_id][$key]);
            }
        }
        
        // Mock update_user_meta
        if (!function_exists('update_user_meta')) {
            function update_user_meta($user_id, $meta_key, $meta_value, $prev_value = '') {
                static $user_meta = array();
                
                if (!isset($user_meta[$user_id])) {
                    $user_meta[$user_id] = array();
                }
                
                $user_meta[$user_id][$meta_key] = $meta_value;
                
                return true;
            }
        }
    }
    
    /**
     * Reset the test environment
     */
    public static function reset_test_environment() {
        global $wpdb;
        
        // Delete all posts
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type IN ('business_listing', 'service_product', 'lead')");
        
        // Delete all users except admin
        $wpdb->query("DELETE FROM {$wpdb->users} WHERE ID > 1");
        
        // Delete all user meta
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE user_id > 1");
        
        // Delete all post meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta}");
        
        // Reset options
        delete_option('hbl_rate_limits');
        delete_option('hbl_bad_ips');
    }
}