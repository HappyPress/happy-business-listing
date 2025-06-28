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
    return preg_replace('/[^0-9+\-() ]/', '', $phone);
}

/**
 * Check if a string is a valid URL
 *
 * @param string $url The URL to check
 * @return bool True if valid URL, false otherwise
 */
function hbl_is_valid_url($url) {
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
function hbl_get_related_businesses_legacy($business_id, $limit = 3) {
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
 * Get related business listings
 *
 * @param int $post_id The business listing post ID
 * @param array $args Optional arguments
 * @return array Array of related business listings
 */
function hbl_get_related_businesses($post_id, $args = array()) {
    // Default arguments
    $defaults = array(
        'posts_per_page' => 4,
        'exclude' => array($post_id),
        'orderby' => 'rand',
        'order' => 'DESC',
        'relationship' => 'category', // 'category', 'tag', 'location', 'company_type', or 'all'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Cache key
    $cache_key = 'related_businesses_' . $post_id . '_' . md5(serialize($args));
    
    // Try to get from cache first
    $cached_data = HBL_Cache::get($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Get the current business data
    $business = get_post($post_id);
    if (!$business || $business->post_type !== 'business_listing') {
        return array();
    }
    
    // Base query args
    $query_args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        'posts_per_page' => $args['posts_per_page'],
        'post__not_in' => $args['exclude'],
        'orderby' => $args['orderby'],
        'order' => $args['order'],
    );
    
    // Different relationship types
    if ($args['relationship'] === 'category' || $args['relationship'] === 'all') {
        // Get categories of the current business
        $categories = wp_get_post_terms($post_id, 'business_category', array('fields' => 'ids'));
        
        if (!empty($categories) && !is_wp_error($categories)) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'business_category',
                'field' => 'id',
                'terms' => $categories,
            );
        }
    }
    
    if ($args['relationship'] === 'tag' || $args['relationship'] === 'all') {
        // Get tags of the current business
        $tags = wp_get_post_terms($post_id, 'business_tag', array('fields' => 'ids'));
        
        if (!empty($tags) && !is_wp_error($tags)) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'business_tag',
                'field' => 'id',
                'terms' => $tags,
            );
        }
    }
    
    if ($args['relationship'] === 'location' || $args['relationship'] === 'all') {
        // Get location of the current business
        $location = get_post_meta($post_id, 'location', true);
        
        if (!empty($location)) {
            $query_args['meta_query'][] = array(
                'key' => 'location',
                'value' => $location,
                'compare' => 'LIKE',
            );
        }
    }
    
    if ($args['relationship'] === 'company_type' || $args['relationship'] === 'all') {
        // Get company type of the current business
        $company_type = get_post_meta($post_id, 'company_type', true);
        
        if (!empty($company_type)) {
            $query_args['meta_query'][] = array(
                'key' => 'company_type',
                'value' => $company_type,
                'compare' => '=',
            );
        }
    }
    
    // If we're using multiple relationship types, add the relation parameter
    if ($args['relationship'] === 'all' && isset($query_args['tax_query']) && isset($query_args['meta_query'])) {
        $query_args['relation'] = 'OR';
    }
    
    // Run the query
    $query = new WP_Query($query_args);
    
    // Format the results
    $related_businesses = array();
    foreach ($query->posts as $related_post) {
        $related_businesses[] = array(
            'id' => $related_post->ID,
            'title' => $related_post->post_title,
            'permalink' => get_permalink($related_post->ID),
            'thumbnail' => get_the_post_thumbnail_url($related_post->ID, 'thumbnail'),
            'company_type' => get_post_meta($related_post->ID, 'company_type', true),
            'location' => get_post_meta($related_post->ID, 'location', true),
            'rating' => get_post_meta($related_post->ID, 'rating', true),
        );
    }
    
    // Cache the results
    HBL_Cache::set($cache_key, $related_businesses, 3600); // Cache for 1 hour
    
    return $related_businesses;
}

/**
 * Get businesses by city
 *
 * @param string $city The city name
 * @param array $args Optional arguments
 * @return array Array of businesses in the specified city
 */
function hbl_get_businesses_by_city($city, $args = array()) {
    // Default arguments
    $defaults = array(
        'posts_per_page' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Cache key
    $cache_key = 'businesses_city_' . sanitize_title($city) . '_' . md5(serialize($args));
    
    // Try to get from cache first
    $cached_data = HBL_Cache::get($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Query args
    $query_args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        'posts_per_page' => $args['posts_per_page'],
        'orderby' => $args['orderby'],
        'order' => $args['order'],
        'meta_query' => array(
            array(
                'key' => 'city',
                'value' => $city,
                'compare' => '=',
            ),
        ),
    );
    
    // Run the query
    $query = new WP_Query($query_args);
    
    // Format the results
    $businesses = array();
    foreach ($query->posts as $post) {
        $businesses[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'permalink' => get_permalink($post->ID),
            'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
            'company_type' => get_post_meta($post->ID, 'company_type', true),
            'location' => get_post_meta($post->ID, 'location', true),
            'rating' => get_post_meta($post->ID, 'rating', true),
        );
    }
    
    // Cache the results
    HBL_Cache::set($cache_key, $businesses, 3600); // Cache for 1 hour
    
    return $businesses;
}