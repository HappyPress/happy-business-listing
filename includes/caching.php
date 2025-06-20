<?php
/**
 * Caching System for Happy Business Listing
 *
 * Provides caching mechanisms for improved performance.
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache class for Happy Business Listing
 */
class HBL_Cache {
    /**
     * Cache group name
     */
    const CACHE_GROUP = 'hbl_cache';
    
    /**
     * Default cache expiration time (1 hour)
     */
    const DEFAULT_EXPIRATION = 3600;
    
    /**
     * Cache keys
     */
    const CACHE_KEYS = array(
        'business_list' => 'hbl_business_list_',
        'business_single' => 'hbl_business_single_',
        'services_list' => 'hbl_services_list_',
        'leads_list' => 'hbl_leads_list_',
        'search_results' => 'hbl_search_results_',
        'stats' => 'hbl_stats',
        'filters' => 'hbl_filters',
        'whatsapp_status' => 'hbl_whatsapp_status'
    );
    
    /**
     * Get cached data
     *
     * @param string $key Cache key
     * @param string $group Cache group (optional)
     * @return mixed|false Cached data or false if not found
     */
    public static function get($key, $group = '') {
        $cache_key = self::build_cache_key($key, $group);
        
        // Try WordPress transients first
        $data = get_transient($cache_key);
        
        if ($data !== false) {
            return $data;
        }
        
        // Try object cache if available
        if (wp_cache_get($cache_key, self::CACHE_GROUP) !== false) {
            $data = wp_cache_get($cache_key, self::CACHE_GROUP);
            return $data;
        }
        
        return false;
    }
    
    /**
     * Set cached data
     *
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration time in seconds
     * @param string $group Cache group (optional)
     * @return bool True on success, false on failure
     */
    public static function set($key, $data, $expiration = self::DEFAULT_EXPIRATION, $group = '') {
        $cache_key = self::build_cache_key($key, $group);
        
        // Set WordPress transient
        $transient_result = set_transient($cache_key, $data, $expiration);
        
        // Set object cache if available
        $object_cache_result = wp_cache_set($cache_key, $data, self::CACHE_GROUP, $expiration);
        
        return $transient_result || $object_cache_result;
    }
    
    /**
     * Delete cached data
     *
     * @param string $key Cache key
     * @param string $group Cache group (optional)
     * @return bool True on success, false on failure
     */
    public static function delete($key, $group = '') {
        $cache_key = self::build_cache_key($key, $group);
        
        // Delete WordPress transient
        $transient_result = delete_transient($cache_key);
        
        // Delete object cache if available
        $object_cache_result = wp_cache_delete($cache_key, self::CACHE_GROUP);
        
        return $transient_result || $object_cache_result;
    }
    
    /**
     * Clear all plugin cache
     *
     * @return bool True on success, false on failure
     */
    public static function clear_all() {
        $success = true;
        
        // Clear all transients with our prefix
        global $wpdb;
        
        $transients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_KEYS['business_list'] . '%'
            )
        );
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient);
            delete_transient($key);
        }
        
        // Clear object cache group if available
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group(self::CACHE_GROUP);
        }
        
        // Log cache clearing
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Cache cleared',
                'info',
                array(
                    'user_id' => get_current_user_id(),
                    'transients_cleared' => count($transients)
            ));
        }
        
        return $success;
    }
    
    /**
     * Build cache key
     *
     * @param string $key Base key
     * @param string $group Group name
     * @return string Full cache key
     */
    private static function build_cache_key($key, $group = '') {
        $cache_key = $key;
        
        if (!empty($group)) {
            $cache_key = $group . '_' . $cache_key;
        }
        
        // Add version for cache invalidation
        $cache_key .= '_v' . HBL_VERSION;
        
        return $cache_key;
    }
    
    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public static function get_stats() {
        global $wpdb;
        
        $stats = array(
            'total_transients' => 0,
            'plugin_transients' => 0,
            'cache_hits' => get_option('hbl_cache_hits', 0),
            'cache_misses' => get_option('hbl_cache_misses', 0),
            'cache_ratio' => 0
        );
        
        // Count total transients
        $total_transients = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'"
        );
        $stats['total_transients'] = $total_transients;
        
        // Count plugin transients
        $plugin_transients = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_KEYS['business_list'] . '%'
            )
        );
        $stats['plugin_transients'] = $plugin_transients;
        
        // Calculate cache ratio
        $total_requests = $stats['cache_hits'] + $stats['cache_misses'];
        if ($total_requests > 0) {
            $stats['cache_ratio'] = round(($stats['cache_hits'] / $total_requests) * 100, 2);
        }
        
        return $stats;
    }
    
    /**
     * Increment cache hit counter
     */
    public static function increment_hits() {
        $hits = get_option('hbl_cache_hits', 0);
        update_option('hbl_cache_hits', $hits + 1);
    }
    
    /**
     * Increment cache miss counter
     */
    public static function increment_misses() {
        $misses = get_option('hbl_cache_misses', 0);
        update_option('hbl_cache_misses', $misses + 1);
    }
}

/**
 * Cache business listings
 *
 * @param array $args Query arguments
 * @param int $expiration Cache expiration time
 * @return array|false Cached business listings or false
 */
function hbl_cache_business_listings($args = array(), $expiration = 3600) {
    $cache_key = 'business_list_' . md5(serialize($args));
    
    // Try to get from cache
    $cached_data = HBL_Cache::get($cache_key, 'businesses');
    
    if ($cached_data !== false) {
        HBL_Cache::increment_hits();
        return $cached_data;
    }
    
    HBL_Cache::increment_misses();
    
    // Query businesses
    $default_args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $query_args = wp_parse_args($args, $default_args);
    $query = new WP_Query($query_args);
    
    $businesses = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $businesses[] = hbl_format_business_for_cache(get_post());
        }
    }
    wp_reset_postdata();
    
    $data = array(
        'businesses' => $businesses,
        'total' => $query->found_posts,
        'max_pages' => $query->max_num_pages,
        'cached_at' => current_time('timestamp')
    );
    
    // Cache the data
    HBL_Cache::set($cache_key, $data, $expiration, 'businesses');
    
    return $data;
}

/**
 * Cache single business
 *
 * @param int $business_id Business ID
 * @param int $expiration Cache expiration time
 * @return array|false Cached business data or false
 */
function hbl_cache_business_single($business_id, $expiration = 3600) {
    $cache_key = 'business_single_' . $business_id;
    
    // Try to get from cache
    $cached_data = HBL_Cache::get($cache_key, 'businesses');
    
    if ($cached_data !== false) {
        HBL_Cache::increment_hits();
        return $cached_data;
    }
    
    HBL_Cache::increment_misses();
    
    // Get business data
    $business = get_post($business_id);
    
    if (!$business || $business->post_type !== 'business_listing') {
        return false;
    }
    
    $data = hbl_format_business_for_cache($business);
    $data['cached_at'] = current_time('timestamp');
    
    // Cache the data
    HBL_Cache::set($cache_key, $data, $expiration, 'businesses');
    
    return $data;
}

/**
 * Cache search results
 *
 * @param string $search_query Search query
 * @param array $filters Search filters
 * @param int $expiration Cache expiration time
 * @return array|false Cached search results or false
 */
function hbl_cache_search_results($search_query, $filters = array(), $expiration = 1800) {
    $cache_key = 'search_results_' . md5($search_query . serialize($filters));
    
    // Try to get from cache
    $cached_data = HBL_Cache::get($cache_key, 'search');
    
    if ($cached_data !== false) {
        HBL_Cache::increment_hits();
        return $cached_data;
    }
    
    HBL_Cache::increment_misses();
    
    // Perform search
    $args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        's' => $search_query,
        'posts_per_page' => 10
    );
    
    // Add filters
    if (!empty($filters)) {
        $meta_query = array();
        
        if (!empty($filters['location'])) {
            $meta_query[] = array(
                'key' => 'location',
                'value' => $filters['location'],
                'compare' => 'LIKE'
            );
        }
        
        if (!empty($filters['company_type'])) {
            $meta_query[] = array(
                'key' => 'company_type',
                'value' => $filters['company_type'],
                'compare' => '='
            );
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
    }
    
    $query = new WP_Query($args);
    
    $results = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = hbl_format_business_for_cache(get_post());
        }
    }
    wp_reset_postdata();
    
    $data = array(
        'results' => $results,
        'total' => $query->found_pages,
        'search_query' => $search_query,
        'filters' => $filters,
        'cached_at' => current_time('timestamp')
    );
    
    // Cache the data
    HBL_Cache::set($cache_key, $data, $expiration, 'search');
    
    return $data;
}

/**
 * Cache plugin statistics
 *
 * @param int $expiration Cache expiration time
 * @return array|false Cached statistics or false
 */
function hbl_cache_stats($expiration = 3600) {
    $cache_key = 'stats';
    
    // Try to get from cache
    $cached_data = HBL_Cache::get($cache_key, 'stats');
    
    if ($cached_data !== false) {
        HBL_Cache::increment_hits();
        return $cached_data;
    }
    
    HBL_Cache::increment_misses();
    
    // Calculate statistics
    $business_count = wp_count_posts('business_listing');
    $service_count = wp_count_posts('service_product');
    $lead_count = wp_count_posts('lead');
    
    $stats = array(
        'businesses' => array(
            'total' => $business_count->publish,
            'draft' => $business_count->draft,
            'pending' => $business_count->pending
        ),
        'services' => array(
            'total' => $service_count->publish,
            'draft' => $service_count->draft
        ),
        'leads' => array(
            'total' => $lead_count->publish,
            'new' => $lead_count->publish
        ),
        'users' => array(
            'business_users' => count_users()['avail_roles']['business_user'] ?? 0
        ),
        'cache' => HBL_Cache::get_stats(),
        'cached_at' => current_time('timestamp')
    );
    
    // Cache the data
    HBL_Cache::set($cache_key, $stats, $expiration, 'stats');
    
    return $stats;
}

/**
 * Cache available filters
 *
 * @param int $expiration Cache expiration time
 * @return array|false Cached filters or false
 */
function hbl_cache_filters($expiration = 7200) {
    $cache_key = 'filters';
    
    // Try to get from cache
    $cached_data = HBL_Cache::get($cache_key, 'filters');
    
    if ($cached_data !== false) {
        HBL_Cache::increment_hits();
        return $cached_data;
    }
    
    HBL_Cache::increment_misses();
    
    // Get available filters
    global $wpdb;
    
    $locations = $wpdb->get_col(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
         WHERE meta_key = 'location' 
         AND meta_value != '' 
         ORDER BY meta_value ASC"
    );
    
    $company_types = $wpdb->get_col(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
         WHERE meta_key = 'company_type' 
         AND meta_value != '' 
         ORDER BY meta_value ASC"
    );
    
    $filters = array(
        'locations' => $locations,
        'company_types' => $company_types,
        'cached_at' => current_time('timestamp')
    );
    
    // Cache the data
    HBL_Cache::set($cache_key, $filters, $expiration, 'filters');
    
    return $filters;
}

/**
 * Format business for caching
 *
 * @param WP_Post $business Business post object
 * @return array Formatted business data
 */
function hbl_format_business_for_cache($business) {
    $meta = get_post_meta($business->ID);
    
    return array(
        'id' => $business->ID,
        'title' => $business->post_title,
        'content' => $business->post_content,
        'excerpt' => $business->post_excerpt,
        'date' => $business->post_date,
        'modified' => $business->post_modified,
        'status' => $business->post_status,
        'slug' => $business->post_name,
        'link' => get_permalink($business->ID),
        'featured_image' => get_the_post_thumbnail_url($business->ID, 'full'),
        'meta' => array(
            'business_name' => $meta['business_name'][0] ?? '',
            'company_type' => $meta['company_type'][0] ?? '',
            'gst_no' => $meta['gst_no'][0] ?? '',
            'tan_pan' => $meta['tan_pan'][0] ?? '',
            'location' => $meta['location'][0] ?? '',
            'website' => $meta['website'][0] ?? '',
            'social_media_handles' => $meta['social_media_handles'][0] ?? '',
            'whatsapp_number' => $meta['whatsapp_number'][0] ?? '',
            'email' => $meta['email'][0] ?? '',
            'contact_name' => $meta['contact_name'][0] ?? '',
            'phone' => $meta['phone'][0] ?? '',
            'user_id' => $meta['user_id'][0] ?? ''
        )
    );
}

/**
 * Clear cache when business is updated
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an update
 */
function hbl_clear_business_cache($post_id, $post, $update) {
    if ($post->post_type !== 'business_listing') {
        return;
    }
    
    // Clear business-specific cache
    HBL_Cache::delete('business_single_' . $post_id, 'businesses');
    
    // Clear list cache
    HBL_Cache::delete('business_list_', 'businesses');
    
    // Clear search cache
    HBL_Cache::delete('search_results_', 'search');
    
    // Clear stats cache
    HBL_Cache::delete('stats', 'stats');
    
    // Clear filters cache
    HBL_Cache::delete('filters', 'filters');
    
    // Log cache clearing
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business cache cleared',
            'info',
            array(
                'business_id' => $post_id,
                'action' => $update ? 'update' : 'create'
            )
        );
    }
}
add_action('save_post', 'hbl_clear_business_cache', 10, 3);

/**
 * Clear cache when business is deleted
 *
 * @param int $post_id Post ID
 */
function hbl_clear_business_cache_on_delete($post_id) {
    $post = get_post($post_id);
    
    if ($post && $post->post_type === 'business_listing') {
        hbl_clear_business_cache($post_id, $post, true);
    }
}
add_action('deleted_post', 'hbl_clear_business_cache_on_delete');

/**
 * Clear cache when service is updated
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an update
 */
function hbl_clear_service_cache($post_id, $post, $update) {
    if ($post->post_type !== 'service_product') {
        return;
    }
    
    // Clear service list cache
    HBL_Cache::delete('services_list_', 'services');
    
    // Clear stats cache
    HBL_Cache::delete('stats', 'stats');
}
add_action('save_post', 'hbl_clear_service_cache', 10, 3);

/**
 * Clear cache when lead is updated
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an update
 */
function hbl_clear_lead_cache($post_id, $post, $update) {
    if ($post->post_type !== 'lead') {
        return;
    }
    
    // Clear lead list cache
    HBL_Cache::delete('leads_list_', 'leads');
    
    // Clear stats cache
    HBL_Cache::delete('stats', 'stats');
}
add_action('save_post', 'hbl_clear_lead_cache', 10, 3);

/**
 * Add cache settings to admin
 */
function hbl_add_cache_settings() {
    add_settings_section(
        'hbl_cache_section',
        __('Cache Settings', 'happy-business-listing'),
        'hbl_cache_section_callback',
        'hbl_settings'
    );
    
    add_settings_field(
        'hbl_enable_caching',
        __('Enable Caching', 'happy-business-listing'),
        'hbl_enable_caching_callback',
        'hbl_settings',
        'hbl_cache_section'
    );
    
    add_settings_field(
        'hbl_cache_expiration',
        __('Cache Expiration (seconds)', 'happy-business-listing'),
        'hbl_cache_expiration_callback',
        'hbl_settings',
        'hbl_cache_section'
    );
    
    register_setting('hbl_options', 'hbl_enable_caching');
    register_setting('hbl_options', 'hbl_cache_expiration', 'absint');
}

/**
 * Cache section callback
 */
function hbl_cache_section_callback() {
    echo '<p>' . __('Configure caching settings for improved performance.', 'happy-business-listing') . '</p>';
}

/**
 * Enable caching callback
 */
function hbl_enable_caching_callback() {
    $enabled = get_option('hbl_enable_caching', '1');
    ?>
    <input type="checkbox" id="hbl_enable_caching" name="hbl_enable_caching" value="1" <?php checked('1', $enabled); ?> />
    <label for="hbl_enable_caching"><?php _e('Enable caching for business listings and search results', 'happy-business-listing'); ?></label>
    <?php
}

/**
 * Cache expiration callback
 */
function hbl_cache_expiration_callback() {
    $expiration = get_option('hbl_cache_expiration', 3600);
    ?>
    <input type="number" id="hbl_cache_expiration" name="hbl_cache_expiration" value="<?php echo esc_attr($expiration); ?>" min="300" max="86400" />
    <p class="description"><?php _e('Cache expiration time in seconds (300-86400). Default: 3600 (1 hour).', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Add cache management to admin
 */
function hbl_add_cache_management() {
    add_submenu_page(
        'edit.php?post_type=business_listing',
        __('Cache Management', 'happy-business-listing'),
        __('Cache', 'happy-business-listing'),
        'manage_options',
        'hbl_cache',
        'hbl_cache_management_page'
    );
}

/**
 * Cache management page
 */
function hbl_cache_management_page() {
    if (isset($_POST['hbl_clear_cache']) && wp_verify_nonce($_POST['hbl_cache_nonce'], 'hbl_clear_cache')) {
        HBL_Cache::clear_all();
        echo '<div class="notice notice-success"><p>' . __('Cache cleared successfully.', 'happy-business-listing') . '</p></div>';
    }
    
    $stats = HBL_Cache::get_stats();
    ?>
    <div class="wrap">
        <h1><?php _e('Cache Management', 'happy-business-listing'); ?></h1>
        
        <div class="card">
            <h2><?php _e('Cache Statistics', 'happy-business-listing'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Total Transients', 'happy-business-listing'); ?></th>
                    <td><?php echo number_format($stats['total_transients']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Plugin Transients', 'happy-business-listing'); ?></th>
                    <td><?php echo number_format($stats['plugin_transients']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Cache Hits', 'happy-business-listing'); ?></th>
                    <td><?php echo number_format($stats['cache_hits']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Cache Misses', 'happy-business-listing'); ?></th>
                    <td><?php echo number_format($stats['cache_misses']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Cache Hit Ratio', 'happy-business-listing'); ?></th>
                    <td><?php echo $stats['cache_ratio']; ?>%</td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2><?php _e('Cache Actions', 'happy-business-listing'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('hbl_clear_cache', 'hbl_cache_nonce'); ?>
                <p><?php _e('Clear all cached data for the plugin.', 'happy-business-listing'); ?></p>
                <input type="submit" name="hbl_clear_cache" class="button button-primary" value="<?php _e('Clear All Cache', 'happy-business-listing'); ?>" />
            </form>
        </div>
    </div>
    <?php
}

// Initialize cache settings
add_action('admin_init', 'hbl_add_cache_settings');
add_action('admin_menu', 'hbl_add_cache_management'); 