<?php
/**
 * Sub-site Creation for Happy Business Listing
 * 
 * Handles the creation and setup of sub-sites for business listings
 * in a multisite WordPress environment.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create a sub-site for the business
 *
 * @param int $post_id The business listing post ID
 * @param int $user_id The user ID of the business owner
 * @param string $business_name The name of the business
 * @param string $username The username for the site URL
 * @return int|WP_Error Site ID on success, WP_Error on failure
 */
function hbl_create_business_subsite($post_id, $user_id, $business_name, $username) {
    // Check if multisite is enabled
    if (!is_multisite()) {
        return new WP_Error('not_multisite', __('WordPress is not in multisite mode.', 'happy-business-listing'));
    }
    
    // Verify the business listing exists
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'business_listing') {
        return new WP_Error('invalid_business', __('Invalid business listing.', 'happy-business-listing'));
    }
    
    // Verify the user exists
    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('invalid_user', __('Invalid user.', 'happy-business-listing'));
    }
    
    // Get domain and path
    $domain = get_network()->domain;
    $path = '/' . sanitize_title($username) . '/';
    
    // Check if site already exists
    $site_id = domain_exists($domain, $path);
    if ($site_id) {
        // Site already exists, return the existing site ID
        hbl_update_field('site_id', $site_id, $post_id);
        return $site_id;
    }
    
    // Get site creation settings
    $template_id = get_option('hbl_subsite_template', 0);
    $site_title = $business_name;
    
    // Allow filtering of site title
    $site_title = apply_filters('hbl_subsite_title', $site_title, $post_id, $user_id);
    
    // Create the sub-site
    $site_id = wpmu_create_blog(
        $domain,
        $path,
        $site_title,
        $user_id
    );
    
    if (is_wp_error($site_id)) {
        // Log error if logging is enabled
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] Error creating sub-site for business listing #$post_id: " . $site_id->get_error_message());
        }
        return $site_id;
    }
    
    // Store site ID in business listing
    hbl_update_field('site_id', $site_id, $post_id);
    
    // Add business details to the sub-site
    switch_to_blog($site_id);
    
    // Set up the site
    hbl_setup_business_site($post_id, $business_name);
    
    // Copy template content if a template site is specified
    if ($template_id > 0) {
        hbl_copy_template_content($template_id, $site_id, $post_id);
    }
    
    // Switch back to main site
    restore_current_blog();
    
    // Fire action after site creation
    do_action('hbl_after_subsite_creation', $site_id, $post_id, $user_id);
    
    return $site_id;
}

/**
 * Set up the business sub-site with pages and content
 *
 * @param int $post_id The business listing post ID
 * @param string $business_name The name of the business
 */
function hbl_setup_business_site($post_id, $business_name) {
    // Get business details
    $company_type = hbl_get_field('company_type', $post_id);
    $location = hbl_get_field('location', $post_id);
    $website = hbl_get_field('website', $post_id);
    $social_media = hbl_get_field('social_media', $post_id);
    $whatsapp_number = hbl_get_field('whatsapp_number', $post_id);
    
    // Get page content templates from settings
    $home_content_template = get_option('hbl_home_page_template', '');
    $about_content_template = get_option('hbl_about_page_template', '');
    $services_content_template = get_option('hbl_services_page_template', '');
    $contact_content_template = get_option('hbl_contact_page_template', '');
    
    // If templates are empty, use defaults
    if (empty($home_content_template)) {
        $home_content = "
            <h1>Welcome to $business_name</h1>
            <p>We are a leading provider of quality services and products.</p>
        ";
    } else {
        $home_content = str_replace(
            array('{business_name}', '{company_type}', '{location}'),
            array($business_name, $company_type, $location),
            $home_content_template
        );
    }
    
    // Create the home page
    $home_page = wp_insert_post(array(
        'post_title' => 'Home',
        'post_content' => $home_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Create the about page with template or default content
    if (empty($about_content_template)) {
        $about_content = "
            <h1>About $business_name</h1>
            <p>$business_name is a " . ($company_type ? esc_html($company_type) : 'company') . " based in " . ($location ? esc_html($location) : 'our location') . ".</p>
            <p>We are committed to providing excellent service to our customers.</p>
        ";
    } else {
        $about_content = str_replace(
            array('{business_name}', '{company_type}', '{location}'),
            array($business_name, $company_type, $location),
            $about_content_template
        );
    }
    
    $about_page = wp_insert_post(array(
        'post_title' => 'About',
        'post_content' => $about_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Create the services page with template or default content
    if (empty($services_content_template)) {
        $services_content = "
            <h1>Our Services</h1>
            <p>$business_name offers a wide range of services to meet your needs.</p>
            <p>Contact us to learn more about how we can help you.</p>
        ";
    } else {
        $services_content = str_replace(
            array('{business_name}', '{company_type}', '{location}'),
            array($business_name, $company_type, $location),
            $services_content_template
        );
    }
    
    $services_page = wp_insert_post(array(
        'post_title' => 'Services',
        'post_content' => $services_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    
    // Create the contact page with template or default content
    if (empty($contact_content_template)) {
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
    } else {
        $contact_content = str_replace(
            array('{business_name}', '{location}', '{website}', '{social_media}', '{whatsapp_number}'),
            array(
                $business_name, 
                $location, 
                $website ? '<a href="' . esc_url($website) . '" target="_blank">' . esc_html($website) . '</a>' : '',
                $social_media,
                $whatsapp_number ? '<a href="https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $whatsapp_number)) . '" target="_blank">' . esc_html($whatsapp_number) . '</a>' : ''
            ),
            $contact_content_template
        );
    }
    
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
    
    // Apply theme settings if specified
    $theme = get_option('hbl_subsite_theme', '');
    if (!empty($theme)) {
        switch_theme($theme);
    }
    
    // Fire action after site setup
    do_action('hbl_after_subsite_setup', get_current_blog_id(), $post_id);
}

/**
 * Copy content from a template site to a new business site
 *
 * @param int $template_id The template site ID
 * @param int $site_id The new site ID
 * @param int $post_id The business listing post ID
 */
function hbl_copy_template_content($template_id, $site_id, $post_id) {
    // Only proceed if both sites exist
    if (!get_site($template_id) || !get_site($site_id)) {
        return;
    }
    
    // Get business details for replacements
    $business_name = hbl_get_field('business_name', $post_id);
    $company_type = hbl_get_field('company_type', $post_id);
    $location = hbl_get_field('location', $post_id);
    
    // Switch to template site to get content
    switch_to_blog($template_id);
    
    // Get template pages
    $template_pages = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));
    
    // Store template content
    $pages_data = array();
    foreach ($template_pages as $page) {
        $pages_data[] = array(
            'title' => $page->post_title,
            'content' => $page->post_content,
            'template' => get_page_template_slug($page->ID),
            'menu_order' => $page->menu_order,
        );
    }
    
    // Get template menus
    $template_menus = wp_get_nav_menus();
    $menus_data = array();
    
    foreach ($template_menus as $menu) {
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        $menus_data[$menu->name] = $menu_items;
    }
    
    // Switch back to main site
    restore_current_blog();
    
    // Switch to new site to create content
    switch_to_blog($site_id);
    
    // Create pages based on template
    $page_ids = array();
    foreach ($pages_data as $page_data) {
        // Replace placeholders in content
        $content = str_replace(
            array('{business_name}', '{company_type}', '{location}'),
            array($business_name, $company_type, $location),
            $page_data['content']
        );
        
        // Create the page
        $page_id = wp_insert_post(array(
            'post_title' => $page_data['title'],
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'menu_order' => $page_data['menu_order'],
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            $page_ids[$page_data['title']] = $page_id;
            
            // Set page template if any
            if (!empty($page_data['template'])) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            }
        }
    }
    
    // Set home page if it exists
    if (isset($page_ids['Home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_ids['Home']);
    }
    
    // Create menus based on template
    foreach ($menus_data as $menu_name => $menu_items) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        if (is_wp_error($menu_id)) {
            continue;
        }
        
        // Add menu items
        foreach ($menu_items as $item) {
            $menu_item_data = array(
                'menu-item-title' => $item->title,
                'menu-item-status' => 'publish',
            );
            
            // Handle different types of menu items
            if ($item->type === 'post_type' && $item->object === 'page') {
                // Find corresponding page in new site
                $page_title = $item->title;
                if (isset($page_ids[$page_title])) {
                    $menu_item_data['menu-item-object'] = 'page';
                    $menu_item_data['menu-item-object-id'] = $page_ids[$page_title];
                    $menu_item_data['menu-item-type'] = 'post_type';
                }
            } elseif ($item->type === 'custom') {
                $menu_item_data['menu-item-url'] = $item->url;
                $menu_item_data['menu-item-type'] = 'custom';
            }
            
            if (!empty($menu_item_data['menu-item-type'])) {
                wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
            }
        }
        
        // Set menu location
        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
    
    // Switch back to main site
    restore_current_blog();
}

/**
 * Register settings for sub-site creation
 */
function hbl_register_subsite_settings() {
    // Register settings
    register_setting('hbl_options_group', 'hbl_enable_subsite_creation', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_subsite_template', 'absint');
    register_setting('hbl_options_group', 'hbl_subsite_theme', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_home_page_template', 'wp_kses_post');
    register_setting('hbl_options_group', 'hbl_about_page_template', 'wp_kses_post');
    register_setting('hbl_options_group', 'hbl_services_page_template', 'wp_kses_post');
    register_setting('hbl_options_group', 'hbl_contact_page_template', 'wp_kses_post');
    
    // Add settings section
    add_settings_section(
        'hbl_subsite_section',
        __('Sub-site Creation Settings', 'happy-business-listing'),
        'hbl_subsite_section_callback',
        'hbl_options_group'
    );
    
    // Add settings fields
    add_settings_field(
        'hbl_enable_subsite_creation',
        __('Enable Sub-site Creation', 'happy-business-listing'),
        'hbl_enable_subsite_creation_callback',
        'hbl_options_group',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_subsite_template',
        __('Template Site', 'happy-business-listing'),
        'hbl_subsite_template_callback',
        'hbl_options_group',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_subsite_theme',
        __('Default Theme', 'happy-business-listing'),
        'hbl_subsite_theme_callback',
        'hbl_options_group',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_page_templates',
        __('Page Templates', 'happy-business-listing'),
        'hbl_page_templates_callback',
        'hbl_options_group',
        'hbl_subsite_section'
    );
}
add_action('admin_init', 'hbl_register_subsite_settings');

/**
 * Settings section callback
 */
function hbl_subsite_section_callback() {
    echo '<p>' . __('Configure settings for automatic sub-site creation for business listings.', 'happy-business-listing') . '</p>';
    
    if (!is_multisite()) {
        echo '<div class="notice notice-warning inline"><p>' . __('WordPress is not in multisite mode. Sub-site creation will be disabled.', 'happy-business-listing') . '</p></div>';
    }
}

/**
 * Enable sub-site creation field callback
 */
function hbl_enable_subsite_creation_callback() {
    $value = get_option('hbl_enable_subsite_creation', '0');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<label><input type="checkbox" name="hbl_enable_subsite_creation" value="1" ' . checked('1', $value, false) . ' ' . $disabled . '> ' . __('Automatically create a sub-site for each business listing', 'happy-business-listing') . '</label>';
    
    if (!is_multisite()) {
        echo '<p class="description">' . __('This option requires WordPress multisite to be enabled.', 'happy-business-listing') . '</p>';
    }
}

/**
 * Template site field callback
 */
function hbl_subsite_template_callback() {
    $value = get_option('hbl_subsite_template', '0');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<select name="hbl_subsite_template" ' . $disabled . '>';
    echo '<option value="0">' . __('None (Use default pages)', 'happy-business-listing') . '</option>';
    
    if (is_multisite()) {
        $sites = get_sites(array('number' => 100));
        foreach ($sites as $site) {
            $site_id = $site->blog_id;
            $site_name = get_blog_details($site_id)->blogname;
            echo '<option value="' . esc_attr($site_id) . '" ' . selected($value, $site_id, false) . '>' . esc_html($site_name) . '</option>';
        }
    }
    
    echo '</select>';
    echo '<p class="description">' . __('Select a site to use as a template for new business sub-sites.', 'happy-business-listing') . '</p>';
}

/**
 * Default theme field callback
 */
function hbl_subsite_theme_callback() {
    $value = get_option('hbl_subsite_theme', '');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<select name="hbl_subsite_theme" ' . $disabled . '>';
    echo '<option value="">' . __('Default Theme', 'happy-business-listing') . '</option>';
    
    $themes = wp_get_themes();
    foreach ($themes as $theme_slug => $theme) {
        echo '<option value="' . esc_attr($theme_slug) . '" ' . selected($value, $theme_slug, false) . '>' . esc_html($theme->get('Name')) . '</option>';
    }
    
    echo '</select>';
    echo '<p class="description">' . __('Select a theme to use for new business sub-sites.', 'happy-business-listing') . '</p>';
}

/**
 * Page templates field callback
 */
function hbl_page_templates_callback() {
    $home_template = get_option('hbl_home_page_template', '');
    $about_template = get_option('hbl_about_page_template', '');
    $services_template = get_option('hbl_services_page_template', '');
    $contact_template = get_option('hbl_contact_page_template', '');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<h4>' . __('Home Page Template', 'happy-business-listing') . '</h4>';
    echo '<textarea name="hbl_home_page_template" rows="4" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($home_template) . '</textarea>';
    echo '<p class="description">' . __('Template for the home page. Use {business_name}, {company_type}, and {location} as placeholders.', 'happy-business-listing') . '</p>';
    
    echo '<h4>' . __('About Page Template', 'happy-business-listing') . '</h4>';
    echo '<textarea name="hbl_about_page_template" rows="4" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($about_template) . '</textarea>';
    echo '<p class="description">' . __('Template for the about page. Use {business_name}, {company_type}, and {location} as placeholders.', 'happy-business-listing') . '</p>';
    
    echo '<h4>' . __('Services Page Template', 'happy-business-listing') . '</h4>';
    echo '<textarea name="hbl_services_page_template" rows="4" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($services_template) . '</textarea>';
    echo '<p class="description">' . __('Template for the services page. Use {business_name}, {company_type}, and {location} as placeholders.', 'happy-business-listing') . '</p>';
    
    echo '<h4>' . __('Contact Page Template', 'happy-business-listing') . '</h4>';
    echo '<textarea name="hbl_contact_page_template" rows="4" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($contact_template) . '</textarea>';
    echo '<p class="description">' . __('Template for the contact page. Use {business_name}, {location}, {website}, {social_media}, and {whatsapp_number} as placeholders.', 'happy-business-listing') . '</p>';
}

/**
 * Add sub-site management metabox to business listings
 */
function hbl_add_subsite_metabox() {
    // Only add if multisite is enabled
    if (!is_multisite()) {
        return;
    }
    
    add_meta_box(
        'hbl_subsite_management',
        __('Sub-site Management', 'happy-business-listing'),
        'hbl_subsite_metabox_callback',
        'business_listing',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'hbl_add_subsite_metabox');

/**
 * Sub-site management metabox callback
 */
function hbl_subsite_metabox_callback($post) {
    // Get site ID if it exists
    $site_id = hbl_get_field('site_id', $post->ID);
    
    if ($site_id) {
        $site_url = get_site_url($site_id);
        $site_name = get_blog_details($site_id)->blogname;
        
        echo '<p><strong>' . __('Site Name:', 'happy-business-listing') . '</strong> ' . esc_html($site_name) . '</p>';
        echo '<p><strong>' . __('Site URL:', 'happy-business-listing') . '</strong> <a href="' . esc_url($site_url) . '" target="_blank">' . esc_url($site_url) . '</a></p>';
        
        // Add admin link
        $admin_url = get_admin_url($site_id);
        echo '<p><a href="' . esc_url($admin_url) . '" target="_blank" class="button">' . __('Manage Site', 'happy-business-listing') . '</a></p>';
        
        // Add option to recreate pages
        echo '<p><button type="button" id="hbl-recreate-pages" class="button" data-post-id="' . esc_attr($post->ID) . '" data-site-id="' . esc_attr($site_id) . '">' . __('Recreate Pages', 'happy-business-listing') . '</button></p>';
        
        // Add nonce for AJAX
        wp_nonce_field('hbl_recreate_pages', 'hbl_recreate_pages_nonce');
    } else {
        echo '<p>' . __('No sub-site has been created for this business listing yet.', 'happy-business-listing') . '</p>';
        
        // Add option to create site manually
        echo '<p><button type="button" id="hbl-create-site" class="button" data-post-id="' . esc_attr($post->ID) . '">' . __('Create Sub-site', 'happy-business-listing') . '</button></p>';
        
        // Add nonce for AJAX
        wp_nonce_field('hbl_create_site', 'hbl_create_site_nonce');
    }
    
    // Add JavaScript for AJAX
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Create site button
        $('#hbl-create-site').on('click', function() {
            var postId = $(this).data('post-id');
            var nonce = $('#hbl_create_site_nonce').val();
            
            $(this).prop('disabled', true).text('<?php _e('Creating...', 'happy-business-listing'); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hbl_create_site_manually',
                    post_id: postId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Sub-site created successfully!', 'happy-business-listing'); ?>');
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php _e('Error creating sub-site.', 'happy-business-listing'); ?>');
                        $('#hbl-create-site').prop('disabled', false).text('<?php _e('Create Sub-site', 'happy-business-listing'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error creating sub-site.', 'happy-business-listing'); ?>');
                    $('#hbl-create-site').prop('disabled', false).text('<?php _e('Create Sub-site', 'happy-business-listing'); ?>');
                }
            });
        });
        
        // Recreate pages button
        $('#hbl-recreate-pages').on('click', function() {
            var postId = $(this).data('post-id');
            var siteId = $(this).data('site-id');
            var nonce = $('#hbl_recreate_pages_nonce').val();
            
            $(this).prop('disabled', true).text('<?php _e('Recreating...', 'happy-business-listing'); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hbl_recreate_pages',
                    post_id: postId,
                    site_id: siteId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Pages recreated successfully!', 'happy-business-listing'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('Error recreating pages.', 'happy-business-listing'); ?>');
                    }
                    $('#hbl-recreate-pages').prop('disabled', false).text('<?php _e('Recreate Pages', 'happy-business-listing'); ?>');
                },
                error: function() {
                    alert('<?php _e('Error recreating pages.', 'happy-business-listing'); ?>');
                    $('#hbl-recreate-pages').prop('disabled', false).text('<?php _e('Recreate Pages', 'happy-business-listing'); ?>');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX handler for creating a site manually
 */
function hbl_ajax_create_site_manually() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hbl_create_site')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'happy-business-listing')));
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to do this.', 'happy-business-listing')));
    }
    
    // Check post ID
    if (!isset($_POST['post_id']) || !get_post($_POST['post_id'])) {
        wp_send_json_error(array('message' => __('Invalid business listing.', 'happy-business-listing')));
    }
    
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    
    // Verify it's a business listing
    if ($post->post_type !== 'business_listing') {
        wp_send_json_error(array('message' => __('Invalid business listing type.', 'happy-business-listing')));
    }
    
    // Get business details
    $business_name = hbl_get_field('business_name', $post_id);
    if (empty($business_name)) {
        $business_name = $post->post_title;
    }
    
    // Get user ID
    $user_id = hbl_get_field('user_id', $post_id);
    if (empty($user_id)) {
        $user_id = $post->post_author;
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
    
    // Create the sub-site
    $site_id = hbl_create_business_subsite($post_id, $user_id, $business_name, $username);
    
    if (is_wp_error($site_id)) {
        wp_send_json_error(array('message' => $site_id->get_error_message()));
    }
    
    wp_send_json_success(array('site_id' => $site_id));
}
add_action('wp_ajax_hbl_create_site_manually', 'hbl_ajax_create_site_manually');

/**
 * AJAX handler for recreating pages
 */
function hbl_ajax_recreate_pages() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hbl_recreate_pages')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'happy-business-listing')));
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to do this.', 'happy-business-listing')));
    }
    
    // Check post ID and site ID
    if (!isset($_POST['post_id']) || !isset($_POST['site_id'])) {
        wp_send_json_error(array('message' => __('Missing required parameters.', 'happy-business-listing')));
    }
    
    $post_id = intval($_POST['post_id']);
    $site_id = intval($_POST['site_id']);
    
    // Verify the post and site exist
    if (!get_post($post_id) || !get_site($site_id)) {
        wp_send_json_error(array('message' => __('Invalid business listing or site.', 'happy-business-listing')));
    }
    
    // Get business name
    $business_name = hbl_get_field('business_name', $post_id);
    if (empty($business_name)) {
        $business_name = get_post($post_id)->post_title;
    }
    
    // Switch to the site and recreate pages
    switch_to_blog($site_id);
    hbl_setup_business_site($post_id, $business_name);
    restore_current_blog();
    
    wp_send_json_success();
}
add_action('wp_ajax_hbl_recreate_pages', 'hbl_ajax_recreate_pages');

/**
 * Helper function to get field value with ACF fallback
 */
function hbl_get_field($field_name, $post_id) {
    // Try ACF first if available
    if (function_exists('get_field')) {
        return get_field($field_name, $post_id);
    }
    
    // Fall back to post meta
    return get_post_meta($post_id, $field_name, true);
}

/**
 * Helper function to update field value with ACF fallback
 */
function hbl_update_field($field_name, $value, $post_id) {
    // Try ACF first if available
    if (function_exists('update_field')) {
        return update_field($field_name, $value, $post_id);
    }
    
    // Fall back to post meta
    return update_post_meta($post_id, $field_name, $value);
}