<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function hbl_custom_post_type_link($post_link, $post) {
    if ('business_listing' === $post->post_type) {
        $custom_slug = get_option('hbl_custom_permalinks');
        if (!empty($custom_slug)) {
            $post_link = home_url($custom_slug . '/' . $post->post_name);
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'hbl_custom_post_type_link', 10, 2);

function hbl_custom_permalinks_init() {
    $custom_slug = get_option('hbl_custom_permalinks');
    if (!empty($custom_slug)) {
        add_rewrite_rule(
            '^' . $custom_slug . '/([^/]+)/?$',
            'index.php?post_type=business_listing&name=$matches[1]',
            'top'
        );
    }
}
add_action('init', 'hbl_custom_permalinks_init');