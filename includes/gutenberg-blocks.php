<?php
// Register custom Gutenberg block
function hbl_register_gutenberg_blocks() {
    // Register block script
    wp_register_script(
        'hbl-block',
        plugins_url('/assets/js/blocks/business-details/index.js', dirname(__FILE__)),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch'),
        HBL_VERSION
    );

    // Register block type
    register_block_type('happy-business-listing/business-details', array(
        'editor_script' => 'hbl-block',
        'render_callback' => 'hbl_render_business_details_block',
        'category' => 'common',
    ));
    error_log('Business Details block registered');
}
add_action('init', 'hbl_register_gutenberg_blocks');

// Enqueue block editor assets
function hbl_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'hbl-business-details-block',
        plugins_url('/assets/js/blocks/business-details/index.js', dirname(__FILE__)),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'),
        HBL_VERSION
    );

    wp_enqueue_style(
        'hbl-business-details-block-style',
        plugins_url('/assets/css/blocks/business-details.css', dirname(__FILE__)),
        array(),
        HBL_VERSION
    );
}
add_action('enqueue_block_editor_assets', 'hbl_enqueue_block_editor_assets');

function hbl_get_business_listings() {
    $businesses = get_posts(array(
        'post_type' => 'business_listing',
        'numberposts' => -1,
    ));

    return array_map(function($business) {
        return array(
            'value' => $business->ID,
            'label' => $business->post_title,
        );
    }, $businesses);
}

add_action('rest_api_init', function () {
    register_rest_route('happy-business-listing/v1', '/businesses', array(
        'methods' => 'GET',
        'callback' => 'hbl_get_business_listings',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
});

// Render business details block
function hbl_render_business_details_block($attributes) {
    if (empty($attributes['businessId'])) {
        return '<p>' . __('Please select a business.', 'happy-business-listing') . '</p>';
    }

    $business_id = $attributes['businessId'];
    $business = get_post($business_id);

    if (!$business || $business->post_type !== 'business_listing') {
        return '<p>' . __('Invalid business selected.', 'happy-business-listing') . '</p>';
    }

    $logo = get_the_post_thumbnail_url($business_id, 'medium');
    $company_type = get_post_meta($business_id, 'company_type', true);
    $location = get_post_meta($business_id, 'location', true);
    $website = get_post_meta($business_id, 'website', true);
    $whatsapp = get_post_meta($business_id, 'whatsapp_number', true);

    ob_start();
    ?>
    <div class="business-details">
        <?php if ($logo) : ?>
            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($business->post_title); ?>" class="business-logo">
        <?php endif; ?>
        <h2 class="business-title"><?php echo esc_html($business->post_title); ?></h2>
        <div class="business-info">
            <?php if ($company_type) : ?>
                <p><strong><?php _e('Company Type:', 'happy-business-listing'); ?></strong> <?php echo esc_html($company_type); ?></p>
            <?php endif; ?>
            <?php if ($location) : ?>
                <p><strong><?php _e('Location:', 'happy-business-listing'); ?></strong> <?php echo esc_html($location); ?></p>
            <?php endif; ?>
            <?php if ($website) : ?>
                <p><strong><?php _e('Website:', 'happy-business-listing'); ?></strong> <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($website); ?></a></p>
            <?php endif; ?>
            <?php if ($whatsapp) : ?>
                <p><strong><?php _e('WhatsApp:', 'happy-business-listing'); ?></strong> <?php echo esc_html($whatsapp); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}