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
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error creating sub-site for business listing',
                'error',
                array(
                    'business_id' => $post_id,
                    'user_id' => $user_id,
                    'error' => $site_id->get_error_message()
                )
            );
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
    
    // Log successful site creation
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Sub-site created for business listing',
            'info',
            array(
                'business_id' => $post_id,
                'user_id' => $user_id,
                'site_id' => $site_id
            )
        );
    }
    
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
    $company_type = hbl_get_business_field('company_type', $post_id);
    $location = hbl_get_business_field('location', $post_id);
    $website = hbl_get_business_field('website', $post_id);
    $social_media = hbl_get_business_field('social_media', $post_id);
    $whatsapp_number = hbl_get_business_field('whatsapp_number', $post_id);
    $email = hbl_get_business_field('email', $post_id);
    $phone = hbl_get_business_field('phone', $post_id);
    
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
            <p>Explore our website to learn more about what we offer.</p>
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
            <p>Our mission is to deliver high-quality products and services that meet the needs of our clients.</p>
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
            <div class='services-list'>
                <div class='service-item'>
                    <h3>Service 1</h3>
                    <p>Description of service 1.</p>
                </div>
                <div class='service-item'>
                    <h3>Service 2</h3>
                    <p>Description of service 2.</p>
                </div>
                <div class='service-item'>
                    <h3>Service 3</h3>
                    <p>Description of service 3.</p>
                </div>
            </div>
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
            <div class='contact-info'>
                <ul>
        ";
        
        if ($location) {
            $contact_content .= "<li><strong>Location:</strong> " . esc_html($location) . "</li>";
        }
        
        if ($email) {
            $contact_content .= "<li><strong>Email:</strong> <a href='mailto:" . esc_attr($email) . "'>" . esc_html($email) . "</a></li>";
        }
        
        if ($phone) {
            $contact_content .= "<li><strong>Phone:</strong> <a href='tel:" . esc_attr(preg_replace('/[^0-9+]/', '', $phone)) . "'>" . esc_html($phone) . "</a></li>";
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
            </div>
            <div class='contact-form'>
                <h2>Send us a message</h2>
                " . do_shortcode('[business_contact_form business_id=\"' . $post_id . '\"]') . "
            </div>
        ";
    } else {
        $contact_content = str_replace(
            array(
                '{business_name}', 
                '{location}', 
                '{email}',
                '{phone}',
                '{website}', 
                '{social_media}', 
                '{whatsapp_number}',
                '{contact_form}'
            ),
            array(
                $business_name, 
                $location, 
                $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '',
                $phone ? '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $phone)) . '">' . esc_html($phone) . '</a>' : '',
                $website ? '<a href="' . esc_url($website) . '" target="_blank">' . esc_html($website) . '</a>' : '',
                $social_media,
                $whatsapp_number ? '<a href="https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $whatsapp_number)) . '" target="_blank">' . esc_html($whatsapp_number) . '</a>' : '',
                do_shortcode('[business_contact_form business_id=\"' . $post_id . '\"]')
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
    
    // Add custom CSS for the site
    $custom_css = "
        /* Custom CSS for $business_name */
        .site-title a {
            color: #333;
        }
        
        .services-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .service-item {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .contact-info {
            margin-bottom: 30px;
        }
        
        .contact-info ul {
            list-style: none;
            padding: 0;
        }
        
        .contact-info li {
            margin-bottom: 10px;
        }
        
        .contact-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
    ";
    
    // Add the custom CSS
    wp_update_custom_css_post($custom_css);
    
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
    $business_name = hbl_get_business_field('business_name', $post_id);
    $company_type = hbl_get_business_field('company_type', $post_id);
    $location = hbl_get_business_field('location', $post_id);
    $email = hbl_get_business_field('email', $post_id);
    $phone = hbl_get_business_field('phone', $post_id);
    $website = hbl_get_business_field('website', $post_id);
    $whatsapp_number = hbl_get_business_field('whatsapp_number', $post_id);
    
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
    
    // Get template widgets
    $sidebars_widgets = get_option('sidebars_widgets');
    $widget_options = array();
    
    foreach ($sidebars_widgets as $sidebar => $widgets) {
        if (is_array($widgets)) {
            foreach ($widgets as $widget) {
                $widget_type = preg_replace('/-[0-9]+$/', '', $widget);
                $widget_options[$widget] = get_option('widget_' . $widget_type);
            }
        }
    }
    
    // Get template theme mods
    $theme_mods = get_theme_mods();
    
    // Get template options
    $template_options = array(
        'blogname' => get_option('blogname'),
        'blogdescription' => get_option('blogdescription'),
        'posts_per_page' => get_option('posts_per_page'),
        'show_on_front' => get_option('show_on_front'),
        'page_on_front' => get_option('page_on_front'),
        'page_for_posts' => get_option('page_for_posts'),
    );
    
    // Switch back to main site
    restore_current_blog();
    
    // Switch to new site to create content
    switch_to_blog($site_id);
    
    // Create pages based on template
    $page_ids = array();
    foreach ($pages_data as $page_data) {
        // Replace placeholders in content
        $content = str_replace(
            array(
                '{business_name}', 
                '{company_type}', 
                '{location}',
                '{email}',
                '{phone}',
                '{website}',
                '{whatsapp_number}'
            ),
            array(
                $business_name, 
                $company_type, 
                $location,
                $email,
                $phone,
                $website,
                $whatsapp_number
            ),
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
    
    // Set theme mods
    foreach ($theme_mods as $key => $value) {
        // Skip nav_menu_locations as we've already set it
        if ($key !== 'nav_menu_locations') {
            set_theme_mod($key, $value);
        }
    }
    
    // Set options
    foreach ($template_options as $key => $value) {
        // Skip blogname as we want to use the business name
        if ($key === 'blogname') {
            update_option($key, $business_name);
        } 
        // Skip page_on_front as we've already set it
        elseif ($key !== 'page_on_front') {
            update_option($key, $value);
        }
    }
    
    // Switch back to main site
    restore_current_blog();
}

/**
 * Sanitize template content with null handling
 *
 * @param mixed $content The content to sanitize
 * @return string The sanitized content
 */
function hbl_sanitize_template_content($content) {
    // Handle null values
    if ($content === null) {
        return '';
    }
    
    // Use wp_kses_post for sanitization
    return wp_kses_post($content);
}

/**
 * Register sub-site settings
 */
function hbl_register_subsite_settings() {
    // Register settings
    register_setting('hbl_options_group', 'hbl_enable_subsite_creation', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_subsite_template', 'absint');
    register_setting('hbl_options_group', 'hbl_subsite_theme', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_home_page_template', 'hbl_sanitize_template_content');
    register_setting('hbl_options_group', 'hbl_about_page_template', 'hbl_sanitize_template_content');
    register_setting('hbl_options_group', 'hbl_services_page_template', 'hbl_sanitize_template_content');
    register_setting('hbl_options_group', 'hbl_contact_page_template', 'hbl_sanitize_template_content');
    
    // Add settings section
    add_settings_section(
        'hbl_subsite_section',
        __('Sub-site Creation Settings', 'happy-business-listing'),
        'hbl_subsite_section_callback',
        'hbl_subsite_settings'
    );
    
    // Add settings fields
    add_settings_field(
        'hbl_enable_subsite_creation',
        __('Enable Sub-site Creation', 'happy-business-listing'),
        'hbl_enable_subsite_creation_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_subsite_template',
        __('Template Site', 'happy-business-listing'),
        'hbl_subsite_template_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_subsite_theme',
        __('Default Theme', 'happy-business-listing'),
        'hbl_subsite_theme_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_home_page_template',
        __('Home Page Template', 'happy-business-listing'),
        'hbl_home_page_template_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_about_page_template',
        __('About Page Template', 'happy-business-listing'),
        'hbl_about_page_template_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_services_page_template',
        __('Services Page Template', 'happy-business-listing'),
        'hbl_services_page_template_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
    
    add_settings_field(
        'hbl_contact_page_template',
        __('Contact Page Template', 'happy-business-listing'),
        'hbl_contact_page_template_callback',
        'hbl_subsite_settings',
        'hbl_subsite_section'
    );
}
add_action('admin_init', 'hbl_register_subsite_settings');

/**
 * Sub-site settings section callback
 */
function hbl_subsite_section_callback() {
    echo '<p>' . __('Configure settings for automatic sub-site creation for business listings.', 'happy-business-listing') . '</p>';
    
    if (!is_multisite()) {
        echo '<div class="notice notice-warning inline"><p>' . __('WordPress is not in multisite mode. Sub-site creation will not work.', 'happy-business-listing') . '</p></div>';
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
        echo '<p class="description">' . __('This option requires WordPress to be in multisite mode.', 'happy-business-listing') . '</p>';
    }
}

/**
 * Template site field callback
 */
function hbl_subsite_template_callback() {
    $value = get_option('hbl_subsite_template', 0);
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<select name="hbl_subsite_template" ' . $disabled . '>';
    echo '<option value="0">' . __('None (Create from scratch)', 'happy-business-listing') . '</option>';
    
    if (is_multisite()) {
        $sites = get_sites(array('number' => 100));
        foreach ($sites as $site) {
            $site_id = $site->blog_id;
            $site_name = get_blog_details($site_id)->blogname;
            echo '<option value="' . esc_attr($site_id) . '" ' . selected($value, $site_id, false) . '>' . esc_html($site_name) . ' (ID: ' . $site_id . ')</option>';
        }
    }
    
    echo '</select>';
    echo '<p class="description">' . __('Select a site to use as a template for new business sites. All content, menus, and settings will be copied.', 'happy-business-listing') . '</p>';
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
    echo '<p class="description">' . __('Select a theme to use for new business sites. If not specified, the default theme will be used.', 'happy-business-listing') . '</p>';
}

/**
 * Home page template field callback
 */
function hbl_home_page_template_callback() {
    $value = get_option('hbl_home_page_template', '');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<textarea name="hbl_home_page_template" rows="5" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Enter the template for the home page. You can use the following placeholders: {business_name}, {company_type}, {location}', 'happy-business-listing') . '</p>';
    echo '<p class="description">' . __('If left empty, a default template will be used.', 'happy-business-listing') . '</p>';
}

/**
 * About page template field callback
 */
function hbl_about_page_template_callback() {
    $value = get_option('hbl_about_page_template', '');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<textarea name="hbl_about_page_template" rows="5" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Enter the template for the about page. You can use the following placeholders: {business_name}, {company_type}, {location}', 'happy-business-listing') . '</p>';
    echo '<p class="description">' . __('If left empty, a default template will be used.', 'happy-business-listing') . '</p>';
}

/**
 * Services page template field callback
 */
function hbl_services_page_template_callback() {
    $value = get_option('hbl_services_page_template', '');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<textarea name="hbl_services_page_template" rows="5" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Enter the template for the services page. You can use the following placeholders: {business_name}, {company_type}, {location}', 'happy-business-listing') . '</p>';
    echo '<p class="description">' . __('If left empty, a default template will be used.', 'happy-business-listing') . '</p>';
}

/**
 * Contact page template field callback
 */
function hbl_contact_page_template_callback() {
    $value = get_option('hbl_contact_page_template', '');
    $disabled = !is_multisite() ? 'disabled' : '';
    
    echo '<textarea name="hbl_contact_page_template" rows="5" cols="50" class="large-text code" ' . $disabled . '>' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Enter the template for the contact page. You can use the following placeholders: {business_name}, {location}, {email}, {phone}, {website}, {social_media}, {whatsapp_number}, {contact_form}', 'happy-business-listing') . '</p>';
    echo '<p class="description">' . __('If left empty, a default template will be used.', 'happy-business-listing') . '</p>';
}

/**
 * Add sub-site tab to settings page
 *
 * @param array $tabs The existing tabs
 * @return array The modified tabs
 */
function hbl_add_subsite_tab($tabs) {
    $tabs['subsite'] = __('Sub-sites', 'happy-business-listing');
    return $tabs;
}
add_filter('hbl_settings_tabs', 'hbl_add_subsite_tab');

/**
 * Display sub-site tab content
 */
function hbl_display_subsite_tab() {
    ?>
    <div id="hbl-subsite-tab" class="hbl-tab-content">
        <h2><?php _e('Sub-site Creation Settings', 'happy-business-listing'); ?></h2>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('hbl_options_group');
            do_settings_sections('hbl_subsite_settings');
            submit_button();
            ?>
        </form>
        
        <?php if (is_multisite() && get_option('hbl_enable_subsite_creation') == '1'): ?>
            <div class="hbl-subsite-management">
                <h3><?php _e('Manage Business Sub-sites', 'happy-business-listing'); ?></h3>
                <?php hbl_display_business_subsites(); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
add_action('hbl_settings_tab_subsite', 'hbl_display_subsite_tab');

/**
 * Display business sub-sites
 */
function hbl_display_business_subsites() {
    // Get all business listings with site IDs
    $businesses = get_posts(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'site_id',
                'compare' => 'EXISTS',
            ),
        ),
    ));
    
    if (empty($businesses)) {
        echo '<p>' . __('No business sub-sites found.', 'happy-business-listing') . '</p>';
        return;
    }
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('Business Name', 'happy-business-listing') . '</th>';
    echo '<th>' . __('Site ID', 'happy-business-listing') . '</th>';
    echo '<th>' . __('Site URL', 'happy-business-listing') . '</th>';
    echo '<th>' . __('Actions', 'happy-business-listing') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($businesses as $business) {
        $site_id = hbl_get_business_field('site_id', $business->ID);
        $site_details = get_blog_details($site_id);
        
        if (!$site_details) {
            continue;
        }
        
        echo '<tr>';
        echo '<td><a href="' . get_edit_post_link($business->ID) . '">' . esc_html($business->post_title) . '</a></td>';
        echo '<td>' . esc_html($site_id) . '</td>';
        echo '<td><a href="' . esc_url($site_details->siteurl) . '" target="_blank">' . esc_html($site_details->siteurl) . '</a></td>';
        echo '<td>';
        echo '<a href="' . esc_url(get_admin_url($site_id)) . '" target="_blank" class="button button-small">' . __('Dashboard', 'happy-business-listing') . '</a> ';
        echo '<a href="' . esc_url(add_query_arg(array('action' => 'rebuild_site', 'business_id' => $business->ID, 'nonce' => wp_create_nonce('rebuild_site')), admin_url('admin.php?page=hbl_settings&tab=subsite'))) . '" class="button button-small">' . __('Rebuild', 'happy-business-listing') . '</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
}

/**
 * Handle sub-site actions
 */
function hbl_handle_subsite_actions() {
    if (!isset($_GET['action']) || !isset($_GET['business_id']) || !isset($_GET['nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_GET['nonce'], 'rebuild_site')) {
        wp_die(__('Security check failed.', 'happy-business-listing'));
    }
    
    $action = $_GET['action'];
    $business_id = intval($_GET['business_id']);
    
    if ($action === 'rebuild_site') {
        // Get business details
        $business = get_post($business_id);
        if (!$business || $business->post_type !== 'business_listing') {
            wp_die(__('Invalid business listing.', 'happy-business-listing'));
        }
        
        $site_id = hbl_get_business_field('site_id', $business_id);
        if (!$site_id) {
            wp_die(__('No site ID found for this business.', 'happy-business-listing'));
        }
        
        $business_name = hbl_get_business_field('business_name', $business_id);
        if (empty($business_name)) {
            $business_name = $business->post_title;
        }
        
        // Switch to the site
        switch_to_blog($site_id);
        
        // Set up the site again
        hbl_setup_business_site($business_id, $business_name);
        
        // Copy template content if a template site is specified
        $template_id = get_option('hbl_subsite_template', 0);
        if ($template_id > 0) {
            hbl_copy_template_content($template_id, $site_id, $business_id);
        }
        
        // Switch back to main site
        restore_current_blog();
        
        // Redirect back to the settings page
        wp_redirect(admin_url('admin.php?page=hbl_settings&tab=subsite&rebuilt=1'));
        exit;
    }
}
add_action('admin_init', 'hbl_handle_subsite_actions');

/**
 * Display rebuilt notice
 */
function hbl_display_rebuilt_notice() {
    if (isset($_GET['page']) && $_GET['page'] === 'hbl_settings' && isset($_GET['tab']) && $_GET['tab'] === 'subsite' && isset($_GET['rebuilt'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Site rebuilt successfully.', 'happy-business-listing'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'hbl_display_rebuilt_notice');

/**
 * Add documentation for sub-site creation
 */
function hbl_add_subsite_documentation() {
    // Create documentation file if it doesn't exist
    $doc_file = HBL_PLUGIN_DIR . 'docs/subsite-creation.md';
    
    if (!file_exists($doc_file)) {
        $doc_content = "# Sub-site Creation Documentation

The Happy Business Listing plugin includes a powerful feature for automatically creating sub-sites for each business listing in a multisite WordPress environment. This document provides detailed information about this feature, its configuration, and customization options.

## Overview

The sub-site creation feature allows you to:

1. Automatically create a sub-site for each business listing
2. Customize the content of the sub-site with templates
3. Copy content from a template site
4. Apply a specific theme to the sub-site
5. Set up navigation menus and widgets

## Requirements

To use the sub-site creation feature, you need:

- WordPress in multisite mode
- The Happy Business Listing plugin activated network-wide
- Appropriate permissions to create sites

## Configuration

You can configure the sub-site creation feature in the plugin settings under the 'Sub-sites' tab:

### Enable Sub-site Creation

Enable or disable automatic sub-site creation for business listings.

### Template Site

Select an existing site to use as a template for new business sites. All content, menus, and settings will be copied from this site.

### Default Theme

Select a theme to use for new business sites. If not specified, the default theme will be used.

### Page Templates

You can customize the content of the following pages:

- **Home Page**: The front page of the business site
- **About Page**: Information about the business
- **Services Page**: Services offered by the business
- **Contact Page**: Contact information and form

Each template supports placeholders that will be replaced with actual business data:

- `{business_name}`: The name of the business
- `{company_type}`: The type of company (e.g., LLC, Corporation)
- `{location}`: The business location
- `{email}`: The business email address
- `{phone}`: The business phone number
- `{website}`: The business website URL
- `{social_media}`: Social media links
- `{whatsapp_number}`: WhatsApp contact number
- `{contact_form}`: A contact form shortcode (Contact Page only)

## How It Works

When a new business listing is created:

1. The plugin checks if sub-site creation is enabled
2. If enabled, it creates a new sub-site with the business name
3. It sets up the site with the specified theme and templates
4. It creates standard pages (Home, About, Services, Contact)
5. It sets up a navigation menu
6. If a template site is specified, it copies content from that site

## Managing Sub-sites

You can manage business sub-sites from the 'Sub-sites' tab in the plugin settings:

- View all business sub-sites
- Access the dashboard of each sub-site
- Rebuild a sub-site if needed

## Customization

### Hooks and Filters

The sub-site creation process can be customized using the following hooks:

- `hbl_subsite_title`: Filter the title of the sub-site
- `hbl_after_subsite_creation`: Action after a sub-site is created
- `hbl_after_subsite_setup`: Action after a sub-site is set up

Example:

```php
// Customize the sub-site title
add_filter('hbl_subsite_title', function(\$title, \$post_id, \$user_id) {
    return 'Custom Title: ' . \$title;
}, 10, 3);

// Do something after a sub-site is created
add_action('hbl_after_subsite_creation', function(\$site_id, \$post_id, \$user_id) {
    // Custom code here
}, 10, 3);
```

### Template Customization

You can customize the page templates in the plugin settings. Each template supports HTML and shortcodes.

Example Home Page Template:

```html
<h1>Welcome to {business_name}</h1>
<p>{business_name} is a {company_type} based in {location}.</p>
<p>We offer high-quality services to meet your needs.</p>
<div class=\"cta-buttons\">
    <a href=\"/services\" class=\"button\">Our Services</a>
    <a href=\"/contact\" class=\"button\">Contact Us</a>
</div>
```

## Troubleshooting

### Common Issues

#### Sub-site Creation Fails

If sub-site creation fails:

1. Make sure WordPress is in multisite mode
2. Check that the plugin is activated network-wide
3. Verify that the user has permission to create sites
4. Check the error logs for more information

#### Template Content Not Copied

If template content is not copied:

1. Make sure the template site exists
2. Verify that the template site has content
3. Check that the template site is accessible

#### Pages Not Created

If pages are not created:

1. Check the page templates in the plugin settings
2. Verify that the business listing has the required fields
3. Check the error logs for more information

## Best Practices

1. **Create a Template Site**: Set up a template site with the desired content, menus, and settings
2. **Customize Page Templates**: Customize the page templates to match your branding
3. **Use Placeholders**: Use placeholders to dynamically insert business information
4. **Test Before Deployment**: Test the sub-site creation process before deploying to production
5. **Regular Backups**: Regularly backup your database and files

## Support

If you encounter any issues with the sub-site creation feature, please contact our support team at support@happypress.com.";
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($doc_file))) {
            wp_mkdir_p(dirname($doc_file));
        }
        
        // Write documentation file
        file_put_contents($doc_file, $doc_content);
    }
}
add_action('init', 'hbl_add_subsite_documentation');

/**
 * Helper function to get field value with ACF fallback
 */
function hbl_get_business_field($field_name, $post_id) {
    // Try ACF first if available
    if (function_exists('get_field')) {
        return get_field($field_name, $post_id);
    }

    // Fall back to post meta
    return get_post_meta($post_id, $field_name, true);
}