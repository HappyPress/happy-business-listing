<?php
/**
 * User Registration and Sub-site Creation for Happy Business Listing
 * 
 * Handles user registration, role management, and sub-site creation for business listings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create a business user role with specific capabilities
 */
function hbl_create_business_user_role() {
    // Check if the role already exists
    if (get_role('business_user')) {
        return;
    }
    
    // Add the business user role with specific capabilities
    add_role(
        'business_user',
        __('Business User', 'happy-business-listing'),
        array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
            'edit_published_posts' => true,
            'delete_published_posts' => true,
        )
    );
}
add_action('init', 'hbl_create_business_user_role');

/**
 * Hook into the post save action to create a user and sub-site
 */
function hbl_create_user_and_site($post_id, $post, $update) {
    // Only proceed for new business listings
    if ($post->post_type != 'business_listing' || $update) {
        return;
    }
    
    // Get business details
    $business_name = hbl_get_field('business_name', $post_id);
    if (empty($business_name)) {
        $business_name = $post->post_title;
    }
    
    // Generate username from business name
    $username = sanitize_user(strtolower(str_replace(' ', '_', $business_name)));
    $username = preg_replace('/[^a-z0-9_]/', '', $username);
    
    // Make sure username is unique
    $original_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $original_username . $counter;
        $counter++;
    }
    
    // Generate random password
    $password = wp_generate_password(12, true, true);
    
    // Get email from form or generate a placeholder
    $email = hbl_get_field('business_email', $post_id);
    if (empty($email)) {
        $email = $username . '@example.com';
        
        // Make sure email is unique
        $counter = 1;
        $original_email = $email;
        while (email_exists($email)) {
            $email = str_replace('@', $counter . '@', $original_email);
            $counter++;
        }
    }
    
    // Create the user
    $user_id = wp_insert_user(array(
        'user_login' => $username,
        'user_pass' => $password,
        'user_email' => $email,
        'display_name' => $business_name,
        'role' => 'business_user',
    ));
    
    if (is_wp_error($user_id)) {
        // Log error if logging is enabled
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] Error creating user for business listing #$post_id: " . $user_id->get_error_message());
        }
        return;
    }
    
    // Store user ID in business listing
    hbl_update_field('user_id', $user_id, $post_id);
    
    // Store business ID in user meta
    update_user_meta($user_id, 'user_business_id', $post_id);
    
    // Store WhatsApp number in user meta
    $whatsapp_number = hbl_get_field('whatsapp_number', $post_id);
    if (!empty($whatsapp_number)) {
        update_user_meta($user_id, 'whatsapp_number', $whatsapp_number);
    }
    
    // Check if multisite is enabled and sub-site creation is enabled
    if (is_multisite() && get_option('hbl_enable_subsite_creation') == '1') {
        // Create the sub-site using the function from site-creation.php
        hbl_create_business_subsite($post_id, $user_id, $business_name, $username);
    }
    
    // Send notification email
    hbl_send_registration_email($user_id, $password, $post_id);
}
add_action('save_post', 'hbl_create_user_and_site', 10, 3);

/**
 * Send registration email to the new business user
 */
function hbl_send_registration_email($user_id, $password, $post_id) {
    $user = get_userdata($user_id);
    if (!$user) {
        return;
    }
    
    $business_name = hbl_get_field('business_name', $post_id);
    if (empty($business_name)) {
        $business_name = get_the_title($post_id);
    }
    
    $site_url = get_site_url();
    $login_url = wp_login_url();
    
    // Check if multisite is enabled
    if (is_multisite()) {
        $site_id = hbl_get_field('site_id', $post_id);
        if ($site_id) {
            $site_url = get_site_url($site_id);
        }
    }
    
    $subject = sprintf(__('Welcome to %s - Your Business Account', 'happy-business-listing'), get_bloginfo('name'));
    
    $message = sprintf(__('Hello %s,', 'happy-business-listing'), $business_name) . "\n\n";
    $message .= sprintf(__('Your business listing on %s has been created successfully.', 'happy-business-listing'), get_bloginfo('name')) . "\n\n";
    $message .= __('Here are your account details:', 'happy-business-listing') . "\n";
    $message .= sprintf(__('Username: %s', 'happy-business-listing'), $user->user_login) . "\n";
    $message .= sprintf(__('Password: %s', 'happy-business-listing'), $password) . "\n";
    $message .= sprintf(__('Login URL: %s', 'happy-business-listing'), $login_url) . "\n\n";
    
    if (is_multisite()) {
        $message .= sprintf(__('Your business website has been created at: %s', 'happy-business-listing'), $site_url) . "\n\n";
    }
    
    $message .= __('Please keep this information safe for future reference.', 'happy-business-listing') . "\n\n";
    $message .= __('Thank you for registering with us!', 'happy-business-listing') . "\n\n";
    $message .= get_bloginfo('name');
    
    wp_mail($user->user_email, $subject, $message);
}

/**
 * Add business user capabilities for managing their own content
 */
function hbl_add_business_user_capabilities() {
    $role = get_role('business_user');
    if (!$role) {
        return;
    }
    
    // Add capabilities for service_product post type
    $role->add_cap('edit_service_product');
    $role->add_cap('read_service_product');
    $role->add_cap('delete_service_product');
    $role->add_cap('edit_service_products');
    $role->add_cap('edit_published_service_products');
    $role->add_cap('publish_service_products');
    $role->add_cap('delete_published_service_products');
    
    // Add capabilities for lead post type
    $role->add_cap('edit_lead');
    $role->add_cap('read_lead');
    $role->add_cap('delete_lead');
    $role->add_cap('edit_leads');
    $role->add_cap('edit_published_leads');
    $role->add_cap('publish_leads');
    $role->add_cap('delete_published_leads');
}
add_action('admin_init', 'hbl_add_business_user_capabilities');

/**
 * Filter content to only show business user's own content
 */
function hbl_filter_business_user_content($query) {
    global $pagenow, $typenow;
    
    // Only apply on admin pages for our custom post types
    if (!is_admin() || $pagenow !== 'edit.php' || !in_array($typenow, array('service_product', 'lead'))) {
        return;
    }
    
    // Only apply for business users
    $user = wp_get_current_user();
    if (!in_array('business_user', $user->roles)) {
        return;
    }
    
    // Get the business ID associated with the user
    $business_id = get_user_meta($user->ID, 'user_business_id', true);
    if (!$business_id) {
        return;
    }
    
    // Add meta query to only show content related to the user's business
    $query->set('meta_key', 'business_id');
    $query->set('meta_value', $business_id);
}
add_action('pre_get_posts', 'hbl_filter_business_user_content');

/**
 * Set default business ID when a business user creates content
 */
function hbl_set_default_business_id($post_id, $post, $update) {
    // Only apply for new posts of our custom types
    if ($update || !in_array($post->post_type, array('service_product', 'lead'))) {
        return;
    }
    
    // Only apply for business users
    $user = wp_get_current_user();
    if (!in_array('business_user', $user->roles)) {
        return;
    }
    
    // Get the business ID associated with the user
    $business_id = get_user_meta($user->ID, 'user_business_id', true);
    if (!$business_id) {
        return;
    }
    
    // Set the business ID if not already set
    $current_business_id = hbl_get_field('business_id', $post_id);
    if (empty($current_business_id)) {
        hbl_update_field('business_id', $business_id, $post_id);
    }
}
add_action('save_post', 'hbl_set_default_business_id', 20, 3);
?>
