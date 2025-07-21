<?php
/**
 * Archive Redirect System
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redirect archive to directory page
 */
function hbl_redirect_archive_to_page() {
    if (is_post_type_archive('business_listing')) {
        $directory_page_url = hbl_get_directory_page_url();
        
        // Only redirect if we have a valid directory page and it's different from current URL
        if ($directory_page_url && $directory_page_url !== get_post_type_archive_link('business_listing')) {
            wp_redirect($directory_page_url, 301);
            exit;
        }
    }
}
add_action('template_redirect', 'hbl_redirect_archive_to_page');

/**
 * Update archive links to point to directory page
 */
function hbl_filter_archive_link($link, $post_type) {
    // Prevent infinite loops
    static $processing = false;
    
    if ($processing || $post_type !== 'business_listing') {
        return $link;
    }
    
    $processing = true;
    $directory_page_url = hbl_get_directory_page_url();
    $processing = false;
    
    if ($directory_page_url && $directory_page_url !== $link) {
        return $directory_page_url;
    }
    
    return $link;
}
add_filter('post_type_archive_link', 'hbl_filter_archive_link', 10, 2);

/**
 * Update breadcrumbs and navigation
 */
function hbl_filter_business_archive_title($title) {
    if (is_post_type_archive('business_listing') || hbl_is_directory_page()) {
        $page_id = get_option('hbl_directory_page_id');
        
        if ($page_id && get_post($page_id)) {
            return get_the_title($page_id);
        }
    }
    
    return $title;
}
add_filter('get_the_archive_title', 'hbl_filter_business_archive_title');
add_filter('wp_title', 'hbl_filter_business_archive_title');

/**
 * Update body classes for directory page
 */
function hbl_add_directory_body_class($classes) {
    if (hbl_is_directory_page()) {
        $classes[] = 'hbl-directory-page';
        $classes[] = 'business-listing-archive';
    }
    
    return $classes;
}
add_filter('body_class', 'hbl_add_directory_body_class');

/**
 * Enqueue assets on directory page
 */
function hbl_enqueue_directory_assets() {
    if (hbl_is_directory_page()) {
        // Enqueue business listing styles
        wp_enqueue_style('hbl-business-listing');
        wp_enqueue_script('hbl-business-listing');
        
        // Enqueue HSF assets if available
        if (function_exists('hsf_save_filter')) {
            wp_enqueue_style('hsf-advanced-filter');
            wp_enqueue_script('hsf-advanced-filter');
        }
    }
}
add_action('wp_enqueue_scripts', 'hbl_enqueue_directory_assets');

/**
 * Update canonical URL for directory page
 */
function hbl_directory_canonical_url() {
    if (hbl_is_directory_page()) {
        $page_id = get_option('hbl_directory_page_id');
        
        if ($page_id) {
            echo '<link rel="canonical" href="' . esc_url(get_permalink($page_id)) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'hbl_directory_canonical_url');

/**
 * Handle search results redirection
 */
function hbl_redirect_search_to_directory() {
    if (is_search() && isset($_GET['post_type']) && $_GET['post_type'] === 'business_listing') {
        $directory_page_url = hbl_get_directory_page_url();
        
        if ($directory_page_url) {
            $search_query = get_search_query();
            $redirect_url = add_query_arg('search', urlencode($search_query), $directory_page_url);
            
            wp_redirect($redirect_url, 301);
            exit;
        }
    }
}
add_action('template_redirect', 'hbl_redirect_search_to_directory');

/**
 * Update pagination links on directory page
 */
function hbl_filter_pagination_base($base) {
    if (hbl_is_directory_page()) {
        $page_id = get_option('hbl_directory_page_id');
        
        if ($page_id) {
            return trailingslashit(get_permalink($page_id)) . 'page/%#%/';
        }
    }
    
    return $base;
}
add_filter('paginate_links', 'hbl_filter_pagination_base');
?> 