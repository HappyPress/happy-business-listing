<?php
/**
 * Security Functions for Happy Business Listing
 * 
 * Provides security-related functions and utilities to protect the plugin
 * from common vulnerabilities and attacks.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify nonce for an action
 *
 * @param string $nonce_name The nonce name in the request
 * @param string $action The action to verify
 * @param bool $die Whether to die on failure
 * @return bool True if nonce is valid, false otherwise
 */
function hbl_verify_nonce($nonce_name, $action, $die = true) {
    // Check if nonce exists
    if (!isset($_REQUEST[$nonce_name])) {
        if ($die) {
            wp_die(__('Security check failed: Nonce is missing.', 'happy-business-listing'), __('Security Error', 'happy-business-listing'), array('response' => 403));
        }
        return false;
    }
    
    // Verify nonce
    $valid = wp_verify_nonce($_REQUEST[$nonce_name], $action);
    
    if (!$valid && $die) {
        wp_die(__('Security check failed: Invalid nonce.', 'happy-business-listing'), __('Security Error', 'happy-business-listing'), array('response' => 403));
    }
    
    return (bool) $valid;
}

/**
 * Check user capability
 *
 * @param string $capability The capability to check
 * @param bool $die Whether to die on failure
 * @return bool True if user has capability, false otherwise
 */
function hbl_check_capability($capability, $die = true) {
    $has_cap = current_user_can($capability);
    
    if (!$has_cap && $die) {
        wp_die(__('You do not have permission to perform this action.', 'happy-business-listing'), __('Permission Error', 'happy-business-listing'), array('response' => 403));
    }
    
    return $has_cap;
}

/**
 * Sanitize and validate input
 *
 * @param mixed $input The input to sanitize
 * @param string $type The type of input (text, email, url, int, float, etc.)
 * @param array $args Additional arguments
 * @return mixed Sanitized input
 */
function hbl_sanitize_input($input, $type = 'text', $args = array()) {
    $defaults = array(
        'min' => null,
        'max' => null,
        'required' => false,
        'default' => '',
        'allowed_values' => array(),
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Handle empty input
    if (empty($input) && $input !== '0' && $input !== 0) {
        if ($args['required']) {
            return new WP_Error('required_field', __('This field is required.', 'happy-business-listing'));
        }
        return $args['default'];
    }
    
    // Sanitize based on type
    $sanitized = $input;
    
    switch ($type) {
        case 'text':
            $sanitized = sanitize_text_field($input);
            break;
            
        case 'textarea':
            $sanitized = sanitize_textarea_field($input);
            break;
            
        case 'email':
            $sanitized = sanitize_email($input);
            if (!is_email($sanitized)) {
                return new WP_Error('invalid_email', __('Invalid email address.', 'happy-business-listing'));
            }
            break;
            
        case 'url':
            $sanitized = esc_url_raw($input);
            if (empty($sanitized) && !empty($input)) {
                return new WP_Error('invalid_url', __('Invalid URL.', 'happy-business-listing'));
            }
            break;
            
        case 'int':
        case 'integer':
            $sanitized = intval($input);
            
            // Check min/max
            if ($args['min'] !== null && $sanitized < $args['min']) {
                return new WP_Error('min_value', sprintf(__('Value must be at least %d.', 'happy-business-listing'), $args['min']));
            }
            
            if ($args['max'] !== null && $sanitized > $args['max']) {
                return new WP_Error('max_value', sprintf(__('Value must be at most %d.', 'happy-business-listing'), $args['max']));
            }
            break;
            
        case 'float':
        case 'number':
            $sanitized = floatval($input);
            
            // Check min/max
            if ($args['min'] !== null && $sanitized < $args['min']) {
                return new WP_Error('min_value', sprintf(__('Value must be at least %f.', 'happy-business-listing'), $args['min']));
            }
            
            if ($args['max'] !== null && $sanitized > $args['max']) {
                return new WP_Error('max_value', sprintf(__('Value must be at most %f.', 'happy-business-listing'), $args['max']));
            }
            break;
            
        case 'bool':
        case 'boolean':
            $sanitized = (bool) $input;
            break;
            
        case 'select':
            $sanitized = sanitize_text_field($input);
            
            // Check allowed values
            if (!empty($args['allowed_values']) && !in_array($sanitized, $args['allowed_values'])) {
                return new WP_Error('invalid_option', __('Invalid option selected.', 'happy-business-listing'));
            }
            break;
            
        case 'html':
            $allowed_html = wp_kses_allowed_html('post');
            $sanitized = wp_kses($input, $allowed_html);
            break;
            
        case 'phone':
            $sanitized = preg_replace('/[^0-9+\-() ]/', '', $input);
            break;
            
        case 'slug':
            $sanitized = sanitize_title($input);
            break;
            
        case 'color':
            $sanitized = sanitize_hex_color($input);
            if (empty($sanitized) && !empty($input)) {
                return new WP_Error('invalid_color', __('Invalid color code.', 'happy-business-listing'));
            }
            break;
            
        case 'date':
            $sanitized = sanitize_text_field($input);
            $date = date_create_from_format('Y-m-d', $sanitized);
            if (!$date) {
                return new WP_Error('invalid_date', __('Invalid date format. Use YYYY-MM-DD.', 'happy-business-listing'));
            }
            $sanitized = date_format($date, 'Y-m-d');
            break;
            
        case 'time':
            $sanitized = sanitize_text_field($input);
            $time = date_create_from_format('H:i', $sanitized);
            if (!$time) {
                return new WP_Error('invalid_time', __('Invalid time format. Use HH:MM.', 'happy-business-listing'));
            }
            $sanitized = date_format($time, 'H:i');
            break;
            
        case 'datetime':
            $sanitized = sanitize_text_field($input);
            $datetime = date_create_from_format('Y-m-d H:i:s', $sanitized);
            if (!$datetime) {
                return new WP_Error('invalid_datetime', __('Invalid datetime format. Use YYYY-MM-DD HH:MM:SS.', 'happy-business-listing'));
            }
            $sanitized = date_format($datetime, 'Y-m-d H:i:s');
            break;
            
        case 'array':
            if (!is_array($input)) {
                return new WP_Error('invalid_array', __('Invalid array.', 'happy-business-listing'));
            }
            $sanitized = array_map('sanitize_text_field', $input);
            break;
            
        default:
            $sanitized = sanitize_text_field($input);
            break;
    }
    
    return $sanitized;
}

/**
 * Sanitize URL input
 *
 * @param string $url The URL to sanitize
 * @return string|WP_Error Sanitized URL or WP_Error on failure
 */
function hbl_sanitize_input_url($url) {
    if (empty($url)) {
        return new WP_Error('empty_url', __('URL cannot be empty.', 'happy-business-listing'));
    }
    
    $url = esc_url_raw($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return new WP_Error('invalid_url', __('Invalid URL format.', 'happy-business-listing'));
    }
    
    return $url;
}

/**
 * Sanitize boolean input
 *
 * @param mixed $value The value to sanitize
 * @return bool Sanitized boolean value
 */
function hbl_sanitize_input_bool($value) {
    if (is_bool($value)) {
        return $value;
    }
    
    if (is_string($value)) {
        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }
    
    return (bool) $value;
}

/**
 * Sanitize phone number input
 *
 * @param string $phone The phone number to sanitize
 * @return string Sanitized phone number
 */
function hbl_sanitize_input_phone($phone) {
    return hbl_sanitize_phone($phone);
}

/**
 * Validate form data
 *
 * @param array $data The form data to validate
 * @return array|WP_Error Validated data or WP_Error on failure
 */
function hbl_validate_form($data) {
    $errors = new WP_Error();
    $validated = array();
    
    // Required fields
    $required_fields = array(
        'business_name' => __('Business Name', 'happy-business-listing'),
        'business_email' => __('Business Email', 'happy-business-listing'),
        'phone' => __('Phone Number', 'happy-business-listing'),
        'website' => __('Website', 'happy-business-listing'),
        'address' => __('Address', 'happy-business-listing')
    );
    
    foreach ($required_fields as $field => $label) {
        if (empty($data[$field])) {
            $errors->add('empty_field', sprintf(__('%s is required.', 'happy-business-listing'), $label));
        }
    }
    
    // Validate email
    if (!empty($data['business_email']) && !is_email($data['business_email'])) {
        $errors->add('invalid_email', __('Invalid email address.', 'happy-business-listing'));
    }
    
    // Validate website
    if (!empty($data['website'])) {
        $website = hbl_sanitize_input_url($data['website']);
        if (is_wp_error($website)) {
            $errors->add('invalid_website', $website->get_error_message());
        } else {
            $validated['website'] = $website;
        }
    }
    
    // Validate phone
    if (!empty($data['phone'])) {
        $validated['phone'] = hbl_sanitize_input_phone($data['phone']);
    }
    
    // If there are errors, return them
    if ($errors->has_errors()) {
        return $errors;
    }
    
    // Return validated data
    return array_merge($data, $validated);
}

/**
 * Check if request is from a bot
 *
 * @return bool True if request is from a bot, false otherwise
 */
function hbl_is_bot() {
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $bot_patterns = array(
        'bot',
        'spider',
        'crawl',
        'slurp',
        'mediapartners',
    );
    
    foreach ($bot_patterns as $pattern) {
        if (stripos($user_agent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if request is from a known bad IP
 *
 * @return bool True if request is from a bad IP, false otherwise
 */
function hbl_is_bad_ip() {
    if (!isset($_SERVER['REMOTE_ADDR'])) {
        return false;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Get bad IPs from options
    $bad_ips = get_option('hbl_bad_ips', array());
    
    if (in_array($ip, $bad_ips)) {
        return true;
    }
    
    return false;
}

/**
 * Add IP to bad IPs list
 *
 * @param string $ip The IP to add
 * @return bool True on success, false on failure
 */
function hbl_add_bad_ip($ip) {
    $bad_ips = get_option('hbl_bad_ips', array());
    
    if (!in_array($ip, $bad_ips)) {
        $bad_ips[] = $ip;
        return update_option('hbl_bad_ips', $bad_ips);
    }
    
    return true;
}

/**
 * Check if user is rate limited
 *
 * @param string $action The action to check
 * @param int $limit The maximum number of requests
 * @param int $time_period The time period in seconds
 * @return bool True if rate limited, false otherwise
 */
function hbl_is_rate_limited($action, $limit = 10, $time_period = 60) {
    // Get user IP
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    
    if (empty($ip)) {
        return false;
    }
    
    // Get rate limit data
    $rate_limits = get_option('hbl_rate_limits', array());
    
    // Generate key
    $key = md5($ip . $action);
    
    // Check if key exists
    if (!isset($rate_limits[$key])) {
        $rate_limits[$key] = array(
            'count' => 1,
            'time' => time(),
        );
        update_option('hbl_rate_limits', $rate_limits);
        return false;
    }
    
    // Check if time period has passed
    $elapsed = time() - $rate_limits[$key]['time'];
    
    if ($elapsed > $time_period) {
        // Reset count
        $rate_limits[$key] = array(
            'count' => 1,
            'time' => time(),
        );
        update_option('hbl_rate_limits', $rate_limits);
        return false;
    }
    
    // Increment count
    $rate_limits[$key]['count']++;
    update_option('hbl_rate_limits', $rate_limits);
    
    // Check if limit exceeded
    if ($rate_limits[$key]['count'] > $limit) {
        return true;
    }
    
    return false;
}

/**
 * Clean expired rate limits
 */
function hbl_clean_rate_limits() {
    $rate_limits = get_option('hbl_rate_limits', array());
    $now = time();
    $max_age = 24 * 60 * 60; // 24 hours
    
    foreach ($rate_limits as $key => $data) {
        if ($now - $data['time'] > $max_age) {
            unset($rate_limits[$key]);
        }
    }
    
    update_option('hbl_rate_limits', $rate_limits);
}
add_action('wp_scheduled_delete', 'hbl_clean_rate_limits');

/**
 * Add security headers
 */
function hbl_add_security_headers() {
    // Only add headers on plugin pages
    $screen = get_current_screen();
    
    if (!$screen || strpos($screen->id, 'hbl') === false) {
        return;
    }
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
    
    // X-Content-Type-Options
    header("X-Content-Type-Options: nosniff");
    
    // X-Frame-Options
    header("X-Frame-Options: SAMEORIGIN");
    
    // X-XSS-Protection
    header("X-XSS-Protection: 1; mode=block");
    
    // Referrer-Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Permissions-Policy
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}
add_action('admin_init', 'hbl_add_security_headers');

/**
 * Log security event
 *
 * @param string $message The message to log
 * @param string $type The type of event (error, warning, info)
 * @param array $data Additional data to log
 */
function hbl_log_security_event($message, $type = 'error', $data = array()) {
    // Only log if logging is enabled
    if (get_option('hbl_enable_logging') != '1') {
        return;
    }
    
    $log_file = WP_CONTENT_DIR . '/hbl-security.log';
    
    // Get user info
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $username = $user ? $user->user_login : 'Guest';
    
    // Get request info
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown';
    
    // Format message
    $timestamp = date('Y-m-d H:i:s');
    $formatted = sprintf(
        "[%s] [%s] [User: %s (%d)] [IP: %s] %s\n",
        $timestamp,
        strtoupper($type),
        $username,
        $user_id,
        $ip,
        $message
    );
    
    // Add data if provided
    if (!empty($data)) {
        $formatted .= "Data: " . json_encode($data) . "\n";
    }
    
    // Add request info
    $formatted .= sprintf(
        "Request: %s\nUser-Agent: %s\n\n",
        $request_uri,
        $user_agent
    );
    
    // Write to log file
    error_log($formatted, 3, $log_file);
    
    // Also log to WordPress error log
    error_log(sprintf('[HBL Security] [%s] %s', strtoupper($type), $message));
}

/**
 * Initialize security audit log
 */
function hbl_init_security_audit_log() {
    // Create log file if it doesn't exist
    $log_file = WP_CONTENT_DIR . '/hbl-security.log';
    
    if (!file_exists($log_file)) {
        $header = "# Happy Business Listing Security Audit Log\n";
        $header .= "# Created: " . date('Y-m-d H:i:s') . "\n\n";
        
        @file_put_contents($log_file, $header);
    }
}
add_action('admin_init', 'hbl_init_security_audit_log');

/**
 * Register security settings
 */
function hbl_register_security_settings() {
    // Register settings
    register_setting('hbl_options_group', 'hbl_enable_rate_limiting', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_rate_limit_threshold', 'absint');
    register_setting('hbl_options_group', 'hbl_rate_limit_period', 'absint');
    register_setting('hbl_options_group', 'hbl_enable_security_headers', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_enable_security_logging', 'sanitize_text_field');
    
    // Add settings section
    add_settings_section(
        'hbl_security_section',
        __('Security Settings', 'happy-business-listing'),
        'hbl_security_section_callback',
        'hbl_options_group'
    );
    
    // Add settings fields
    add_settings_field(
        'hbl_enable_rate_limiting',
        __('Enable Rate Limiting', 'happy-business-listing'),
        'hbl_enable_rate_limiting_callback',
        'hbl_options_group',
        'hbl_security_section'
    );
    
    add_settings_field(
        'hbl_rate_limit_threshold',
        __('Rate Limit Threshold', 'happy-business-listing'),
        'hbl_rate_limit_threshold_callback',
        'hbl_options_group',
        'hbl_security_section'
    );
    
    add_settings_field(
        'hbl_rate_limit_period',
        __('Rate Limit Period', 'happy-business-listing'),
        'hbl_rate_limit_period_callback',
        'hbl_options_group',
        'hbl_security_section'
    );
    
    add_settings_field(
        'hbl_enable_security_headers',
        __('Enable Security Headers', 'happy-business-listing'),
        'hbl_enable_security_headers_callback',
        'hbl_options_group',
        'hbl_security_section'
    );
    
    add_settings_field(
        'hbl_enable_security_logging',
        __('Enable Security Logging', 'happy-business-listing'),
        'hbl_enable_security_logging_callback',
        'hbl_options_group',
        'hbl_security_section'
    );
}
add_action('admin_init', 'hbl_register_security_settings');

/**
 * Security settings section callback
 */
function hbl_security_section_callback() {
    echo '<p>' . __('Configure security settings for the plugin.', 'happy-business-listing') . '</p>';
}

/**
 * Enable rate limiting field callback
 */
function hbl_enable_rate_limiting_callback() {
    $value = get_option('hbl_enable_rate_limiting', '0');
    
    echo '<label><input type="checkbox" name="hbl_enable_rate_limiting" value="1" ' . checked('1', $value, false) . '> ' . __('Enable rate limiting for form submissions', 'happy-business-listing') . '</label>';
    echo '<p class="description">' . __('Limits the number of form submissions from a single IP address.', 'happy-business-listing') . '</p>';
}

/**
 * Rate limit threshold field callback
 */
function hbl_rate_limit_threshold_callback() {
    $value = get_option('hbl_rate_limit_threshold', 10);
    
    echo '<input type="number" name="hbl_rate_limit_threshold" value="' . esc_attr($value) . '" min="1" max="100" step="1">';
    echo '<p class="description">' . __('Maximum number of submissions allowed within the time period.', 'happy-business-listing') . '</p>';
}

/**
 * Rate limit period field callback
 */
function hbl_rate_limit_period_callback() {
    $value = get_option('hbl_rate_limit_period', 60);
    
    echo '<input type="number" name="hbl_rate_limit_period" value="' . esc_attr($value) . '" min="10" max="3600" step="10">';
    echo '<p class="description">' . __('Time period in seconds for rate limiting.', 'happy-business-listing') . '</p>';
}

/**
 * Enable security headers field callback
 */
function hbl_enable_security_headers_callback() {
    $value = get_option('hbl_enable_security_headers', '0');
    
    echo '<label><input type="checkbox" name="hbl_enable_security_headers" value="1" ' . checked('1', $value, false) . '> ' . __('Enable security headers', 'happy-business-listing') . '</label>';
    echo '<p class="description">' . __('Adds security headers to plugin pages.', 'happy-business-listing') . '</p>';
}

/**
 * Enable security logging field callback
 */
function hbl_enable_security_logging_callback() {
    $value = get_option('hbl_enable_security_logging', '0');
    
    echo '<label><input type="checkbox" name="hbl_enable_security_logging" value="1" ' . checked('1', $value, false) . '> ' . __('Enable security logging', 'happy-business-listing') . '</label>';
    echo '<p class="description">' . __('Logs security events to a file.', 'happy-business-listing') . '</p>';
}

/**
 * Add security tab to plugin settings page
 *
 * @param array $tabs The existing tabs
 * @return array The modified tabs
 */
function hbl_add_security_tab($tabs) {
    $tabs['security'] = __('Security', 'happy-business-listing');
    return $tabs;
}
add_filter('hbl_settings_tabs', 'hbl_add_security_tab');

/**
 * Display security tab content
 */
function hbl_display_security_tab() {
    ?>
    <div id="hbl-security-tab" class="hbl-tab-content">
        <h2><?php _e('Security Settings', 'happy-business-listing'); ?></h2>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('hbl_options_group');
            do_settings_sections('hbl_options_group');
            submit_button();
            ?>
        </form>
        
        <h3><?php _e('Security Audit Log', 'happy-business-listing'); ?></h3>
        
        <?php
        $log_file = WP_CONTENT_DIR . '/hbl-security.log';
        
        if (file_exists($log_file)) {
            $log_size = size_format(filesize($log_file));
            $log_modified = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), filemtime($log_file));
            
            echo '<p>' . sprintf(__('Log file size: %s', 'happy-business-listing'), $log_size) . '</p>';
            echo '<p>' . sprintf(__('Last modified: %s', 'happy-business-listing'), $log_modified) . '</p>';
            
            echo '<p><a href="' . admin_url('admin.php?page=hbl_settings&tab=security&action=view_log') . '" class="button">' . __('View Log', 'happy-business-listing') . '</a> ';
            echo '<a href="' . admin_url('admin.php?page=hbl_settings&tab=security&action=clear_log') . '" class="button" onclick="return confirm(\'' . __('Are you sure you want to clear the log?', 'happy-business-listing') . '\')">' . __('Clear Log', 'happy-business-listing') . '</a></p>';
        } else {
            echo '<p>' . __('No security log file found.', 'happy-business-listing') . '</p>';
        }
        ?>
    </div>
    <?php
}
add_action('hbl_settings_tab_security', 'hbl_display_security_tab');

/**
 * Handle security log actions
 */
function hbl_handle_security_log_actions() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'hbl_settings' || !isset($_GET['tab']) || $_GET['tab'] !== 'security') {
        return;
    }
    
    if (!isset($_GET['action'])) {
        return;
    }
    
    $action = $_GET['action'];
    $log_file = WP_CONTENT_DIR . '/hbl-security.log';
    
    switch ($action) {
        case 'view_log':
            if (!file_exists($log_file)) {
                wp_die(__('Log file not found.', 'happy-business-listing'));
            }
            
            $log_content = file_get_contents($log_file);
            
            echo '<div class="wrap">';
            echo '<h1>' . __('Security Audit Log', 'happy-business-listing') . '</h1>';
            echo '<p><a href="' . admin_url('admin.php?page=hbl_settings&tab=security') . '" class="button">' . __('Back to Security Settings', 'happy-business-listing') . '</a></p>';
            echo '<div style="background: #fff; padding: 10px; border: 1px solid #ccc; max-height: 500px; overflow: auto;">';
            echo '<pre>' . esc_html($log_content) . '</pre>';
            echo '</div>';
            echo '</div>';
            exit;
            
        case 'clear_log':
            if (file_exists($log_file)) {
                $header = "# Happy Business Listing Security Audit Log\n";
                $header .= "# Cleared: " . date('Y-m-d H:i:s') . "\n\n";
                
                file_put_contents($log_file, $header);
                
                // Redirect back to security tab
                wp_redirect(admin_url('admin.php?page=hbl_settings&tab=security&cleared=1'));
                exit;
            }
            break;
    }
}
add_action('admin_init', 'hbl_handle_security_log_actions');

/**
 * Display security log cleared notice
 */
function hbl_display_security_log_cleared_notice() {
    if (isset($_GET['page']) && $_GET['page'] === 'hbl_settings' && isset($_GET['tab']) && $_GET['tab'] === 'security' && isset($_GET['cleared'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Security log cleared successfully.', 'happy-business-listing'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'hbl_display_security_log_cleared_notice');

/**
 * Add CSRF protection to forms
 *
 * @param string $action The nonce action
 * @return string The nonce field HTML
 */
function hbl_nonce_field($action) {
    return wp_nonce_field($action, 'hbl_nonce', true, false);
}

/**
 * Check if security headers are enabled
 *
 * @return bool True if security headers are enabled, false otherwise
 */
function hbl_security_headers_enabled() {
    return get_option('hbl_enable_security_headers', '0') === '1';
}

/**
 * Check if rate limiting is enabled
 *
 * @return bool True if rate limiting is enabled, false otherwise
 */
function hbl_rate_limiting_enabled() {
    return get_option('hbl_enable_rate_limiting', '0') === '1';
}

/**
 * Check if security logging is enabled
 *
 * @return bool True if security logging is enabled, false otherwise
 */
function hbl_security_logging_enabled() {
    return get_option('hbl_enable_security_logging', '0') === '1';
}

/**
 * Get rate limit threshold
 *
 * @return int The rate limit threshold
 */
function hbl_get_rate_limit_threshold() {
    return intval(get_option('hbl_rate_limit_threshold', 10));
}

/**
 * Get rate limit period
 *
 * @return int The rate limit period in seconds
 */
function hbl_get_rate_limit_period() {
    return intval(get_option('hbl_rate_limit_period', 60));
}

/**
 * Check if request is rate limited
 *
 * @param string $action The action to check
 * @return bool True if rate limited, false otherwise
 */
function hbl_check_rate_limit($action) {
    // Only check if rate limiting is enabled
    if (!hbl_rate_limiting_enabled()) {
        return false;
    }
    
    $threshold = hbl_get_rate_limit_threshold();
    $period = hbl_get_rate_limit_period();
    
    return hbl_is_rate_limited($action, $threshold, $period);
}

/**
 * Add security headers to admin pages
 */
function hbl_maybe_add_security_headers() {
    // Only add headers if enabled
    if (!hbl_security_headers_enabled()) {
        return;
    }
    
    hbl_add_security_headers();
}
add_action('admin_init', 'hbl_maybe_add_security_headers');

/**
 * Log failed login attempts
 *
 * @param string $username The username that was used to attempt to log in
 */
function hbl_log_failed_login($username) {
    // Only log if security logging is enabled
    if (!hbl_security_logging_enabled()) {
        return;
    }
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Failed login attempt for username: %s', 'happy-business-listing'), $username),
        'warning',
        array(
            'username' => $username,
            'ip' => $ip
        )
    );
}
add_action('wp_login_failed', 'hbl_log_failed_login');

/**
 * Log successful logins
 *
 * @param string $user_login The username that was used to log in
 * @param WP_User $user The user object
 */
function hbl_log_successful_login($user_login, $user) {
    // Only log if security logging is enabled
    if (!hbl_security_logging_enabled()) {
        return;
    }
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Successful login for user: %s', 'happy-business-listing'), $user_login),
        'info',
        array(
            'user_id' => $user->ID,
            'ip' => $ip
        )
    );
}
add_action('wp_login', 'hbl_log_successful_login', 10, 2);

/**
 * Log user registration
 *
 * @param int $user_id The user ID
 */
function hbl_log_user_registration($user_id) {
    // Only log if security logging is enabled
    if (!hbl_security_logging_enabled()) {
        return;
    }
    
    $user = get_userdata($user_id);
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('New user registered: %s', 'happy-business-listing'), $user->user_login),
        'info',
        array(
            'user_id' => $user_id,
            'email' => $user->user_email,
            'ip' => $ip
        )
    );
}
add_action('user_register', 'hbl_log_user_registration');

/**
 * Log password reset requests
 *
 * @param WP_User $user The user object
 */
function hbl_log_password_reset_request($user) {
    // Only log if security logging is enabled
    if (!hbl_security_logging_enabled()) {
        return;
    }
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Password reset requested for user: %s', 'happy-business-listing'), $user->user_login),
        'info',
        array(
            'user_id' => $user->ID,
            'ip' => $ip
        )
    );
}
add_action('retrieve_password', 'hbl_log_password_reset_request');

/**
 * Log password reset completions
 *
 * @param WP_User $user The user object
 */
function hbl_log_password_reset_complete($user) {
    // Only log if security logging is enabled
    if (!hbl_security_logging_enabled()) {
        return;
    }
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Password reset completed for user: %s', 'happy-business-listing'), $user->user_login),
        'info',
        array(
            'user_id' => $user->ID,
            'ip' => $ip
        )
    );
}
add_action('password_reset', 'hbl_log_password_reset_complete');

/**
 * Log plugin activation
 *
 * @param string $plugin The plugin being activated
 */
function hbl_log_plugin_activation($plugin) {
    // Only log if security logging is enabled and it's our plugin
    if (!hbl_security_logging_enabled() || $plugin !== 'happy-business-listing/happy-business-listing.php') {
        return;
    }
    
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $username = $user ? $user->user_login : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Plugin activated by user: %s', 'happy-business-listing'), $username),
        'info',
        array(
            'user_id' => $user_id
        )
    );
}
add_action('activated_plugin', 'hbl_log_plugin_activation');

/**
 * Log plugin deactivation
 *
 * @param string $plugin The plugin being deactivated
 */
function hbl_log_plugin_deactivation($plugin) {
    // Only log if security logging is enabled and it's our plugin
    if (!hbl_security_logging_enabled() || $plugin !== 'happy-business-listing/happy-business-listing.php') {
        return;
    }
    
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $username = $user ? $user->user_login : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Plugin deactivated by user: %s', 'happy-business-listing'), $username),
        'info',
        array(
            'user_id' => $user_id
        )
    );
}
add_action('deactivated_plugin', 'hbl_log_plugin_deactivation');

/**
 * Log plugin settings changes
 *
 * @param string $option The option name
 * @param mixed $old_value The old option value
 * @param mixed $value The new option value
 */
function hbl_log_settings_changes($option, $old_value, $value) {
    // Only log if security logging is enabled and it's our plugin option
    if (!hbl_security_logging_enabled() || strpos($option, 'hbl_') !== 0) {
        return;
    }
    
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $username = $user ? $user->user_login : 'Unknown';
    
    hbl_log_security_event(
        sprintf(__('Plugin setting changed: %s', 'happy-business-listing'), $option),
        'info',
        array(
            'user_id' => $user_id,
            'option' => $option,
            'old_value' => $old_value,
            'new_value' => $value
        )
    );
}
add_action('updated_option', 'hbl_log_settings_changes', 10, 3);