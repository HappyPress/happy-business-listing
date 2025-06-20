<?php
/**
 * Search and Filters for Happy Business Listing
 * 
 * Provides search and filter functionality for business listings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register search and filter shortcode
 */
function hbl_search_filter_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_type' => 'business_listing',
        'per_page' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
        'show_filters' => 'true',
    ), $atts);
    
    ob_start();
    
    // Get filter options
    $company_types = hbl_get_meta_values('company_type', 'business_listing');
    $locations = hbl_get_meta_values('location', 'business_listing');
    
    // Get current filter values from URL
    $current_type = isset($_GET['company_type']) ? sanitize_text_field($_GET['company_type']) : '';
    $current_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    
    // Display search and filter form
    if ($atts['show_filters'] === 'true') {
        ?>
        <div class="hbl-search-filters">
            <form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="hbl-filter-form">
                <div class="hbl-search-box">
                    <input type="text" name="search" placeholder="<?php esc_attr_e('Search businesses...', 'happy-business-listing'); ?>" value="<?php echo esc_attr($search_term); ?>">
                </div>
                
                <div class="hbl-filters">
                    <div class="hbl-filter">
                        <label for="company_type"><?php _e('Company Type', 'happy-business-listing'); ?></label>
                        <select name="company_type" id="company_type">
                            <option value=""><?php _e('All Types', 'happy-business-listing'); ?></option>
                            <?php foreach ($company_types as $type) : ?>
                                <option value="<?php echo esc_attr($type); ?>" <?php selected($current_type, $type); ?>><?php echo esc_html($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="hbl-filter">
                        <label for="location"><?php _e('Location', 'happy-business-listing'); ?></label>
                        <select name="location" id="location">
                            <option value=""><?php _e('All Locations', 'happy-business-listing'); ?></option>
                            <?php foreach ($locations as $location) : ?>
                                <option value="<?php echo esc_attr($location); ?>" <?php selected($current_location, $location); ?>><?php echo esc_html($location); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="hbl-filter-actions">
                    <button type="submit" class="hbl-filter-button"><?php _e('Apply Filters', 'happy-business-listing'); ?></button>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="hbl-reset-button"><?php _e('Reset', 'happy-business-listing'); ?></a>
                </div>
            </form>
        </div>
        <?php
    }
    
    // Build query args based on filters
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => $atts['post_type'],
        'posts_per_page' => $atts['per_page'],
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'paged' => $paged,
    );
    
    // Add meta query for filters
    $meta_query = array();
    
    if (!empty($current_type)) {
        $meta_query[] = array(
            'key' => 'company_type',
            'value' => $current_type,
            'compare' => '=',
        );
    }
    
    if (!empty($current_location)) {
        $meta_query[] = array(
            'key' => 'location',
            'value' => $current_location,
            'compare' => 'LIKE',
        );
    }
    
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    // Add search term
    if (!empty($search_term)) {
        $args['s'] = $search_term;
    }
    
    // Run the query
    $query = new WP_Query($args);
    
    // Display results
    if ($query->have_posts()) {
        ?>
        <div class="hbl-search-results">
            <div class="hbl-result-count">
                <?php printf(
                    _n(
                        '%s business found',
                        '%s businesses found',
                        $query->found_posts,
                        'happy-business-listing'
                    ),
                    number_format_i18n($query->found_posts)
                ); ?>
            </div>
            
            <div class="hbl-business-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="hbl-business-card">
                        <a href="<?php the_permalink(); ?>" class="hbl-business-link">
                            <div class="hbl-business-image">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('thumbnail', array('class' => 'hbl-business-logo'));
                                } else {
                                    echo '<div class="hbl-business-logo-placeholder"></div>';
                                }
                                ?>
                            </div>
                            <h3 class="hbl-business-title"><?php the_title(); ?></h3>
                            <?php
                            $company_type = get_field('company_type');
                            $location = get_field('location');
                            if ($company_type || $location) :
                            ?>
                            <div class="hbl-business-meta">
                                <?php if ($company_type) : ?>
                                    <span class="hbl-business-type"><?php echo esc_html($company_type); ?></span>
                                <?php endif; ?>
                                <?php if ($location) : ?>
                                    <span class="hbl-business-location"><?php echo esc_html($location); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="hbl-pagination">
                <?php
                echo paginate_links(array(
                    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'format' => '?paged=%#%',
                    'current' => max(1, get_query_var('paged')),
                    'total' => $query->max_num_pages,
                    'prev_text' => '&laquo; ' . __('Previous', 'happy-business-listing'),
                    'next_text' => __('Next', 'happy-business-listing') . ' &raquo;',
                ));
                ?>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="hbl-no-results">
            <p><?php _e('No businesses found matching your criteria.', 'happy-business-listing'); ?></p>
        </div>
        <?php
    }
    
    wp_reset_postdata();
    
    // Add CSS styles
    hbl_search_filter_styles();
    
    return ob_get_clean();
}
add_shortcode('business_search', 'hbl_search_filter_shortcode');

/**
 * Get unique meta values for a specific meta key and post type
 */
function hbl_get_meta_values($meta_key, $post_type) {
    global $wpdb;
    
    $query = $wpdb->prepare(
        "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s
        AND p.post_type = %s
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        ORDER BY pm.meta_value",
        $meta_key,
        $post_type
    );
    
    $values = $wpdb->get_col($query);
    
    return $values;
}

/**
 * Add CSS styles for search and filters
 */
function hbl_search_filter_styles() {
    ?>
    <style>
        .hbl-search-filters {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .hbl-filter-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .hbl-search-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .hbl-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .hbl-filter {
            flex: 1;
            min-width: 200px;
        }
        
        .hbl-filter label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .hbl-filter select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .hbl-filter-actions {
            display: flex;
            gap: 10px;
        }
        
        .hbl-filter-button {
            padding: 10px 20px;
            background-color: #2271b1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .hbl-reset-button {
            padding: 10px 20px;
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
        }
        
        .hbl-business-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .hbl-business-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .hbl-business-card:hover {
            transform: translateY(-5px);
        }
        
        .hbl-business-link {
            display: block;
            text-decoration: none;
            color: inherit;
            padding: 15px;
        }
        
        .hbl-business-image {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            margin-bottom: 15px;
        }
        
        .hbl-business-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .hbl-business-logo-placeholder {
            width: 80px;
            height: 80px;
            background-color: #e0e0e0;
            border-radius: 50%;
        }
        
        .hbl-business-title {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }
        
        .hbl-business-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .hbl-business-type,
        .hbl-business-location {
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .hbl-result-count {
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .hbl-pagination {
            margin-top: 30px;
            text-align: center;
        }
        
        .hbl-pagination .page-numbers {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 3px;
            border: 1px solid #ddd;
            border-radius: 3px;
            text-decoration: none;
        }
        
        .hbl-pagination .current {
            background-color: #2271b1;
            color: white;
            border-color: #2271b1;
        }
        
        .hbl-no-results {
            padding: 30px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .hbl-filters {
                flex-direction: column;
            }
            
            .hbl-filter {
                width: 100%;
            }
        }
    </style>
    <?php
}

/**
 * Register AJAX handler for live search
 */
function hbl_ajax_live_search() {
    // Check nonce for security
    check_ajax_referer('hbl_live_search', 'security');
    
    $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    
    if (empty($search_term)) {
        wp_send_json_error('Search term is required');
        wp_die();
    }
    
    $args = array(
        'post_type' => 'business_listing',
        'posts_per_page' => 5,
        's' => $search_term,
        'post_status' => 'publish',
    );
    
    $query = new WP_Query($args);
    $results = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            $results[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'thumbnail' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') : '',
                'company_type' => get_field('company_type'),
                'location' => get_field('location'),
            );
        }
    }
    
    wp_reset_postdata();
    
    wp_send_json_success($results);
    wp_die();
}
add_action('wp_ajax_hbl_live_search', 'hbl_ajax_live_search');
add_action('wp_ajax_nopriv_hbl_live_search', 'hbl_ajax_live_search');

/**
 * Enqueue scripts for live search
 */
function hbl_enqueue_search_scripts() {
    wp_enqueue_script(
        'hbl-live-search',
        plugins_url('/assets/js/live-search.js', dirname(__FILE__)),
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_localize_script('hbl-live-search', 'hbl_search', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('hbl_live_search'),
    ));
}
add_action('wp_enqueue_scripts', 'hbl_enqueue_search_scripts');