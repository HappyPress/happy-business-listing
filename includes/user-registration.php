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
    
    // Log role creation
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business user role created',
            'info'
        );
    }
}
add_action('init', 'hbl_create_business_user_role');

/**
 * Hook into the post save action to create a user and sub-site
 *
 * @param int $post_id The post ID
 * @param WP_Post $post The post object
 * @param bool $update Whether this is an update or a new post
 * @return int|WP_Error User ID on success, WP_Error on failure
 */
function hbl_create_user_and_site($post_id, $post, $update) {
    // Only proceed for new business listings
    if ($post->post_type != 'business_listing' || $update) {
        return;
    }
    
    // Get business details
    $business_name = hbl_get_business_field('business_name', $post_id);
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
    
    // Generate random password with high entropy
    $password = wp_generate_password(16, true, true);
    
    // Get email from form or generate a placeholder
    $email = hbl_get_business_field('email', $post_id);
    if (empty($email)) {
        // Check for contact email
        $email = hbl_get_business_field('business_email', $post_id);
    }
    
    // Validate email
    if (!empty($email) && !is_email($email)) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Invalid email provided for business user creation',
                'warning',
                array(
                    'business_id' => $post_id,
                    'email' => $email
                )
            );
        }
        
        return new WP_Error('invalid_email', __('Invalid email address provided.', 'happy-business-listing'));
    }
    
    // If still empty, generate a placeholder email
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
        // Log error
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error creating user for business listing: ' . $user_id->get_error_message(),
                'error',
                array(
                    'business_id' => $post_id,
                    'username' => $username
                )
            );
        }
        
        return $user_id;
    }
    
    // Store user ID in business listing
    hbl_update_field('user_id', $user_id, $post_id);
    
    // Store business ID in user meta
    update_user_meta($user_id, 'user_business_id', $post_id);
    
    // Store WhatsApp number in user meta
    $whatsapp_number = hbl_get_business_field('whatsapp_number', $post_id);
    if (!empty($whatsapp_number)) {
        update_user_meta($user_id, 'whatsapp_number', $whatsapp_number);
    }
    
    // Check if multisite is enabled and sub-site creation is enabled
    if (is_multisite() && get_option('hbl_enable_subsite_creation') == '1') {
        // Create the sub-site using the function from site-creation.php
        $site_result = hbl_create_business_subsite($post_id, $user_id, $business_name, $username);
        
        if (is_wp_error($site_result)) {
            // Log error
            if (hbl_security_logging_enabled()) {
                hbl_log_security_event(
                    'Error creating sub-site for business: ' . $site_result->get_error_message(),
                    'error',
                    array(
                        'business_id' => $post_id,
                        'user_id' => $user_id
                    )
                );
            }
        }
    }
    
    // Send notification email
    $email_result = hbl_send_registration_email($user_id, $password, $post_id);
    
    if (is_wp_error($email_result)) {
        // Log error
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error sending registration email: ' . $email_result->get_error_message(),
                'error',
                array(
                    'business_id' => $post_id,
                    'user_id' => $user_id
                )
            );
        }
    }
    
    // Log successful user creation
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business user created successfully',
            'info',
            array(
                'business_id' => $post_id,
                'user_id' => $user_id,
                'username' => $username
            )
        );
    }
    
    return $user_id;
}
add_action('save_post', 'hbl_create_user_and_site', 10, 3);

/**
 * Send registration email to the new business user
 *
 * @param int $user_id The user ID
 * @param string $password The user password
 * @param int $post_id The business post ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hbl_send_registration_email($user_id, $password, $post_id) {
    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('invalid_user', __('Invalid user ID.', 'happy-business-listing'));
    }
    
    $business_name = hbl_get_business_field('business_name', $post_id);
    if (empty($business_name)) {
        $business_name = get_the_title($post_id);
    }
    
    $site_url = get_site_url();
    $login_url = wp_login_url();
    
    // Check if multisite is enabled
    if (is_multisite()) {
        $site_id = hbl_get_business_field('site_id', $post_id);
        if ($site_id) {
            $site_url = get_site_url($site_id);
            $login_url = get_admin_url($site_id);
        }
    }
    
    // Generate password reset link
    $reset_key = get_password_reset_key($user);
    if (is_wp_error($reset_key)) {
        return $reset_key;
    }
    
    $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login');
    
    $subject = sprintf(__('Welcome to %s - Your Business Account', 'happy-business-listing'), get_bloginfo('name'));
    
    $message = sprintf(__('Hello %s,', 'happy-business-listing'), $business_name) . "\n\n";
    $message .= sprintf(__('Your business listing on %s has been created successfully.', 'happy-business-listing'), get_bloginfo('name')) . "\n\n";
    $message .= __('Here are your account details:', 'happy-business-listing') . "\n";
    $message .= sprintf(__('Username: %s', 'happy-business-listing'), $user->user_login) . "\n";
    $message .= sprintf(__('Password: %s', 'happy-business-listing'), $password) . "\n";
    $message .= sprintf(__('Login URL: %s', 'happy-business-listing'), $login_url) . "\n\n";
    $message .= sprintf(__('For security reasons, we recommend changing your password immediately after logging in. You can also use this password reset link: %s', 'happy-business-listing'), $reset_url) . "\n\n";
    
    if (is_multisite() && !empty($site_id)) {
        $message .= sprintf(__('Your business website has been created at: %s', 'happy-business-listing'), $site_url) . "\n\n";
    }
    
    $message .= __('Please keep this information safe for future reference.', 'happy-business-listing') . "\n\n";
    $message .= __('Thank you for registering with us!', 'happy-business-listing') . "\n\n";
    $message .= get_bloginfo('name');
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
    );
    
    $email_sent = wp_mail($user->user_email, $subject, $message, $headers);
    
    if (!$email_sent) {
        return new WP_Error('email_failed', __('Failed to send registration email.', 'happy-business-listing'));
    }
    
    return true;
}

/**
 * Add business user capabilities for managing their own content
 */
function hbl_add_business_user_capabilities() {
    $role = get_role('business_user');
    
    if (!$role) {
        add_role('business_user', __('Business User', 'happy-business-listing'), array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => true
        ));
    }
    
    $role = get_role('business_user');
    $role->add_cap('edit_business_listing');
    $role->add_cap('edit_business_listings');
    $role->add_cap('edit_others_business_listings');
    $role->add_cap('publish_business_listings');
    $role->add_cap('read_business_listing');
    $role->add_cap('read_private_business_listings');
    $role->add_cap('delete_business_listing');
    $role->add_cap('delete_business_listings');
    $role->add_cap('delete_others_business_listings');
    $role->add_cap('delete_published_business_listings');
    $role->add_cap('delete_private_business_listings');
}
add_action('admin_init', 'hbl_add_business_user_capabilities');

/**
 * Filter content to only show business user's own content
 *
 * @param WP_Query $query The query object
 */
function hbl_filter_business_user_content($query) {
    global $pagenow, $typenow;
    
    // Only apply on admin pages for our custom post types
    if (!is_admin() || $pagenow !== 'edit.php' || !in_array($typenow, array('service_product', 'lead', 'business_listing'))) {
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
    
    // For business listings, only show the user's own business
    if ($typenow === 'business_listing') {
        $query->set('p', $business_id);
        return;
    }
    
    // For other post types, filter by business ID
    $query->set('meta_key', 'business_id');
    $query->set('meta_value', $business_id);
}
add_action('pre_get_posts', 'hbl_filter_business_user_content');

/**
 * Set default business ID when a business user creates content
 *
 * @param int $post_id The post ID
 * @param WP_Post $post The post object
 * @param bool $update Whether this is an update or a new post
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
    $current_business_id = hbl_get_business_field('business_id', $post_id);
    if (empty($current_business_id)) {
        hbl_update_field('business_id', $business_id, $post_id);
    }
}
add_action('save_post', 'hbl_set_default_business_id', 20, 3);

/**
 * Restrict business users from editing other users' content
 *
 * @param array $allcaps All capabilities of the user
 * @param array $caps Capabilities being checked
 * @param array $args Arguments passed to current_user_can()
 * @return array Modified capabilities
 */
function hbl_restrict_business_user_capabilities($allcaps, $caps, $args) {
    // Only apply for business users
    $user = wp_get_current_user();
    if (!in_array('business_user', $user->roles)) {
        return $allcaps;
    }
    
    // Get the business ID associated with the user
    $business_id = get_user_meta($user->ID, 'user_business_id', true);
    if (!$business_id) {
        return $allcaps;
    }
    
    // Check if we're trying to edit a post
    if (isset($args[0]) && ($args[0] === 'edit_post' || $args[0] === 'delete_post') && isset($args[2])) {
        $post_id = $args[2];
        $post = get_post($post_id);
        
        // If not our custom post types, deny access
        if (!in_array($post->post_type, array('business_listing', 'service_product', 'lead'))) {
            return $allcaps;
        }
        
        // For business listings, only allow editing their own
        if ($post->post_type === 'business_listing' && $post_id != $business_id) {
            $allcaps[$caps[0]] = false;
            return $allcaps;
        }
        
        // For other post types, check the business ID
        $post_business_id = hbl_get_business_field('business_id', $post_id);
        if ($post_business_id != $business_id) {
            $allcaps[$caps[0]] = false;
            
            // Log unauthorized access attempt
            if (hbl_security_logging_enabled()) {
                hbl_log_security_event(
                    'Unauthorized access attempt to post',
                    'warning',
                    array(
                        'user_id' => $user->ID,
                        'post_id' => $post_id,
                        'post_type' => $post->post_type,
                        'capability' => $args[0]
                    )
                );
            }
        }
    }
    
    return $allcaps;
}
add_filter('user_has_cap', 'hbl_restrict_business_user_capabilities', 10, 3);

/**
 * Add security check to prevent business users from accessing admin pages they shouldn't
 */
function hbl_restrict_business_user_admin_access() {
    // Only apply in admin
    if (!is_admin()) {
        return;
    }
    
    // Only apply for business users
    $user = wp_get_current_user();
    if (!in_array('business_user', $user->roles)) {
        return;
    }
    
    // Get current screen
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }
    
    // Allow access to these screens
    $allowed_screens = array(
        'dashboard',
        'profile',
        'edit-service_product',
        'service_product',
        'edit-lead',
        'lead',
        'edit-business_listing',
        'business_listing',
        'upload',
        'media'
    );
    
    // Check if current screen is allowed
    if (!in_array($screen->id, $allowed_screens) && !in_array($screen->parent_base, $allowed_screens)) {
        // Log unauthorized access attempt
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Unauthorized admin page access attempt',
                'warning',
                array(
                    'user_id' => $user->ID,
                    'screen_id' => $screen->id
                )
            );
        }
        
        // Redirect to dashboard
        wp_redirect(admin_url('index.php'));
        exit;
    }
}
add_action('current_screen', 'hbl_restrict_business_user_admin_access');

/**
 * Add password strength meter to registration form
 */
function hbl_add_password_strength_meter() {
    wp_enqueue_script('password-strength-meter');
    
    // Add custom script
    wp_add_inline_script('password-strength-meter', '
        jQuery(document).ready(function($) {
            $(".wp-pwd").each(function() {
                var $this = $(this);
                var $pass = $this.find("input[name=\'pass1\']");
                var $strengthResult = $("<div class=\'password-strength-meter-result\'></div>");
                
                $this.append($strengthResult);
                
                $pass.on("keyup", function() {
                    var strength = wp.passwordStrength.meter(
                        $pass.val(),
                        [],
                        $pass.val()
                    );
                    
                    var strengthText = "";
                    var strengthClass = "";
                    
                    switch (strength) {
                        case 0:
                        case 1:
                            strengthText = "Very Weak";
                            strengthClass = "very-weak";
                            break;
                        case 2:
                            strengthText = "Weak";
                            strengthClass = "weak";
                            break;
                        case 3:
                            strengthText = "Medium";
                            strengthClass = "medium";
                            break;
                        case 4:
                            strengthText = "Strong";
                            strengthClass = "strong";
                            break;
                    }
                    
                    $strengthResult.attr("class", "password-strength-meter-result " + strengthClass);
                    $strengthResult.text(strengthText);
                });
            });
        });
    ');
    
    // Add custom styles
    wp_add_inline_style('wp-admin', '
        .password-strength-meter-result {
            margin-top: 8px;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
        }
        .password-strength-meter-result.very-weak {
            background-color: #f1adad;
            color: #a00;
        }
        .password-strength-meter-result.weak {
            background-color: #fbc5a9;
            color: #c80;
        }
        .password-strength-meter-result.medium {
            background-color: #ffe399;
            color: #c60;
        }
        .password-strength-meter-result.strong {
            background-color: #c1e1b9;
            color: #060;
        }
    ');
}
add_action('admin_enqueue_scripts', 'hbl_add_password_strength_meter');
add_action('login_enqueue_scripts', 'hbl_add_password_strength_meter');

/**
 * Enforce strong passwords for business users
 *
 * @param WP_Error $errors WP_Error object
 * @param bool $update Whether this is an update or a new user
 * @param WP_User|stdClass $user User object
 * @return WP_Error Modified WP_Error object
 */
function hbl_enforce_strong_passwords($errors, $update, $user) {
    // Only apply for business users
    if (!empty($user->role) && $user->role === 'business_user') {
        $password = isset($_POST['pass1']) ? $_POST['pass1'] : '';
        
        // Check password strength
        if (!empty($password)) {
            $strength = 0;
            
            // Length check
            if (strlen($password) >= 12) {
                $strength++;
            }
            
            // Uppercase check
            if (preg_match('/[A-Z]/', $password)) {
                $strength++;
            }
            
            // Lowercase check
            if (preg_match('/[a-z]/', $password)) {
                $strength++;
            }
            
            // Number check
            if (preg_match('/[0-9]/', $password)) {
                $strength++;
            }
            
            // Special character check
            if (preg_match('/[^a-zA-Z0-9]/', $password)) {
                $strength++;
            }
            
            // Require at least 4 out of 5 criteria
            if ($strength < 4) {
                $errors->add('password_too_weak', __('Error: Password is too weak. Please use at least 12 characters with a mix of uppercase letters, lowercase letters, numbers, and special characters.', 'happy-business-listing'));
            }
        }
    }
    
    return $errors;
}
add_filter('user_profile_update_errors', 'hbl_enforce_strong_passwords', 10, 3);

/**
 * Log user login and logout events
 *
 * @param string $user_login The username
 * @param WP_User $user The user object
 */
function hbl_log_user_login($user_login, $user) {
    // Only log for business users
    if (in_array('business_user', $user->roles) && hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business user logged in',
            'info',
            array(
                'user_id' => $user->ID,
                'username' => $user_login
            )
        );
    }
}
add_action('wp_login', 'hbl_log_user_login', 10, 2);

/**
 * Log user logout events
 */
function hbl_log_user_logout() {
    $user = wp_get_current_user();
    
    // Only log for business users
    if ($user && in_array('business_user', $user->roles) && hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business user logged out',
            'info',
            array(
                'user_id' => $user->ID,
                'username' => $user->user_login
            )
        );
    }
}
add_action('wp_logout', 'hbl_log_user_logout');

/**
 * Log user password reset events
 *
 * @param WP_User $user The user object
 */
function hbl_log_password_reset($user) {
    // Only log for business users
    if (in_array('business_user', $user->roles) && hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business user password reset',
            'info',
            array(
                'user_id' => $user->ID,
                'username' => $user->user_login
            )
        );
    }
}
add_action('password_reset', 'hbl_log_password_reset');

/**
 * Log user profile update events
 *
 * @param int $user_id The user ID
 * @param WP_User $old_user_data The old user data
 */
function hbl_log_profile_update($user_id, $old_user_data) {
    $user = get_userdata($user_id);
    
    // Only log for business users
    if ($user && in_array('business_user', $user->roles) && hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business user profile updated',
            'info',
            array(
                'user_id' => $user_id,
                'username' => $user->user_login
            )
        );
    }
}
add_action('profile_update', 'hbl_log_profile_update', 10, 2);

/**
 * Add custom user meta for security tracking
 *
 * @param int $user_id The user ID
 */
function hbl_add_security_user_meta($user_id) {
    // Only apply for business users
    $user = get_userdata($user_id);
    if (!$user || !in_array('business_user', $user->roles)) {
        return;
    }
    
    // Add last login time
    update_user_meta($user_id, 'hbl_last_login', current_time('mysql'));
    
    // Add login count
    $login_count = get_user_meta($user_id, 'hbl_login_count', true);
    $login_count = $login_count ? intval($login_count) + 1 : 1;
    update_user_meta($user_id, 'hbl_login_count', $login_count);
    
    // Add last IP
    if (isset($_SERVER['REMOTE_ADDR'])) {
        update_user_meta($user_id, 'hbl_last_ip', $_SERVER['REMOTE_ADDR']);
    }
}
add_action('wp_login', 'hbl_add_security_user_meta');

/**
 * Add security information to user profile
 *
 * @param WP_User $user The user object
 */
function hbl_add_security_profile_fields($user) {
    // Only show for business users
    if (!in_array('business_user', $user->roles)) {
        return;
    }
    
    // Only show for administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $last_login = get_user_meta($user->ID, 'hbl_last_login', true);
    $login_count = get_user_meta($user->ID, 'hbl_login_count', true);
    $last_ip = get_user_meta($user->ID, 'hbl_last_ip', true);
    $business_id = get_user_meta($user->ID, 'user_business_id', true);
    
    ?>
    <h3><?php _e('Business User Security Information', 'happy-business-listing'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label><?php _e('Business ID', 'happy-business-listing'); ?></label></th>
            <td>
                <?php if ($business_id) : ?>
                    <a href="<?php echo get_edit_post_link($business_id); ?>"><?php echo $business_id; ?></a>
                <?php else : ?>
                    <?php _e('Not associated with a business', 'happy-business-listing'); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label><?php _e('Last Login', 'happy-business-listing'); ?></label></th>
            <td><?php echo $last_login ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_login)) : __('Never', 'happy-business-listing'); ?></td>
        </tr>
        <tr>
            <th><label><?php _e('Login Count', 'happy-business-listing'); ?></label></th>
            <td><?php echo $login_count ? $login_count : '0'; ?></td>
        </tr>
        <tr>
            <th><label><?php _e('Last IP Address', 'happy-business-listing'); ?></label></th>
            <td><?php echo $last_ip ? $last_ip : __('Unknown', 'happy-business-listing'); ?></td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'hbl_add_security_profile_fields');
add_action('edit_user_profile', 'hbl_add_security_profile_fields');

/**
 * Create user from business listing
 *
 * @param int $business_id The business ID
 * @return int|WP_Error The user ID or WP_Error on failure
 */
function hbl_create_user_from_business($business_id) {
    $business_email = hbl_get_business_field($business_id, 'business_email');
    $business_name = hbl_get_business_field($business_id, 'business_name');
    
    if (empty($business_email) || empty($business_name)) {
        return new WP_Error('missing_data', __('Business email and name are required.', 'happy-business-listing'));
    }
    
    // Check if user already exists
    $user = get_user_by('email', $business_email);
    if ($user) {
        return $user->ID;
    }
    
    // Generate username from business name
    $username = sanitize_user(strtolower(str_replace(' ', '', $business_name)));
    $username = preg_replace('/[^a-z0-9]/', '', $username);
    
    // Ensure username is unique
    $count = 1;
    $original_username = $username;
    while (username_exists($username)) {
        $username = $original_username . $count;
        $count++;
    }
    
    // Generate random password
    $password = wp_generate_password();
    
    // Create user
    $user_id = wp_create_user($username, $password, $business_email);
    
    if (is_wp_error($user_id)) {
        return $user_id;
    }
    
    // Set user role
    $user = new WP_User($user_id);
    $user->set_role('business_user');
    
    // Update user meta
    update_user_meta($user_id, 'business_id', $business_id);
    
    // Send welcome email
    wp_new_user_notification($user_id, null, 'user');
    
    return $user_id;
}

/**
 * Filter content for business users
 *
 * @param string $content The content to filter
 * @return string The filtered content
 */
function hbl_filter_content_for_business_users($content) {
    if (!is_user_logged_in()) {
        return $content;
    }
    
    $user = wp_get_current_user();
    if (!in_array('business_user', (array) $user->roles)) {
        return $content;
    }
    
    $business_id = get_user_meta($user->ID, 'business_id', true);
    if (!$business_id) {
        return $content;
    }
    
    // Add business ID to content
    $content .= sprintf(
        '<input type="hidden" name="business_id" value="%d">',
        esc_attr($business_id)
    );
    
    return $content;
}