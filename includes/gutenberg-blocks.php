<?php
/**
 * Gutenberg Blocks for Happy Business Listing
 * 
 * Provides custom blocks for displaying business listings and related content
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom Gutenberg blocks
 */
function hbl_register_gutenberg_blocks() {
    // Skip if Gutenberg is not available
    if (!function_exists('register_block_type')) {
        return;
    }
    
    // Check if blocks are enabled in settings
    if (get_option('hbl_activate_blocks') != 1) {
        return;
    }
    
    // Register block scripts
    wp_register_script(
        'hbl-blocks',
        plugins_url('/assets/js/blocks.js', dirname(__FILE__)),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/js/blocks.js')
    );
    
    // Register block styles
    wp_register_style(
        'hbl-blocks-editor',
        plugins_url('/assets/css/blocks-editor.css', dirname(__FILE__)),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/blocks-editor.css')
    );
    
    wp_register_style(
        'hbl-blocks-style',
        plugins_url('/assets/css/blocks-style.css', dirname(__FILE__)),
        array(),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/blocks-style.css')
    );
    
    // Register block types
    register_block_type('hbl/business-listings', array(
        'editor_script' => 'hbl-blocks',
        'editor_style' => 'hbl-blocks-editor',
        'style' => 'hbl-blocks-style',
        'render_callback' => 'hbl_render_business_listings_block',
        'attributes' => array(
            'numberOfItems' => array(
                'type' => 'number',
                'default' => 3,
            ),
            'orderBy' => array(
                'type' => 'string',
                'default' => 'date',
            ),
            'order' => array(
                'type' => 'string',
                'default' => 'desc',
            ),
            'displayFeaturedImage' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'displayExcerpt' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'displayCompanyType' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'displayLocation' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'columns' => array(
                'type' => 'number',
                'default' => 3,
            ),
            'className' => array(
                'type' => 'string',
            ),
        ),
    ));
    
    register_block_type('hbl/business-search', array(
        'editor_script' => 'hbl-blocks',
        'editor_style' => 'hbl-blocks-editor',
        'style' => 'hbl-blocks-style',
        'render_callback' => 'hbl_render_business_search_block',
        'attributes' => array(
            'showFilters' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'className' => array(
                'type' => 'string',
            ),
        ),
    ));
    
    register_block_type('hbl/business-details', array(
        'editor_script' => 'hbl-blocks',
        'editor_style' => 'hbl-blocks-editor',
        'style' => 'hbl-blocks-style',
        'render_callback' => 'hbl_render_business_details_block',
        'attributes' => array(
            'businessId' => array(
                'type' => 'number',
                'default' => 0,
            ),
            'showTitle' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'showImage' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'showDetails' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'showContent' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'className' => array(
                'type' => 'string',
            ),
        ),
    ));
    
    // Set translation for blocks
    if (function_exists('wp_set_script_translations')) {
        wp_set_script_translations('hbl-blocks', 'happy-business-listing');
    }
    
    // Localize script with data for the editor
    wp_localize_script('hbl-blocks', 'hblBlocksData', array(
        'pluginUrl' => plugins_url('', dirname(__FILE__)),
        'restUrl' => rest_url('wp/v2/'),
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('init', 'hbl_register_gutenberg_blocks');

/**
 * Render Business Listings block
 */
function hbl_render_business_listings_block($attributes) {
    $attributes = wp_parse_args($attributes, array(
        'numberOfItems' => 3,
        'orderBy' => 'date',
        'order' => 'desc',
        'displayFeaturedImage' => true,
        'displayExcerpt' => true,
        'displayCompanyType' => true,
        'displayLocation' => true,
        'columns' => 3,
        'className' => '',
    ));
    
    $args = array(
        'post_type' => 'business_listing',
        'posts_per_page' => $attributes['numberOfItems'],
        'orderby' => $attributes['orderBy'],
        'order' => $attributes['order'],
        'post_status' => 'publish',
    );
    
    $query = new WP_Query($args);
    
    ob_start();
    
    if ($query->have_posts()) {
        $class_name = 'hbl-block-business-listings';
        if (!empty($attributes['className'])) {
            $class_name .= ' ' . $attributes['className'];
        }
        
        echo '<div class="' . esc_attr($class_name) . '">';
        echo '<div class="hbl-block-grid" style="grid-template-columns: repeat(' . esc_attr($attributes['columns']) . ', 1fr);">';
        
        while ($query->have_posts()) {
            $query->the_post();
            
            echo '<div class="hbl-block-grid-item">';
            echo '<a href="' . esc_url(get_permalink()) . '" class="hbl-block-business-link">';
            
            if ($attributes['displayFeaturedImage'] && has_post_thumbnail()) {
                echo '<div class="hbl-block-business-image">';
                the_post_thumbnail('medium', array('class' => 'hbl-block-business-thumbnail'));
                echo '</div>';
            }
            
            echo '<h3 class="hbl-block-business-title">' . get_the_title() . '</h3>';
            
            if ($attributes['displayExcerpt']) {
                echo '<div class="hbl-block-business-excerpt">';
                echo wp_trim_words(get_the_excerpt(), 20);
                echo '</div>';
            }
            
            $meta_items = array();
            
            if ($attributes['displayCompanyType']) {
                $company_type = get_field('company_type');
                if ($company_type) {
                    $meta_items[] = '<span class="hbl-block-business-type">' . esc_html($company_type) . '</span>';
                }
            }
            
            if ($attributes['displayLocation']) {
                $location = get_field('location');
                if ($location) {
                    $meta_items[] = '<span class="hbl-block-business-location">' . esc_html($location) . '</span>';
                }
            }
            
            if (!empty($meta_items)) {
                echo '<div class="hbl-block-business-meta">';
                echo implode('', $meta_items);
                echo '</div>';
            }
            
            echo '</a>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p class="hbl-block-no-results">' . __('No business listings found.', 'happy-business-listing') . '</p>';
    }
    
    wp_reset_postdata();
    
    return ob_get_clean();
}

/**
 * Render Business Search block
 */
function hbl_render_business_search_block($attributes) {
    $attributes = wp_parse_args($attributes, array(
        'showFilters' => true,
        'className' => '',
    ));
    
    $shortcode_atts = array(
        'show_filters' => $attributes['showFilters'] ? 'true' : 'false',
    );
    
    $shortcode = '[business_search';
    
    foreach ($shortcode_atts as $key => $value) {
        $shortcode .= ' ' . $key . '="' . $value . '"';
    }
    
    $shortcode .= ']';
    
    return do_shortcode($shortcode);
}

/**
 * Render Business Details block
 */
function hbl_render_business_details_block($attributes) {
    $attributes = wp_parse_args($attributes, array(
        'businessId' => 0,
        'showTitle' => true,
        'showImage' => true,
        'showDetails' => true,
        'showContent' => true,
        'className' => '',
    ));
    
    $business_id = $attributes['businessId'];
    
    // If no business ID is provided, try to get the current post if it's a business listing
    if ($business_id === 0) {
        global $post;
        if (is_singular('business_listing')) {
            $business_id = $post->ID;
        }
    }
    
    if ($business_id === 0) {
        return '<p class="hbl-block-error">' . __('No business listing selected.', 'happy-business-listing') . '</p>';
    }
    
    $business = get_post($business_id);
    
    if (!$business || $business->post_type !== 'business_listing' || $business->post_status !== 'publish') {
        return '<p class="hbl-block-error">' . __('Invalid business listing.', 'happy-business-listing') . '</p>';
    }
    
    ob_start();
    
    $class_name = 'hbl-block-business-details';
    if (!empty($attributes['className'])) {
        $class_name .= ' ' . $attributes['className'];
    }
    
    echo '<div class="' . esc_attr($class_name) . '">';
    
    if ($attributes['showTitle']) {
        echo '<h2 class="hbl-block-business-title">' . get_the_title($business_id) . '</h2>';
    }
    
    if ($attributes['showImage'] && has_post_thumbnail($business_id)) {
        echo '<div class="hbl-block-business-image">';
        echo get_the_post_thumbnail($business_id, 'medium', array('class' => 'hbl-block-business-thumbnail'));
        echo '</div>';
    }
    
    if ($attributes['showDetails']) {
        echo '<div class="hbl-block-business-details">';
        
        $fields = array(
            'company_type' => __('Type of Company', 'happy-business-listing'),
            'gst_no' => __('GST No', 'happy-business-listing'),
            'tan_pan' => __('TAN/PAN', 'happy-business-listing'),
            'location' => __('Location', 'happy-business-listing'),
            'website' => __('Website', 'happy-business-listing'),
            'social_media' => __('Social Media', 'happy-business-listing'),
            'whatsapp_number' => __('WhatsApp Number', 'happy-business-listing'),
        );
        
        foreach ($fields as $field_key => $field_label) {
            $field_value = get_field($field_key, $business_id);
            
            if ($field_value) {
                echo '<div class="hbl-block-business-detail">';
                echo '<span class="hbl-block-detail-label">' . esc_html($field_label) . ':</span>';
                
                if ($field_key === 'website') {
                    echo '<a href="' . esc_url($field_value) . '" target="_blank" class="hbl-block-detail-value">' . esc_html($field_value) . '</a>';
                } elseif ($field_key === 'whatsapp_number') {
                    echo '<a href="https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $field_value)) . '" target="_blank" class="hbl-block-detail-value">' . esc_html($field_value) . '</a>';
                } else {
                    echo '<span class="hbl-block-detail-value">' . esc_html($field_value) . '</span>';
                }
                
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
    
    if ($attributes['showContent']) {
        echo '<div class="hbl-block-business-content">';
        echo apply_filters('the_content', $business->post_content);
        echo '</div>';
    }
    
    echo '</div>';
    
    return ob_get_clean();
}

/**
 * Create block category for Happy Business Listing blocks
 */
function hbl_block_categories($categories, $post) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'happy-business-listing',
                'title' => __('Business Listing', 'happy-business-listing'),
                'icon' => 'store',
            ),
        )
    );
}
add_filter('block_categories_all', 'hbl_block_categories', 10, 2);

/**
 * Enqueue block assets for both editor and front-end
 */
function hbl_enqueue_block_assets() {
    // Skip if blocks are not enabled in settings
    if (get_option('hbl_activate_blocks') != 1) {
        return;
    }
    
    wp_enqueue_style(
        'hbl-blocks-style',
        plugins_url('/assets/css/blocks-style.css', dirname(__FILE__)),
        array(),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/blocks-style.css')
    );
}
add_action('enqueue_block_assets', 'hbl_enqueue_block_assets');

/**
 * Enqueue block editor assets
 */
function hbl_enqueue_block_editor_assets() {
    // Skip if blocks are not enabled in settings
    if (get_option('hbl_activate_blocks') != 1) {
        return;
    }
    
    wp_enqueue_script(
        'hbl-blocks-editor',
        plugins_url('/assets/js/blocks-editor.js', dirname(__FILE__)),
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/js/blocks-editor.js')
    );
    
    wp_enqueue_style(
        'hbl-blocks-editor',
        plugins_url('/assets/css/blocks-editor.css', dirname(__FILE__)),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/blocks-editor.css')
    );
}
add_action('enqueue_block_editor_assets', 'hbl_enqueue_block_editor_assets');
?>
