<?php
/**
 * Helper Functions for Happy Business Listing
 * 
 * Contains utility functions used throughout the plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Format price with currency symbol
 *
 * @param float $price The price to format
 * @param string $currency The currency code (optional)
 * @return string Formatted price
 */
function hbl_format_price($price, $currency = '') {
    // Get currency from settings if not provided
    if (empty($currency)) {
        $currency = get_option('hbl_currency', 'USD');
    }
    
    // Format price based on currency
    switch ($currency) {
        case 'INR':
            $formatted = '₹' . number_format($price, 2, '.', ',');
            break;
        case 'EUR':
            $formatted = '€' . number_format($price, 2, '.', ',');
            break;
        case 'GBP':
            $formatted = '£' . number_format($price, 2, '.', ',');
            break;
        case 'JPY':
            $formatted = '¥' . number_format($price, 0, '.', ',');
            break;
        case 'USD':
        default:
            $formatted = '$' . number_format($price, 2, '.', ',');
            break;
    }
    
    return apply_filters('hbl_formatted_price', $formatted, $price, $currency);
}

/**
 * Get field value with ACF fallback
 *
 * @param string $field_name The field name
 * @param int $post_id The post ID
 * @param mixed $default Default value if field is empty
 * @return mixed The field value
 */
function hbl_get_field($field_name, $post_id, $default = '') {
    // Try ACF first if available
    if (function_exists('get_field')) {
        $value = get_field($field_name, $post_id);
        if (!empty($value)) {
            return $value;
        }
    }
    
    // Fallback to post meta
    $value = get_post_meta($post_id, $field_name, true);
    
    // Return default if empty
    return !empty($value) ? $value : $default;
}

/**
 * Update field value with ACF fallback
 *
 * @param string $field_name The field name
 * @param mixed $value The field value
 * @param int $post_id The post ID
 * @return bool True on success, false on failure
 */
function hbl_update_field($field_name, $value, $post_id) {
    // Try ACF first if available
    if (function_exists('update_field')) {
        return update_field($field_name, $value, $post_id);
    }
    
    // Fallback to post meta
    return update_post_meta($post_id, $field_name, $value);
}

/**
 * Sanitize and validate phone number
 *
 * @param string $phone The phone number to sanitize
 * @return string Sanitized phone number
 */
function hbl_sanitize_phone($phone) {
    // Remove all characters except digits, plus sign, hyphen, parentheses, and spaces
    $sanitized = preg_replace('/[^0-9+\-() ]/', '', $phone);
    
    // Ensure the number starts with a plus sign if it contains country code
    if (preg_match('/^[0-9]/', $sanitized)) {
        $sanitized = '+' . $sanitized;
    }
    
    // Remove extra spaces
    $sanitized = preg_replace('/\s+/', ' ', $sanitized);
    
    return $sanitized;
}

/**
 * Check if a string is a valid URL
 *
 * @param string $url The URL to check
 * @return bool True if valid URL, false otherwise
 */
function hbl_is_valid_url($url) {
    // Check if URL starts with http:// or https://
    if (!preg_match('/^https?:\/\//', $url)) {
        return false;
    }
    
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Get plugin asset URL
 *
 * @param string $path The asset path relative to the assets directory
 * @return string The full URL to the asset
 */
function hbl_get_asset_url($path) {
    return HBL_PLUGIN_URL . 'assets/' . ltrim($path, '/');
}

/**
 * Get default logo URL
 *
 * @return string The URL to the default logo
 */
function hbl_get_default_logo_url() {
    return hbl_get_asset_url('img/default-logo.svg');
}

/**
 * Get business listings
 *
 * @param array $args Query arguments
 * @return array Array of business listings
 */
function hbl_get_businesses($args = array()) {
    $defaults = array(
        'post_type' => 'business_listing',
        'posts_per_page' => 10,
        'post_status' => 'publish'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $query = new WP_Query($args);
    
    return $query->posts;
}

/**
 * Get business by user ID
 *
 * @param int $user_id The user ID
 * @return WP_Post|false Business post object or false if not found
 */
function hbl_get_business_by_user($user_id) {
    $args = array(
        'post_type' => 'business_listing',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '='
            )
        )
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0];
    }
    
    return false;
}

/**
 * Check if current user owns a business
 *
 * @param int $business_id The business ID to check (optional)
 * @return bool True if user owns the business, false otherwise
 */
function hbl_user_owns_business($business_id = 0) {
    // Must be logged in
    if (!is_user_logged_in()) {
        return false;
    }
    
    $user_id = get_current_user_id();
    
    // Admins can edit any business
    if (current_user_can('manage_options')) {
        return true;
    }
    
    // If no business ID provided, check if user has any business
    if (empty($business_id)) {
        $user_business = hbl_get_business_by_user($user_id);
        return !empty($user_business);
    }
    
    // Check if user owns this specific business
    $business_user_id = hbl_get_field('user_id', $business_id);
    return $user_id == $business_user_id;
}

/**
 * Log error message if logging is enabled
 *
 * @param string $message The error message
 * @param string $type The error type (error, warning, info)
 */
function hbl_log($message, $type = 'error') {
    // Only log if logging is enabled
    if (get_option('hbl_enable_logging') != '1') {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/hbl-error.log';
    
    // Format message
    $timestamp = date('Y-m-d H:i:s');
    $formatted = "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
    
    // Write to log file
    error_log($formatted, 3, $log_file);
}

/**
 * Get business categories
 *
 * @param array $args Query arguments
 * @return array Array of term objects
 */
function hbl_get_business_categories($args = array()) {
    $defaults = array(
        'taxonomy' => 'business_category',
        'hide_empty' => false
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return get_terms($args);
}

/**
 * Get business locations
 *
 * @param array $args Query arguments
 * @return array Array of term objects
 */
function hbl_get_business_locations($args = array()) {
    $defaults = array(
        'taxonomy' => 'business_location',
        'hide_empty' => false
    );
    
    $args = wp_parse_args($args, $defaults);
    
    return get_terms($args);
}

/**
 * Get services/products for a business
 *
 * @param int $business_id The business ID
 * @param array $args Additional query arguments
 * @return array Array of service/product posts
 */
function hbl_get_business_services($business_id, $args = array()) {
    $defaults = array(
        'post_type' => 'service_product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'business_id',
                'value' => $business_id,
                'compare' => '='
            )
        )
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $query = new WP_Query($args);
    
    return $query->posts;
}

/**
 * Get related businesses
 *
 * @param int $business_id The business ID
 * @param int $limit Number of related businesses to return
 * @return array Array of related business posts
 */
function hbl_get_related_businesses($business_id, $limit = 3) {
    // Get business details
    $company_type = hbl_get_field('company_type', $business_id);
    $location = hbl_get_field('location', $business_id);
    
    // Set up query args
    $args = array(
        'post_type' => 'business_listing',
        'posts_per_page' => $limit,
        'post__not_in' => array($business_id),
        'post_status' => 'publish'
    );
    
    // Add meta query if we have company type or location
    if (!empty($company_type) || !empty($location)) {
        $args['meta_query'] = array('relation' => 'OR');
        
        if (!empty($company_type)) {
            $args['meta_query'][] = array(
                'key' => 'company_type',
                'value' => $company_type,
                'compare' => '='
            );
        }
        
        if (!empty($location)) {
            $args['meta_query'][] = array(
                'key' => 'location',
                'value' => $location,
                'compare' => 'LIKE'
            );
        }
    }
    
    $query = new WP_Query($args);
    
    return $query->posts;
}

/**
 * Get business field with proper fallback
 *
 * @param string $field_name The field name
 * @param int $post_id The post ID
 * @param mixed $default Default value if field is empty
 * @return mixed The field value
 */
function hbl_get_business_field($field_name, $post_id, $default = '') {
    $value = get_post_meta($post_id, $field_name, true);
    
    if (empty($value)) {
        return $default;
    }
    
    return $value;
}

/**
 * Get business meta data
 *
 * @param int $post_id The post ID
 * @return array Array of meta data
 */
function hbl_get_business_meta($post_id) {
    $meta = array();
    
    $fields = array(
        'phone' => __('Phone', 'happy-business-listing'),
        'email' => __('Email', 'happy-business-listing'),
        'website' => __('Website', 'happy-business-listing'),
        'address' => __('Address', 'happy-business-listing'),
        'price' => __('Price', 'happy-business-listing'),
        'hours' => __('Hours', 'happy-business-listing'),
        'category' => __('Category', 'happy-business-listing'),
        'tags' => __('Tags', 'happy-business-listing')
    );
    
    foreach ($fields as $field => $label) {
        $value = hbl_get_business_field($field, $post_id);
        if (!empty($value)) {
            $meta[$field] = array(
                'label' => $label,
                'value' => $value
            );
        }
    }
    
    return $meta;
}