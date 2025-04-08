<?php
/**
 * ACF Fields for Happy Business Listing
 * 
 * Registers custom fields for business listings, services/products, and leads
 * using Advanced Custom Fields (ACF) if available, or falls back to custom meta boxes.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if ACF is active and set up fields
 */
function hbl_setup_acf_fields() {
    // Check if ACF is active
    if (function_exists('acf_add_local_field_group')) {
        // Add ACF fields for Business Listings
        acf_add_local_field_group(array(
            'key' => 'group_hbl_business_listing',
            'title' => 'Business Listing Details',
            'fields' => array(
                array(
                    'key' => 'field_hbl_business_name',
                    'label' => 'Business Name',
                    'name' => 'business_name',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_hbl_company_type',
                    'label' => 'Type of Company',
                    'name' => 'company_type',
                    'type' => 'select',
                    'choices' => array(
                        'Pvt Ltd' => 'Pvt Ltd',
                        'LLP' => 'LLP',
                        'OPC' => 'OPC',
                        'Other' => 'Other',
                    ),
                    'default_value' => 'Pvt Ltd',
                ),
                array(
                    'key' => 'field_hbl_gst_no',
                    'label' => 'GST No.',
                    'name' => 'gst_no',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_hbl_tan_pan',
                    'label' => 'TAN/PAN',
                    'name' => 'tan_pan',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_hbl_location',
                    'label' => 'Location/s',
                    'name' => 'location',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_hbl_website',
                    'label' => 'Website',
                    'name' => 'website',
                    'type' => 'url',
                ),
                array(
                    'key' => 'field_hbl_social_media',
                    'label' => 'Social Media',
                    'name' => 'social_media',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_hbl_whatsapp_number',
                    'label' => 'WhatsApp Number',
                    'name' => 'whatsapp_number',
                    'type' => 'text',
                ),
                array(
                    'key' => 'field_hbl_is_featured',
                    'label' => 'Featured Business',
                    'name' => 'is_featured',
                    'type' => 'true_false',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_hbl_verification_status',
                    'label' => 'Verification Status',
                    'name' => 'verification_status',
                    'type' => 'select',
                    'choices' => array(
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ),
                    'default_value' => 'pending',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'business_listing',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));

        // Add ACF fields for Services and Products
        acf_add_local_field_group(array(
            'key' => 'group_hbl_service_product',
            'title' => 'Service/Product Details',
            'fields' => array(
                array(
                    'key' => 'field_hbl_description',
                    'label' => 'Description',
                    'name' => 'description',
                    'type' => 'textarea',
                ),
                array(
                    'key' => 'field_hbl_price',
                    'label' => 'Price',
                    'name' => 'price',
                    'type' => 'number',
                ),
                array(
                    'key' => 'field_hbl_business_id',
                    'label' => 'Business',
                    'name' => 'business_id',
                    'type' => 'post_object',
                    'post_type' => array('business_listing'),
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_hbl_service_type',
                    'label' => 'Type',
                    'name' => 'service_type',
                    'type' => 'select',
                    'choices' => array(
                        'service' => 'Service',
                        'product' => 'Product',
                    ),
                    'default_value' => 'service',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'service_product',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));

        // Add ACF fields for Leads
        acf_add_local_field_group(array(
            'key' => 'group_hbl_lead',
            'title' => 'Lead Details',
            'fields' => array(
                array(
                    'key' => 'field_hbl_lead_details',
                    'label' => 'Lead Details',
                    'name' => 'lead_details',
                    'type' => 'textarea',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_hbl_lead_phone',
                    'label' => 'Phone Number',
                    'name' => 'lead_phone',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_hbl_lead_email',
                    'label' => 'Email',
                    'name' => 'lead_email',
                    'type' => 'email',
                ),
                array(
                    'key' => 'field_hbl_business_id',
                    'label' => 'Business',
                    'name' => 'business_id',
                    'type' => 'post_object',
                    'post_type' => array('business_listing'),
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_hbl_lead_status',
                    'label' => 'Status',
                    'name' => 'lead_status',
                    'type' => 'select',
                    'choices' => array(
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'converted' => 'Converted',
                        'closed' => 'Closed',
                    ),
                    'default_value' => 'new',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'lead',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
        
        // Register ACF fields for user profiles
        acf_add_local_field_group(array(
            'key' => 'group_hbl_user',
            'title' => 'Business User Details',
            'fields' => array(
                array(
                    'key' => 'field_hbl_user_business_id',
                    'label' => 'Business',
                    'name' => 'user_business_id',
                    'type' => 'post_object',
                    'post_type' => array('business_listing'),
                    'return_format' => 'id',
                ),
                array(
                    'key' => 'field_hbl_user_whatsapp',
                    'label' => 'WhatsApp Number',
                    'name' => 'whatsapp_number',
                    'type' => 'text',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'user_role',
                        'operator' => '==',
                        'value' => 'all',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ));
        
        return true;
    }
    
    return false;
}
add_action('init', 'hbl_setup_acf_fields');

/**
 * Register custom meta fields for fallback when ACF is not available
 */
function hbl_register_meta_fields() {
    // Only register if ACF is not active
    if (function_exists('acf_add_local_field_group')) {
        return;
    }
    
    // Register meta fields for business_listing post type
    $business_fields = array(
        'business_name' => array(
            'type' => 'string',
            'description' => 'Business Name',
            'single' => true,
            'show_in_rest' => true,
        ),
        'company_type' => array(
            'type' => 'string',
            'description' => 'Type of Company',
            'single' => true,
            'show_in_rest' => true,
        ),
        'gst_no' => array(
            'type' => 'string',
            'description' => 'GST No.',
            'single' => true,
            'show_in_rest' => true,
        ),
        'tan_pan' => array(
            'type' => 'string',
            'description' => 'TAN/PAN',
            'single' => true,
            'show_in_rest' => true,
        ),
        'location' => array(
            'type' => 'string',
            'description' => 'Location/s',
            'single' => true,
            'show_in_rest' => true,
        ),
        'website' => array(
            'type' => 'string',
            'description' => 'Website',
            'single' => true,
            'show_in_rest' => true,
        ),
        'social_media' => array(
            'type' => 'string',
            'description' => 'Social Media',
            'single' => true,
            'show_in_rest' => true,
        ),
        'whatsapp_number' => array(
            'type' => 'string',
            'description' => 'WhatsApp Number',
            'single' => true,
            'show_in_rest' => true,
        ),
        'is_featured' => array(
            'type' => 'boolean',
            'description' => 'Featured Business',
            'single' => true,
            'show_in_rest' => true,
            'default' => false,
        ),
        'verification_status' => array(
            'type' => 'string',
            'description' => 'Verification Status',
            'single' => true,
            'show_in_rest' => true,
            'default' => 'pending',
        ),
    );
    
    foreach ($business_fields as $key => $args) {
        register_post_meta('business_listing', $key, $args);
    }
    
    // Register meta fields for service_product post type
    $service_fields = array(
        'description' => array(
            'type' => 'string',
            'description' => 'Description',
            'single' => true,
            'show_in_rest' => true,
        ),
        'price' => array(
            'type' => 'number',
            'description' => 'Price',
            'single' => true,
            'show_in_rest' => true,
        ),
        'business_id' => array(
            'type' => 'integer',
            'description' => 'Business ID',
            'single' => true,
            'show_in_rest' => true,
        ),
        'service_type' => array(
            'type' => 'string',
            'description' => 'Type (Service or Product)',
            'single' => true,
            'show_in_rest' => true,
            'default' => 'service',
        ),
    );
    
    foreach ($service_fields as $key => $args) {
        register_post_meta('service_product', $key, $args);
    }
    
    // Register meta fields for lead post type
    $lead_fields = array(
        'lead_details' => array(
            'type' => 'string',
            'description' => 'Lead Details',
            'single' => true,
            'show_in_rest' => true,
        ),
        'lead_phone' => array(
            'type' => 'string',
            'description' => 'Phone Number',
            'single' => true,
            'show_in_rest' => true,
        ),
        'lead_email' => array(
            'type' => 'string',
            'description' => 'Email',
            'single' => true,
            'show_in_rest' => true,
        ),
        'business_id' => array(
            'type' => 'integer',
            'description' => 'Business ID',
            'single' => true,
            'show_in_rest' => true,
        ),
        'lead_status' => array(
            'type' => 'string',
            'description' => 'Status',
            'single' => true,
            'show_in_rest' => true,
            'default' => 'new',
        ),
    );
    
    foreach ($lead_fields as $key => $args) {
        register_post_meta('lead', $key, $args);
    }
    
    // Register user meta fields
    register_meta('user', 'whatsapp_number', array(
        'type' => 'string',
        'description' => 'WhatsApp Number',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    register_meta('user', 'user_business_id', array(
        'type' => 'integer',
        'description' => 'Business ID',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'hbl_register_meta_fields');

/**
 * Add meta boxes for business listings when ACF is not available
 */
function hbl_add_meta_boxes() {
    // Only add meta boxes if ACF is not active
    if (function_exists('acf_add_local_field_group')) {
        return;
    }
    
    // Business Listing meta box
    add_meta_box(
        'hbl_business_details',
        'Business Details',
        'hbl_business_details_callback',
        'business_listing',
        'normal',
        'default'
    );
    
    // Service/Product meta box
    add_meta_box(
        'hbl_service_details',
        'Service/Product Details',
        'hbl_service_details_callback',
        'service_product',
        'normal',
        'default'
    );
    
    // Lead meta box
    add_meta_box(
        'hbl_lead_details',
        'Lead Details',
        'hbl_lead_details_callback',
        'lead',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'hbl_add_meta_boxes');

/**
 * Business details meta box callback
 */
function hbl_business_details_callback($post) {
    wp_nonce_field('hbl_save_business_details', 'hbl_business_details_nonce');
    
    $fields = array(
        'business_name' => array(
            'label' => 'Business Name',
            'type' => 'text',
            'required' => true,
        ),
        'company_type' => array(
            'label' => 'Type of Company',
            'type' => 'select',
            'options' => array(
                'Pvt Ltd' => 'Pvt Ltd',
                'LLP' => 'LLP',
                'OPC' => 'OPC',
                'Other' => 'Other',
            ),
        ),
        'gst_no' => array(
            'label' => 'GST No.',
            'type' => 'text',
        ),
        'tan_pan' => array(
            'label' => 'TAN/PAN',
            'type' => 'text',
        ),
        'location' => array(
            'label' => 'Location/s',
            'type' => 'text',
            'required' => true,
        ),
        'website' => array(
            'label' => 'Website',
            'type' => 'url',
        ),
        'social_media' => array(
            'label' => 'Social Media',
            'type' => 'text',
        ),
        'whatsapp_number' => array(
            'label' => 'WhatsApp Number',
            'type' => 'text',
        ),
    );
    
    echo '<div class="hbl-meta-box">';
    
    foreach ($fields as $field_key => $field) {
        $value = get_post_meta($post->ID, $field_key, true);
        $required = isset($field['required']) && $field['required'] ? 'required' : '';
        
        echo '<div class="hbl-field">';
        echo '<label for="' . esc_attr($field_key) . '">' . esc_html($field['label']) . ($required ? ' <span class="required">*</span>' : '') . '</label>';
        
        if ($field['type'] === 'select') {
            echo '<select id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" ' . $required . '>';
            echo '<option value="">Select ' . esc_html($field['label']) . '</option>';
            
            foreach ($field['options'] as $option_value => $option_label) {
                echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
            }
            
            echo '</select>';
        } else {
            echo '<input type="' . esc_attr($field['type']) . '" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '" ' . $required . ' />';
        }
        
        echo '</div>';
    }
    
    // Additional fields
    $is_featured = get_post_meta($post->ID, 'is_featured', true);
    $verification_status = get_post_meta($post->ID, 'verification_status', true) ?: 'pending';
    
    echo '<div class="hbl-field">';
    echo '<label for="is_featured">Featured Business</label>';
    echo '<input type="checkbox" id="is_featured" name="is_featured" value="1" ' . checked($is_featured, '1', false) . ' />';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="verification_status">Verification Status</label>';
    echo '<select id="verification_status" name="verification_status">';
    echo '<option value="pending" ' . selected($verification_status, 'pending', false) . '>Pending</option>';
    echo '<option value="verified" ' . selected($verification_status, 'verified', false) . '>Verified</option>';
    echo '<option value="rejected" ' . selected($verification_status, 'rejected', false) . '>Rejected</option>';
    echo '</select>';
    echo '</div>';
    
    echo '</div>';
    
    // Add some basic styling
    echo '<style>
        .hbl-meta-box {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .hbl-field {
            margin-bottom: 10px;
        }
        .hbl-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .hbl-field input[type="text"],
        .hbl-field input[type="url"],
        .hbl-field select {
            width: 100%;
        }
        .hbl-field .required {
            color: #cc0000;
        }
    </style>';
}

/**
 * Service/Product details meta box callback
 */
function hbl_service_details_callback($post) {
    wp_nonce_field('hbl_save_service_details', 'hbl_service_details_nonce');
    
    $description = get_post_meta($post->ID, 'description', true);
    $price = get_post_meta($post->ID, 'price', true);
    $business_id = get_post_meta($post->ID, 'business_id', true);
    $service_type = get_post_meta($post->ID, 'service_type', true) ?: 'service';
    
    // Get all business listings for dropdown
    $businesses = get_posts(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));
    
    echo '<div class="hbl-meta-box">';
    
    echo '<div class="hbl-field">';
    echo '<label for="description">Description</label>';
    echo '<textarea id="description" name="description" rows="4" style="width: 100%;">' . esc_textarea($description) . '</textarea>';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="price">Price</label>';
    echo '<input type="number" id="price" name="price" value="' . esc_attr($price) . '" step="0.01" min="0" />';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="service_type">Type</label>';
    echo '<select id="service_type" name="service_type">';
    echo '<option value="service" ' . selected($service_type, 'service', false) . '>Service</option>';
    echo '<option value="product" ' . selected($service_type, 'product', false) . '>Product</option>';
    echo '</select>';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="business_id">Business</label>';
    echo '<select id="business_id" name="business_id">';
    echo '<option value="">Select Business</option>';
    
    foreach ($businesses as $business) {
        echo '<option value="' . esc_attr($business->ID) . '" ' . selected($business_id, $business->ID, false) . '>' . esc_html($business->post_title) . '</option>';
    }
    
    echo '</select>';
    echo '</div>';
    
    echo '</div>';
    
    // Add some basic styling
    echo '<style>
        .hbl-meta-box {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .hbl-field {
            margin-bottom: 10px;
        }
        .hbl-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .hbl-field input[type="number"],
        .hbl-field select {
            width: 100%;
        }
    </style>';
}

/**
 * Lead details meta box callback
 */
function hbl_lead_details_callback($post) {
    wp_nonce_field('hbl_save_lead_details', 'hbl_lead_details_nonce');
    
    $lead_details = get_post_meta($post->ID, 'lead_details', true);
    $lead_phone = get_post_meta($post->ID, 'lead_phone', true);
    $lead_email = get_post_meta($post->ID, 'lead_email', true);
    $business_id = get_post_meta($post->ID, 'business_id', true);
    $lead_status = get_post_meta($post->ID, 'lead_status', true) ?: 'new';
    
    // Get all business listings for dropdown
    $businesses = get_posts(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));
    
    echo '<div class="hbl-meta-box">';
    
    echo '<div class="hbl-field">';
    echo '<label for="lead_details">Lead Details <span class="required">*</span></label>';
    echo '<textarea id="lead_details" name="lead_details" rows="4" style="width: 100%;" required>' . esc_textarea($lead_details) . '</textarea>';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="lead_phone">Phone Number <span class="required">*</span></label>';
    echo '<input type="text" id="lead_phone" name="lead_phone" value="' . esc_attr($lead_phone) . '" required />';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="lead_email">Email</label>';
    echo '<input type="email" id="lead_email" name="lead_email" value="' . esc_attr($lead_email) . '" />';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="business_id">Business</label>';
    echo '<select id="business_id" name="business_id">';
    echo '<option value="">Select Business</option>';
    
    foreach ($businesses as $business) {
        echo '<option value="' . esc_attr($business->ID) . '" ' . selected($business_id, $business->ID, false) . '>' . esc_html($business->post_title) . '</option>';
    }
    
    echo '</select>';
    echo '</div>';
    
    echo '<div class="hbl-field">';
    echo '<label for="lead_status">Status</label>';
    echo '<select id="lead_status" name="lead_status">';
    echo '<option value="new" ' . selected($lead_status, 'new', false) . '>New</option>';
    echo '<option value="contacted" ' . selected($lead_status, 'contacted', false) . '>Contacted</option>';
    echo '<option value="qualified" ' . selected($lead_status, 'qualified', false) . '>Qualified</option>';
    echo '<option value="converted" ' . selected($lead_status, 'converted', false) . '>Converted</option>';
    echo '<option value="closed" ' . selected($lead_status, 'closed', false) . '>Closed</option>';
    echo '</select>';
    echo '</div>';
    
    echo '</div>';
    
    // Add some basic styling
    echo '<style>
        .hbl-meta-box {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .hbl-field {
            margin-bottom: 10px;
        }
        .hbl-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .hbl-field input[type="text"],
        .hbl-field input[type="email"],
        .hbl-field select {
            width: 100%;
        }
        .hbl-field .required {
            color: #cc0000;
        }
    </style>';
}

/**
 * Save business details meta box data
 */
function hbl_save_business_details($post_id) {
    // Check if we're supposed to save
    if (!isset($_POST['hbl_business_details_nonce']) || !wp_verify_nonce($_POST['hbl_business_details_nonce'], 'hbl_save_business_details')) {
        return;
    }
    
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save fields
    $fields = array(
        'business_name',
        'company_type',
        'gst_no',
        'tan_pan',
        'location',
        'website',
        'social_media',
        'whatsapp_number',
        'verification_status',
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Save checkbox fields
    update_post_meta($post_id, 'is_featured', isset($_POST['is_featured']) ? '1' : '0');
}
add_action('save_post_business_listing', 'hbl_save_business_details');

/**
 * Save service details meta box data
 */
function hbl_save_service_details($post_id) {
    // Check if we're supposed to save
    if (!isset($_POST['hbl_service_details_nonce']) || !wp_verify_nonce($_POST['hbl_service_details_nonce'], 'hbl_save_service_details')) {
        return;
    }
    
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save fields
    if (isset($_POST['description'])) {
        update_post_meta($post_id, 'description', sanitize_textarea_field($_POST['description']));
    }
    
    if (isset($_POST['price'])) {
        update_post_meta($post_id, 'price', sanitize_text_field($_POST['price']));
    }
    
    if (isset($_POST['business_id'])) {
        update_post_meta($post_id, 'business_id', absint($_POST['business_id']));
    }
    
    if (isset($_POST['service_type'])) {
        update_post_meta($post_id, 'service_type', sanitize_text_field($_POST['service_type']));
    }
}
add_action('save_post_service_product', 'hbl_save_service_details');

/**
 * Save lead details meta box data
 */
function hbl_save_lead_details($post_id) {
    // Check if we're supposed to save
    if (!isset($_POST['hbl_lead_details_nonce']) || !wp_verify_nonce($_POST['hbl_lead_details_nonce'], 'hbl_save_lead_details')) {
        return;
    }
    
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save fields
    if (isset($_POST['lead_details'])) {
        update_post_meta($post_id, 'lead_details', sanitize_textarea_field($_POST['lead_details']));
    }
    
    if (isset($_POST['lead_phone'])) {
        update_post_meta($post_id, 'lead_phone', sanitize_text_field($_POST['lead_phone']));
    }
    
    if (isset($_POST['lead_email'])) {
        update_post_meta($post_id, 'lead_email', sanitize_email($_POST['lead_email']));
    }
    
    if (isset($_POST['business_id'])) {
        update_post_meta($post_id, 'business_id', absint($_POST['business_id']));
    }
    
    if (isset($_POST['lead_status'])) {
        update_post_meta($post_id, 'lead_status', sanitize_text_field($_POST['lead_status']));
    }
}
add_action('save_post_lead', 'hbl_save_lead_details');

/**
 * Helper function to get field value regardless of ACF availability
 */
function hbl_get_field($field_name, $post_id = false) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Try ACF function first if available
    if (function_exists('get_field')) {
        return get_field($field_name, $post_id);
    }
    
    // Fall back to regular post meta
    return get_post_meta($post_id, $field_name, true);
}

/**
 * Helper function to update field value regardless of ACF availability
 */
function hbl_update_field($field_name, $value, $post_id = false) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Try ACF function first if available
    if (function_exists('update_field')) {
        return update_field($field_name, $value, $post_id);
    }
    
    // Fall back to regular post meta
    return update_post_meta($post_id, $field_name, $value);
}
?>
