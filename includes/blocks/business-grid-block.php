<?php
/**
 * Business Grid Gutenberg Block
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Business Grid Block
 */
function hbl_register_business_grid_block() {
    // Register block editor script
    wp_register_script(
        'hbl-business-grid-editor',
        HBL_PLUGIN_URL . 'assets/js/business-grid-editor.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        HBL_VERSION,
        true
    );

    // Register block editor style
    wp_register_style(
        'hbl-business-grid-editor',
        HBL_PLUGIN_URL . 'assets/css/business-grid-editor.css',
        array('wp-edit-blocks'),
        HBL_VERSION
    );

    // Register block
    register_block_type('happy-business-listing/business-grid', array(
        'editor_script' => 'hbl-business-grid-editor',
        'editor_style'  => 'hbl-business-grid-editor',
        'render_callback' => 'hbl_render_business_grid_block',
        'attributes' => array(
            'columns' => array(
                'type' => 'number',
                'default' => 3
            ),
            'postsPerPage' => array(
                'type' => 'number',
                'default' => 12
            ),
            'showFilters' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showSearch' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showPagination' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'filterStyle' => array(
                'type' => 'string',
                'default' => 'default'
            ),
            'gridStyle' => array(
                'type' => 'string',
                'default' => 'cards'
            ),
            'showExcerpt' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showRating' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showLocation' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showCompanyType' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'showVerificationBadge' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'orderBy' => array(
                'type' => 'string',
                'default' => 'date'
            ),
            'order' => array(
                'type' => 'string',
                'default' => 'DESC'
            ),
            'categories' => array(
                'type' => 'array',
                'default' => array()
            ),
            'excludeCategories' => array(
                'type' => 'array',
                'default' => array()
            ),
            'className' => array(
                'type' => 'string'
            )
        )
    ));
}
add_action('init', 'hbl_register_business_grid_block');

/**
 * Render Business Grid Block
 */
function hbl_render_business_grid_block($attributes) {
    // Set defaults
    $defaults = array(
        'columns' => 3,
        'postsPerPage' => 12,
        'showFilters' => true,
        'showSearch' => true,
        'showPagination' => true,
        'filterStyle' => 'default',
        'gridStyle' => 'cards',
        'showExcerpt' => true,
        'showRating' => true,
        'showLocation' => true,
        'showCompanyType' => true,
        'showVerificationBadge' => true,
        'orderBy' => 'date',
        'order' => 'DESC',
        'categories' => array(),
        'excludeCategories' => array(),
        'className' => ''
    );
    
    $attributes = array_merge($defaults, $attributes);
    
    // Convert to shortcode format for now (we'll use existing logic)
    $shortcode_atts = array(
        'columns' => $attributes['columns'],
        'posts_per_page' => $attributes['postsPerPage'],
        'show_filters' => $attributes['showFilters'] ? 'true' : 'false',
        'show_search' => $attributes['showSearch'] ? 'true' : 'false',
        'show_pagination' => $attributes['showPagination'] ? 'true' : 'false',
        'orderby' => $attributes['orderBy'],
        'order' => $attributes['order']
    );
    
    // Add categories if specified
    if (!empty($attributes['categories'])) {
        $shortcode_atts['category'] = implode(',', $attributes['categories']);
    }
    
    // Add exclude categories if specified
    if (!empty($attributes['excludeCategories'])) {
        $shortcode_atts['exclude_category'] = implode(',', $attributes['excludeCategories']);
    }
    
    // Add custom class
    if (!empty($attributes['className'])) {
        $shortcode_atts['class'] = $attributes['className'];
    }
    
    ob_start();
    
    // Add wrapper with block-specific classes
    $wrapper_classes = array('hbl-business-grid-block');
    
    if (!empty($attributes['className'])) {
        $wrapper_classes[] = $attributes['className'];
    }
    
    $wrapper_classes[] = 'hbl-grid-style-' . $attributes['gridStyle'];
    $wrapper_classes[] = 'hbl-filter-style-' . $attributes['filterStyle'];
    
    echo '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '">';
    
    // Use existing shortcode logic
    echo hbl_business_listing_archive_shortcode($shortcode_atts);
    
    echo '</div>';
    
    return ob_get_clean();
}

/**
 * Add block category
 */
function hbl_add_block_category($categories) {
    return array_merge(
        array(
            array(
                'slug'  => 'happy-business-listing',
                'title' => __('Business Directory', 'happy-business-listing'),
                'icon'  => 'store'
            )
        ),
        $categories
    );
}
add_filter('block_categories_all', 'hbl_add_block_category');

/**
 * Enqueue block assets
 */
function hbl_enqueue_business_grid_assets() {
    // Enqueue frontend styles for blocks
    wp_enqueue_style(
        'hbl-blocks-style',
        HBL_PLUGIN_URL . 'assets/css/blocks.css',
        array(),
        HBL_VERSION
    );
}
add_action('enqueue_block_assets', 'hbl_enqueue_business_grid_assets');

/**
 * Enqueue block editor assets
 */
function hbl_enqueue_business_grid_editor_assets() {
    // Localize script for editor
    wp_localize_script('hbl-business-grid-editor', 'hblBlockData', array(
        'categories' => hbl_get_business_categories_for_editor(),
        'pluginUrl' => HBL_PLUGIN_URL,
        'nonce' => wp_create_nonce('hbl_block_nonce')
    ));
}
add_action('enqueue_block_editor_assets', 'hbl_enqueue_business_grid_editor_assets');

/**
 * Get business categories for block editor
 */
function hbl_get_business_categories_for_editor() {
    $categories = get_terms(array(
        'taxonomy' => 'business_category',
        'hide_empty' => false
    ));
    
    $category_options = array();
    
    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            $category_options[] = array(
                'label' => $category->name,
                'value' => $category->term_id
            );
        }
    }
    
    return $category_options;
}
?> 