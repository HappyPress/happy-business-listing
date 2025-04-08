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
    
    // Check if multisite is enabled
    if (is_multisite()) {
        // Create the sub-site
        hbl_create_business_subsite($post_id, $user_id, $business_name, $username);
    }
    
    // Send notification email
    hbl_send_registration_email($user_id, $password, $post_id);
}
add_action('save_post', 'hbl_create_user_and_site', 10, 3);

/**
 * Create a sub-site for the business
 */
function hbl_create_business_subsite($post_id, $user_id, $business_name, $username) {
    // Check if multisite is enabled
    if (!is_multisite()) {
        return;
    }
    
    // Get domain and path
    $domain = get_network()->domain;
    $path = '/' . $username . '/';
    
    // Create the sub-site
    $site_id = wpmu_create_blog(
        $domain,
        $path,
        $business_name,
        $user_id
    );
    
    if (is_wp_error($site_id)) {
        // Log error if logging is enabled
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] Error creating sub-site for business listing #$post_id: " . $site_id->get_error_message());
        }
        return;
    }
    
    // Store site ID in business listing
    hbl_update_field('site_id', $site_id, $post_id);
    
    // Add business details to the sub-site
    switch_to_blog($site_id);
    
    // Set up the site
    hbl_setup_business_site($post_id, $business_name);
    
    // Switch back to main site
    restore_current_blog();
}

/**
 * Set up the business sub-site with pages and content
 */
function hbl_setup_business_site($post_id, $business_name) {
    // Get business details
    $company_type = hbl_get_field('company_type', $post_id);
    $location = hbl_get_field('location', $post_id);
    $website = hbl_get_field('website', $post_id);
    $social_media = hbl_get_field('social_media', $post_id);
    $whatsapp_number = hbl_get_field('whatsapp_number', $post_id);
    
    // Create the home page
    $home_content = "
        <h1>Welcome to $business_name</h1>
        <p>We are a leading provider of quality services and products.</p>
    ";
    
    $home_page = wp_insert_post(array(
        'post_title' => 'Home',
        'post_content' => $home_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Create the about page
    $about_content = "
        <h1>About $business_name</h1>
        <p>$business_name is a " . ($company_type ? esc_html($company_type) : 'company') . " based in " . ($location ? esc_html($location) : 'our location') . ".</p>
        <p>We are committed to providing excellent service to our customers.</p>
    ";
    
    $about_page = wp_insert_post(array(
        'post_title' => 'About',
        'post_content' => $about_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Create the services page
    $services_content = "
        <h1>Our Services</h1>
        <p>$business_name offers a wide range of services to meet your needs.</p>
        <p>Contact us to learn more about how we can help you.</p>
    ";
    
    $services_page = wp_insert_post(array(
        'post_title' => 'Services',
        'post_content' => $services_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Create the contact page
    $contact_content = "
        <h1>Contact Us</h1>
        <p>Get in touch with $business_name:</p>
        <ul>
    ";
    
    if ($location) {
        $contact_content .= "<li><strong>Location:</strong> " . esc_html($location) . "</li>";
    }
    
    if ($website) {
        $contact_content .= "<li><strong>Website:</strong> <a href='" . esc_url($website) . "' target='_blank'>" . esc_html($website) . "</a></li>";
    }
    
    if ($social_media) {
        $contact_content .= "<li><strong>Social Media:</strong> " . esc_html($social_media) . "</li>";
    }
    
    if ($whatsapp_number) {
        $contact_content .= "<li><strong>WhatsApp:</strong> <a href='https://wa.me/" . esc_attr(preg_replace('/[^0-9]/', '', $whatsapp_number)) . "' target='_blank'>" . esc_html($whatsapp_number) . "</a></li>";
    }
    
    $contact_content .= "
        </ul>
        <p>We look forward to hearing from you!</p>
    ";
    
    $contact_page = wp_insert_post(array(
        'post_title' => 'Contact',
        'post_content' => $contact_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Set home page as front page
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home_page);
    
    // Set up navigation menu
    $menu_name = 'Business Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);
    
    if (!$menu_exists) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        // Add pages to menu
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => 'Home',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $home_page,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ));
        
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => 'About',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $about_page,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ));
        
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => 'Services',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $services_page,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ));
        
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => 'Contact',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $contact_page,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ));
        
        // Set menu location
        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}

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
