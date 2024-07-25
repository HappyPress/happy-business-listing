<?php
// Register Custom Post Types for Business Listings, Services/Products, and Leads
function hbl_register_post_types() {
    $business_listing_args = array(
        'label' => 'Business Listings',
        'public' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'rewrite' => array('slug' => 'business-listing'),
        'has_archive' => true,
    );
    register_post_type('business_listing', $business_listing_args);

    $service_product_args = array(
        'label' => 'Services and Products',
        'public' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'rewrite' => array('slug' => 'service-product'),
    );
    register_post_type('service_product', $service_product_args);

    $lead_args = array(
        'label' => 'Leads',
        'public' => false,
        'supports' => array('title', 'editor', 'custom-fields'),
        'rewrite' => array('slug' => 'lead'),
    );
    register_post_type('lead', $lead_args);
}
add_action('init', 'hbl_register_post_types');


function hbl_add_business_listing_meta_boxes() {
    add_meta_box(
        'hbl_business_details',
        'Business Details',
        'hbl_business_details_callback',
        'business_listing',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'hbl_add_business_listing_meta_boxes');

function hbl_business_details_callback($post) {
    wp_nonce_field('hbl_save_business_details', 'hbl_business_details_nonce');

    $fields = [
        'type_of_company' => 'Type of Company',
        'gst_no' => 'GST No',
        'tan_pan' => 'TAN/PAN',
        'location' => 'Location',
        'website' => 'Website',
        'social_media_handles' => 'Social Media Handles',
        'whatsapp_number' => 'WhatsApp Number'
    ];

    foreach ($fields as $field_key => $field_label) {
        $value = get_post_meta($post->ID, $field_key, true);
        echo '<p>';
        echo '<label for="' . $field_key . '">' . $field_label . ':</label>';
        echo '<input type="text" id="' . $field_key . '" name="' . $field_key . '" value="' . esc_attr($value) . '" size="25" />';
        echo '</p>';
    }
}

function hbl_save_business_details($post_id) {
    if (!isset($_POST['hbl_business_details_nonce']) || !wp_verify_nonce($_POST['hbl_business_details_nonce'], 'hbl_save_business_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = [
        'type_of_company', 'gst_no', 'tan_pan', 'location', 'website', 'social_media_handles', 'whatsapp_number'
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'hbl_save_business_details');