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

/**
 * Register Advanced Filtering Block
 */
function hbl_register_advanced_filter_block() {
    // Skip if Gutenberg is not available
    if (!function_exists('register_block_type')) {
        return;
    }
    
    // Register block script
    wp_register_script(
        'hbl-advanced-filter-block',
        plugins_url('assets/js/blocks/advanced-filter-block.js', HBL_PLUGIN_FILE),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'jquery'),
        HBL_VERSION
    );
    
    // Register block styles
    wp_register_style(
        'hbl-advanced-filter-block',
        plugins_url('assets/css/blocks/advanced-filter-block.css', HBL_PLUGIN_FILE),
        array(),
        HBL_VERSION
    );
    
    // Register the block
    register_block_type('happy-business-listing/advanced-filter', array(
        'editor_script' => 'hbl-advanced-filter-block',
        'editor_style' => 'hbl-advanced-filter-block',
        'render_callback' => 'hbl_render_advanced_filter_block',
        'attributes' => array(
            'title' => array(
                'type' => 'string',
                'default' => __('Find Businesses', 'happy-business-listing')
            ),
            'layout' => array(
                'type' => 'string',
                'default' => 'horizontal'
            ),
            'showKeywordSearch' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showLocationFilter' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showCategoryFilter' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showCompanyTypeFilter' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showRatingFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showPriceRangeFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showVerifiedFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showSorting' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'resultsPerPage' => array(
                'type' => 'number',
                'default' => 10
            ),
            'showDateRangeFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showServiceFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showDistanceFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showTagsFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'showOpenNowFilter' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'maxDistanceOptions' => array(
                'type' => 'string',
                'default' => '5,10,25,50,100'
            ),
            'defaultDistanceUnit' => array(
                'type' => 'string',
                'default' => 'km'
            ),
            'enableAutoSubmit' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'enableSavedFilters' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'filterStyle' => array(
                'type' => 'string',
                'default' => 'standard'
            ),
            'showFilterToggle' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'className' => array(
                'type' => 'string'
            )
        )
    ));
}
add_action('init', 'hbl_register_advanced_filter_block');

/**
 * Render Advanced Filter Block
 *
 * @param array $attributes Block attributes
 * @return string Block output
 */
function hbl_render_advanced_filter_block($attributes) {
    // Default attributes
    $attributes = wp_parse_args($attributes, array(
        'title' => __('Find Businesses', 'happy-business-listing'),
        'layout' => 'horizontal',
        'showKeywordSearch' => true,
        'showLocationFilter' => true,
        'showCategoryFilter' => true,
        'showCompanyTypeFilter' => true,
        'showRatingFilter' => false,
        'showPriceRangeFilter' => false,
        'showVerifiedFilter' => false,
        'showSorting' => true,
        'resultsPerPage' => 10,
        'showDateRangeFilter' => false,
        'showServiceFilter' => false,
        'showDistanceFilter' => false,
        'showTagsFilter' => false,
        'showOpenNowFilter' => false,
        'maxDistanceOptions' => '5,10,25,50,100',
        'defaultDistanceUnit' => 'km',
        'enableAutoSubmit' => true,
        'enableSavedFilters' => false,
        'filterStyle' => 'standard',
        'showFilterToggle' => false,
        'className' => ''
    ));
    
    // Enqueue necessary scripts and styles
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('hbl-advanced-filter', plugins_url('assets/js/advanced-filter.js', HBL_PLUGIN_FILE), array('jquery'), HBL_VERSION, true);
    wp_enqueue_style('hbl-advanced-filter', plugins_url('assets/css/advanced-filter.css', HBL_PLUGIN_FILE), array(), HBL_VERSION);
    wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    
    // Localize script with AJAX URL
    wp_localize_script('hbl-advanced-filter', 'hblAdvancedFilter', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hbl_advanced_filter_nonce')
    ));
    
    // Get filter options
    $categories = get_terms(array(
        'taxonomy' => 'business_category',
        'hide_empty' => true
    ));
    
    $company_types = hbl_get_meta_values('company_type', 'business_listing');
    $locations = hbl_get_meta_values('location', 'business_listing');
    $price_ranges = array(
        'low' => __('Low', 'happy-business-listing'),
        'medium' => __('Medium', 'happy-business-listing'),
        'high' => __('High', 'happy-business-listing'),
        'premium' => __('Premium', 'happy-business-listing')
    );
    
    // Get services if enabled
    $services = array();
    if ($attributes['showServiceFilter']) {
        $services = hbl_get_meta_values('services', 'business_listing', true);
    }
    
    // Get tags if enabled
    $tags = array();
    if ($attributes['showTagsFilter']) {
        $tags = get_terms(array(
            'taxonomy' => 'business_tag',
            'hide_empty' => true
        ));
    }
    
    // Get current filter values from URL
    $current_search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $current_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
    $current_company_type = isset($_GET['company_type']) ? sanitize_text_field($_GET['company_type']) : '';
    $current_rating = isset($_GET['rating_min']) ? floatval($_GET['rating_min']) : 0;
    $current_price_range = isset($_GET['price_range']) ? sanitize_text_field($_GET['price_range']) : '';
    $current_verified = isset($_GET['verified']) ? filter_var($_GET['verified'], FILTER_VALIDATE_BOOLEAN) : false;
    $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
    $current_order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
    
    // New filter values
    $current_date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $current_date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
    $current_services = isset($_GET['services']) ? (array) $_GET['services'] : array();
    $current_tags = isset($_GET['tags']) ? (array) $_GET['tags'] : array();
    $current_distance = isset($_GET['distance']) ? intval($_GET['distance']) : 0;
    $current_distance_unit = isset($_GET['distance_unit']) ? sanitize_text_field($_GET['distance_unit']) : $attributes['defaultDistanceUnit'];
    $current_latitude = isset($_GET['latitude']) ? floatval($_GET['latitude']) : 0;
    $current_longitude = isset($_GET['longitude']) ? floatval($_GET['longitude']) : 0;
    $current_open_now = isset($_GET['open_now']) ? filter_var($_GET['open_now'], FILTER_VALIDATE_BOOLEAN) : false;
    
    // Distance options
    $distance_options = explode(',', $attributes['maxDistanceOptions']);
    $distance_options = array_map('trim', $distance_options);
    $distance_options = array_filter($distance_options, 'is_numeric');
    
    // Start output buffer
    ob_start();
    
    // Filter container
    $filter_class = 'hbl-advanced-filter';
    if (!empty($attributes['className'])) {
        $filter_class .= ' ' . $attributes['className'];
    }
    $filter_class .= ' hbl-filter-layout-' . $attributes['layout'];
    $filter_class .= ' hbl-filter-style-' . $attributes['filterStyle'];
    
    // Add data attributes
    $data_attrs = '';
    $data_attrs .= ' data-auto-submit="' . ($attributes['enableAutoSubmit'] ? 'true' : 'false') . '"';
    $data_attrs .= ' data-saved-filters="' . ($attributes['enableSavedFilters'] ? 'true' : 'false') . '"';
    ?>
    
    <div class="<?php echo esc_attr($filter_class); ?>"<?php echo $data_attrs; ?>>
        <?php if (!empty($attributes['title'])) : ?>
            <h3 class="hbl-filter-title"><?php echo esc_html($attributes['title']); ?></h3>
        <?php endif; ?>
        
        <?php if ($attributes['showFilterToggle']) : ?>
            <button type="button" class="hbl-filter-toggle">
                <span class="hbl-filter-toggle-icon"></span>
                <span class="hbl-filter-toggle-text"><?php _e('Show Filters', 'happy-business-listing'); ?></span>
            </button>
        <?php endif; ?>
        
        <form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="hbl-advanced-filter-form" id="hbl-advanced-filter-form">
            <div class="hbl-filter-fields">
                <?php if ($attributes['showKeywordSearch']) : ?>
                    <div class="hbl-filter-field hbl-filter-search">
                        <label for="hbl-filter-search"><?php _e('Search', 'happy-business-listing'); ?></label>
                        <input type="text" name="search" id="hbl-filter-search" placeholder="<?php esc_attr_e('Search businesses...', 'happy-business-listing'); ?>" value="<?php echo esc_attr($current_search); ?>">
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showCategoryFilter'] && !empty($categories)) : ?>
                    <div class="hbl-filter-field hbl-filter-category">
                        <label for="hbl-filter-category"><?php _e('Category', 'happy-business-listing'); ?></label>
                        <select name="category" id="hbl-filter-category">
                            <option value=""><?php _e('All Categories', 'happy-business-listing'); ?></option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($current_category, $category->slug); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showLocationFilter'] && !empty($locations)) : ?>
                    <div class="hbl-filter-field hbl-filter-location">
                        <label for="hbl-filter-location"><?php _e('Location', 'happy-business-listing'); ?></label>
                        <select name="location" id="hbl-filter-location">
                            <option value=""><?php _e('All Locations', 'happy-business-listing'); ?></option>
                            <?php foreach ($locations as $location) : ?>
                                <option value="<?php echo esc_attr($location); ?>" <?php selected($current_location, $location); ?>>
                                    <?php echo esc_html($location); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showCompanyTypeFilter'] && !empty($company_types)) : ?>
                    <div class="hbl-filter-field hbl-filter-company-type">
                        <label for="hbl-filter-company-type"><?php _e('Company Type', 'happy-business-listing'); ?></label>
                        <select name="company_type" id="hbl-filter-company-type">
                            <option value=""><?php _e('All Types', 'happy-business-listing'); ?></option>
                            <?php foreach ($company_types as $type) : ?>
                                <option value="<?php echo esc_attr($type); ?>" <?php selected($current_company_type, $type); ?>>
                                    <?php echo esc_html($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showRatingFilter']) : ?>
                    <div class="hbl-filter-field hbl-filter-rating">
                        <label for="hbl-filter-rating"><?php _e('Minimum Rating', 'happy-business-listing'); ?></label>
                        <select name="rating_min" id="hbl-filter-rating">
                            <option value=""><?php _e('Any Rating', 'happy-business-listing'); ?></option>
                            <option value="5" <?php selected($current_rating, 5); ?>><?php _e('5 Stars', 'happy-business-listing'); ?></option>
                            <option value="4" <?php selected($current_rating, 4); ?>><?php _e('4+ Stars', 'happy-business-listing'); ?></option>
                            <option value="3" <?php selected($current_rating, 3); ?>><?php _e('3+ Stars', 'happy-business-listing'); ?></option>
                            <option value="2" <?php selected($current_rating, 2); ?>><?php _e('2+ Stars', 'happy-business-listing'); ?></option>
                            <option value="1" <?php selected($current_rating, 1); ?>><?php _e('1+ Stars', 'happy-business-listing'); ?></option>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showPriceRangeFilter']) : ?>
                    <div class="hbl-filter-field hbl-filter-price-range">
                        <label for="hbl-filter-price-range"><?php _e('Price Range', 'happy-business-listing'); ?></label>
                        <select name="price_range" id="hbl-filter-price-range">
                            <option value=""><?php _e('Any Price', 'happy-business-listing'); ?></option>
                            <?php foreach ($price_ranges as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_price_range, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showVerifiedFilter']) : ?>
                    <div class="hbl-filter-field hbl-filter-verified">
                        <label for="hbl-filter-verified" class="hbl-checkbox-label">
                            <input type="checkbox" name="verified" id="hbl-filter-verified" value="1" <?php checked($current_verified); ?>>
                            <?php _e('Verified Businesses Only', 'happy-business-listing'); ?>
                        </label>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showDateRangeFilter']) : ?>
                    <div class="hbl-filter-field hbl-filter-date-range">
                        <label><?php _e('Date Range', 'happy-business-listing'); ?></label>
                        <div class="hbl-date-range-inputs">
                            <input type="text" name="date_from" id="hbl-filter-date-from" class="hbl-datepicker" placeholder="<?php esc_attr_e('From', 'happy-business-listing'); ?>" value="<?php echo esc_attr($current_date_from); ?>">
                            <span class="hbl-date-separator">-</span>
                            <input type="text" name="date_to" id="hbl-filter-date-to" class="hbl-datepicker" placeholder="<?php esc_attr_e('To', 'happy-business-listing'); ?>" value="<?php echo esc_attr($current_date_to); ?>">
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showTagsFilter'] && !empty($tags)) : ?>
                    <div class="hbl-filter-field hbl-filter-tags">
                        <label><?php _e('Tags', 'happy-business-listing'); ?></label>
                        <div class="hbl-checkbox-group">
                            <?php foreach ($tags as $tag) : ?>
                                <label class="hbl-checkbox-label">
                                    <input type="checkbox" name="tags[]" value="<?php echo esc_attr($tag->slug); ?>" <?php checked(in_array($tag->slug, $current_tags)); ?>>
                                    <?php echo esc_html($tag->name); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showServiceFilter'] && !empty($services)) : ?>
                    <div class="hbl-filter-field hbl-filter-services">
                        <label><?php _e('Services', 'happy-business-listing'); ?></label>
                        <div class="hbl-checkbox-group">
                            <?php foreach ($services as $service) : ?>
                                <label class="hbl-checkbox-label">
                                    <input type="checkbox" name="services[]" value="<?php echo esc_attr($service); ?>" <?php checked(in_array($service, $current_services)); ?>>
                                    <?php echo esc_html($service); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showDistanceFilter']) : ?>
                    <div class="hbl-filter-field hbl-filter-distance">
                        <label><?php _e('Distance', 'happy-business-listing'); ?></label>
                        <div class="hbl-distance-inputs">
                            <input type="text" id="hbl-filter-location-search" placeholder="<?php esc_attr_e('Enter your location', 'happy-business-listing'); ?>" class="hbl-location-search">
                            <div class="hbl-distance-selects">
                                <select name="distance" id="hbl-filter-distance">
                                    <option value=""><?php _e('Select distance', 'happy-business-listing'); ?></option>
                                    <?php foreach ($distance_options as $distance) : ?>
                                        <option value="<?php echo esc_attr($distance); ?>" <?php selected($current_distance, $distance); ?>>
                                            <?php echo esc_html($distance); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="distance_unit" id="hbl-filter-distance-unit">
                                    <option value="km" <?php selected($current_distance_unit, 'km'); ?>><?php _e('km', 'happy-business-listing'); ?></option>
                                    <option value="mi" <?php selected($current_distance_unit, 'mi'); ?>><?php _e('miles', 'happy-business-listing'); ?></option>
                                </select>
                            </div>
                            <input type="hidden" name="latitude" id="hbl-filter-latitude" value="<?php echo esc_attr($current_latitude); ?>">
                            <input type="hidden" name="longitude" id="hbl-filter-longitude" value="<?php echo esc_attr($current_longitude); ?>">
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showOpenNowFilter']) : ?>
                    <div class="hbl-filter-field hbl-filter-open-now">
                        <label for="hbl-filter-open-now" class="hbl-checkbox-label">
                            <input type="checkbox" name="open_now" id="hbl-filter-open-now" value="1" <?php checked($current_open_now); ?>>
                            <?php _e('Open Now', 'happy-business-listing'); ?>
                        </label>
                    </div>
                <?php endif; ?>
                
                <?php if ($attributes['showSorting']) : ?>
                    <div class="hbl-filter-field hbl-filter-sorting">
                        <label for="hbl-filter-orderby"><?php _e('Sort By', 'happy-business-listing'); ?></label>
                        <div class="hbl-sorting-selects">
                            <select name="orderby" id="hbl-filter-orderby">
                                <option value="date" <?php selected($current_orderby, 'date'); ?>><?php _e('Date', 'happy-business-listing'); ?></option>
                                <option value="title" <?php selected($current_orderby, 'title'); ?>><?php _e('Name', 'happy-business-listing'); ?></option>
                                <option value="rating" <?php selected($current_orderby, 'rating'); ?>><?php _e('Rating', 'happy-business-listing'); ?></option>
                                <option value="popularity" <?php selected($current_orderby, 'popularity'); ?>><?php _e('Popularity', 'happy-business-listing'); ?></option>
                                <option value="price" <?php selected($current_orderby, 'price'); ?>><?php _e('Price', 'happy-business-listing'); ?></option>
                            </select>
                            <select name="order" id="hbl-filter-order">
                                <option value="ASC" <?php selected($current_order, 'ASC'); ?>><?php _e('Ascending', 'happy-business-listing'); ?></option>
                                <option value="DESC" <?php selected($current_order, 'DESC'); ?>><?php _e('Descending', 'happy-business-listing'); ?></option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="hbl-filter-actions">
                <button type="submit" class="hbl-submit-button"><?php _e('Search', 'happy-business-listing'); ?></button>
                <button type="button" class="hbl-reset-button"><?php _e('Reset', 'happy-business-listing'); ?></button>
                
                <?php if ($attributes['enableSavedFilters']) : ?>
                    <button type="button" class="hbl-save-filter-button"><?php _e('Save Filter', 'happy-business-listing'); ?></button>
                <?php endif; ?>
            </div>
            
            <?php if ($attributes['enableSavedFilters']) : ?>
                <div class="hbl-saved-filters">
                    <h4><?php _e('Saved Filters', 'happy-business-listing'); ?></h4>
                    <div class="hbl-saved-filters-list">
                        <!-- Saved filters will be loaded here via JavaScript -->
                    </div>
                </div>
            <?php endif; ?>
            
            <input type="hidden" name="results_per_page" value="<?php echo esc_attr($attributes['resultsPerPage']); ?>">
        </form>
        
        <div id="hbl-filter-results" class="hbl-filter-results">
            <!-- Results will be loaded here via AJAX -->
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * AJAX handler for advanced filter results
 */
function hbl_ajax_advanced_filter_results() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hbl_advanced_filter_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'happy-business-listing')));
    }
    
    // Get filter parameters
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
    $company_type = isset($_POST['company_type']) ? sanitize_text_field($_POST['company_type']) : '';
    $rating_min = isset($_POST['rating_min']) ? floatval($_POST['rating_min']) : 0;
    $price_range = isset($_POST['price_range']) ? sanitize_text_field($_POST['price_range']) : '';
    $verified = isset($_POST['verified']) && $_POST['verified'] === '1';
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
    $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : 10;
    
    // New filter parameters
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
    $services = isset($_POST['services']) ? (array) $_POST['services'] : array();
    $tags = isset($_POST['tags']) ? (array) $_POST['tags'] : array();
    $distance = isset($_POST['distance']) ? intval($_POST['distance']) : 0;
    $distance_unit = isset($_POST['distance_unit']) ? sanitize_text_field($_POST['distance_unit']) : 'km';
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;
    $open_now = isset($_POST['open_now']) && $_POST['open_now'] === '1';
    
    // Sanitize arrays
    $services = array_map('sanitize_text_field', $services);
    $tags = array_map('sanitize_text_field', $tags);
    
    // Build query arguments
    $args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        'posts_per_page' => $results_per_page,
        'paged' => $paged
    );
    
    // Search query
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Category filter
    if (!empty($category)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'business_category',
            'field' => 'slug',
            'terms' => $category
        );
    }
    
    // Tags filter
    if (!empty($tags)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'business_tag',
            'field' => 'slug',
            'terms' => $tags,
            'operator' => 'IN'
        );
    }
    
    // Meta query for multiple conditions
    $meta_query = array();
    
    // Location filter
    if (!empty($location)) {
        $meta_query[] = array(
            'key' => 'location',
            'value' => $location,
            'compare' => '='
        );
    }
    
    // Company type filter
    if (!empty($company_type)) {
        $meta_query[] = array(
            'key' => 'company_type',
            'value' => $company_type,
            'compare' => '='
        );
    }
    
    // Rating filter
    if ($rating_min > 0) {
        $meta_query[] = array(
            'key' => 'rating',
            'value' => $rating_min,
            'compare' => '>=',
            'type' => 'NUMERIC'
        );
    }
    
    // Price range filter
    if (!empty($price_range)) {
        $meta_query[] = array(
            'key' => 'price_range',
            'value' => $price_range,
            'compare' => '='
        );
    }
    
    // Verified filter
    if ($verified) {
        $meta_query[] = array(
            'key' => 'verified',
            'value' => '1',
            'compare' => '='
        );
    }
    
    // Date range filter
    if (!empty($date_from) || !empty($date_to)) {
        $date_query = array();
        
        if (!empty($date_from)) {
            $date_query['after'] = $date_from;
        }
        
        if (!empty($date_to)) {
            $date_query['before'] = $date_to;
        }
        
        $date_query['inclusive'] = true;
        
        $args['date_query'] = array($date_query);
    }
    
    // Services filter
    if (!empty($services)) {
        $meta_query[] = array(
            'key' => 'services',
            'value' => $services,
            'compare' => 'LIKE'
        );
    }
    
    // Open now filter
    if ($open_now) {
        // Get current day and time
        $current_day = strtolower(date('l'));
        $current_time = date('H:i');
        
        // Add meta query for business hours
        $meta_query[] = array(
            'key' => 'business_hours_' . $current_day . '_open',
            'value' => '',
            'compare' => '!='
        );
        
        $meta_query[] = array(
            'key' => 'business_hours_' . $current_day . '_open',
            'value' => $current_time,
            'compare' => '<='
        );
        
        $meta_query[] = array(
            'key' => 'business_hours_' . $current_day . '_close',
            'value' => $current_time,
            'compare' => '>='
        );
    }
    
    // Add meta query to args if not empty
    if (!empty($meta_query)) {
        $args['meta_query'] = array_merge(array('relation' => 'AND'), $meta_query);
    }
    
    // Distance-based search
    $distance_filtered_ids = array();
    if ($distance > 0 && $latitude && $longitude) {
        // Convert distance to meters based on unit
        $distance_meters = $distance_unit === 'mi' ? $distance * 1609.34 : $distance * 1000;
        
        // Get all business listings with coordinates
        $locations_query = new WP_Query(array(
            'post_type' => 'business_listing',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'latitude',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'longitude',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        if ($locations_query->have_posts()) {
            foreach ($locations_query->posts as $post_id) {
                $business_lat = get_post_meta($post_id, 'latitude', true);
                $business_lng = get_post_meta($post_id, 'longitude', true);
                
                if ($business_lat && $business_lng) {
                    // Calculate distance using Haversine formula
                    $distance_between = hbl_calculate_distance($latitude, $longitude, $business_lat, $business_lng);
                    
                    // If within range, add to filtered IDs
                    if ($distance_between <= $distance_meters) {
                        $distance_filtered_ids[] = $post_id;
                    }
                }
            }
            
            // If we have filtered IDs, add them to the query
            if (!empty($distance_filtered_ids)) {
                $args['post__in'] = $distance_filtered_ids;
            } else {
                // No results within distance
                $args['post__in'] = array(0); // Force no results
            }
        }
    }
    
    // Order by
    switch ($orderby) {
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = $order;
            break;
            
        case 'rating':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = $order;
            break;
            
        case 'popularity':
            $args['meta_key'] = 'view_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = $order;
            break;
            
        case 'price':
            $args['meta_key'] = 'price_level';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = $order;
            break;
            
        default:
            $args['orderby'] = 'date';
            $args['order'] = $order;
    }
    
    // Cache results if enabled
    $cache_key = 'hbl_filter_' . md5(serialize($args));
    $cache_expiration = apply_filters('hbl_filter_cache_expiration', 3600); // 1 hour by default
    
    // Check if we have cached results
    $results_html = get_transient($cache_key);
    
    if (false === $results_html || isset($_POST['bypass_cache'])) {
        // Run the query
        $query = new WP_Query($args);
        
        // Start output buffer
        ob_start();
        
        if ($query->have_posts()) {
            ?>
            <div class="hbl-filter-results-count">
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
            
            <div class="hbl-filter-results-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="hbl-business-card">
                        <a href="<?php the_permalink(); ?>" class="hbl-business-link">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="hbl-business-image">
                                    <?php the_post_thumbnail('medium'); ?>
                                    
                                    <?php if (get_post_meta(get_the_ID(), 'featured', true)) : ?>
                                        <span class="hbl-featured-badge"><?php _e('Featured', 'happy-business-listing'); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <h3 class="hbl-business-title"><?php the_title(); ?></h3>
                            
                            <?php
                            // Display rating if available
                            $rating = get_post_meta(get_the_ID(), 'rating', true);
                            if ($rating) :
                                $rating_value = floatval($rating);
                                $rating_percent = ($rating_value / 5) * 100;
                            ?>
                                <div class="hbl-business-rating">
                                    <div class="hbl-rating-stars">
                                        <div class="hbl-rating-stars-bg"></div>
                                        <div class="hbl-rating-stars-value" style="width: <?php echo esc_attr($rating_percent); ?>%"></div>
                                    </div>
                                    <span class="hbl-rating-value"><?php echo esc_html(number_format($rating_value, 1)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php
                            // Display meta information
                            $meta_items = array();
                            
                            // Location
                            $location = get_post_meta(get_the_ID(), 'location', true);
                            if ($location) {
                                $meta_items[] = '<span class="hbl-business-meta-item hbl-business-location"><i class="hbl-icon hbl-icon-location"></i>' . esc_html($location) . '</span>';
                            }
                            
                            // Company type
                            $company_type = get_post_meta(get_the_ID(), 'company_type', true);
                            if ($company_type) {
                                $meta_items[] = '<span class="hbl-business-meta-item hbl-business-type"><i class="hbl-icon hbl-icon-type"></i>' . esc_html($company_type) . '</span>';
                            }
                            
                            // Price range
                            $price_range = get_post_meta(get_the_ID(), 'price_range', true);
                            if ($price_range) {
                                $price_label = '';
                                switch ($price_range) {
                                    case 'low':
                                        $price_label = '$';
                                        break;
                                    case 'medium':
                                        $price_label = '$$';
                                        break;
                                    case 'high':
                                        $price_label = '$$$';
                                        break;
                                    case 'premium':
                                        $price_label = '$$$$';
                                        break;
                                }
                                
                                if ($price_label) {
                                    $meta_items[] = '<span class="hbl-business-meta-item hbl-business-price"><i class="hbl-icon hbl-icon-price"></i>' . esc_html($price_label) . '</span>';
                                }
                            }
                            
                            // Verified badge
                            if (get_post_meta(get_the_ID(), 'verified', true)) {
                                $meta_items[] = '<span class="hbl-business-meta-item hbl-business-verified"><i class="hbl-icon hbl-icon-verified"></i>' . __('Verified', 'happy-business-listing') . '</span>';
                            }
                            
                            if (!empty($meta_items)) {
                                echo '<div class="hbl-business-meta">' . implode('', $meta_items) . '</div>';
                            }
                            ?>
                            
                            <div class="hbl-business-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php
            // Pagination
            $total_pages = $query->max_num_pages;
            
            if ($total_pages > 1) {
                $current_page = max(1, $paged);
                
                echo '<div class="hbl-pagination">';
                
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo; Previous', 'happy-business-listing'),
                    'next_text' => __('Next &raquo;', 'happy-business-listing'),
                    'total' => $total_pages,
                    'current' => $current_page
                ));
                
                echo '</div>';
            }
            
        } else {
            ?>
            <div class="hbl-no-results">
                <p><?php _e('No businesses found matching your criteria.', 'happy-business-listing'); ?></p>
                <p><?php _e('Try adjusting your filters or search term.', 'happy-business-listing'); ?></p>
            </div>
            <?php
        }
        
        wp_reset_postdata();
        
        $results_html = ob_get_clean();
        
        // Cache the results
        set_transient($cache_key, $results_html, $cache_expiration);
    }
    
    wp_send_json_success(array('html' => $results_html));
}
add_action('wp_ajax_hbl_advanced_filter_results', 'hbl_ajax_advanced_filter_results');
add_action('wp_ajax_nopriv_hbl_advanced_filter_results', 'hbl_ajax_advanced_filter_results');

/**
 * Calculate distance between two coordinates using Haversine formula
 *
 * @param float $lat1 First latitude
 * @param float $lon1 First longitude
 * @param float $lat2 Second latitude
 * @param float $lon2 Second longitude
 * @return float Distance in meters
 */
function hbl_calculate_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // Earth radius in meters
    
    $lat1_rad = deg2rad($lat1);
    $lon1_rad = deg2rad($lon1);
    $lat2_rad = deg2rad($lat2);
    $lon2_rad = deg2rad($lon2);
    
    $delta_lat = $lat2_rad - $lat1_rad;
    $delta_lon = $lon2_rad - $lon1_rad;
    
    $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
         cos($lat1_rad) * cos($lat2_rad) *
         sin($delta_lon / 2) * sin($delta_lon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earth_radius * $c;
}
?>
