<?php
// Hook into the post save action to create a user and sub-site
function hbl_create_user_and_site($post_id, $post, $update) {
    if ($post->post_type != 'business_listing' || $update) {
        return;
    }

    $business_name = get_field('business_name', $post_id);
    $username = sanitize_user($business_name);
    $password = wp_generate_password();
    $email = $username . '@example.com';

    if (username_exists($username) || email_exists($email)) {
        return;
    }

    // Create the user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return;
    }

    // Create the sub-site
    $site_id = wpmu_create_blog(
        get_network()->domain,
        '/' . $username,
        $business_name,
        $user_id
    );

    if (is_wp_error($site_id)) {
        return;
    }

    // Add business details to the sub-site
    switch_to_blog($site_id);

    // Create the pages
    $home_page = wp_insert_post(array(
        'post_title' => 'Home',
        'post_content' => "Welcome to $business_name's site!",
        'post_status' => 'publish',
        'post_type' => 'page',
    ));

    $about_page = wp_insert_post(array(
        'post_title' => 'About',
        'post_content' => 'About Us content here.',
        'post_status' => 'publish',
        'post_type' => 'page',
    ));

    $services_page = wp_insert_post(array(
        'post_title' => 'Services',
        'post_content' => 'Our Services content here.',
        'post_status' => 'publish',
        'post_type' => 'page',
    ));

    $contact_page = wp_insert_post(array(
        'post_title' => 'Contact',
        'post_content' => 'Contact Us content here.',
        'post_status' => 'publish',
        'post_type' => 'page',
    ));

    restore_current_blog();
}
add_action('save_post', 'hbl_create_user_and_site', 10, 3);
?>
