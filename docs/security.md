# Security Documentation

The Happy Business Listing plugin includes comprehensive security features to protect your website from common vulnerabilities and attacks. This document provides detailed information about the security features, their configuration, and best practices.

## Overview

The security features include:

1. **Input Validation and Sanitization**: Comprehensive validation and sanitization of all user inputs
2. **CSRF Protection**: Protection against Cross-Site Request Forgery attacks
3. **Capability Checks**: Proper permission checks for all administrative actions
4. **Rate Limiting**: Protection against brute force and DoS attacks
5. **Security Headers**: HTTP security headers to protect against common web vulnerabilities
6. **Security Logging**: Comprehensive logging of security events
7. **API Key Protection**: Secure storage and handling of API keys

## Security Settings

The plugin includes a dedicated security settings page where you can configure various security features:

### Rate Limiting

Rate limiting helps protect your website from brute force attacks and DoS attacks by limiting the number of requests a user can make within a specific time period.

- **Enable Rate Limiting**: Enable or disable rate limiting for form submissions
- **Rate Limit Threshold**: Maximum number of submissions allowed within the time period
- **Rate Limit Period**: Time period in seconds for rate limiting

### Security Headers

Security headers help protect your website from common web vulnerabilities such as XSS, clickjacking, and MIME type sniffing.

- **Enable Security Headers**: Enable or disable security headers for plugin pages

The following security headers are added when enabled:

- **Content-Security-Policy**: Helps prevent XSS attacks
- **X-Content-Type-Options**: Prevents MIME type sniffing
- **X-Frame-Options**: Prevents clickjacking attacks
- **X-XSS-Protection**: Provides additional XSS protection
- **Referrer-Policy**: Controls how much referrer information is included with requests
- **Permissions-Policy**: Controls which browser features can be used

### Security Logging

Security logging helps you monitor and detect security threats by logging security events to a file.

- **Enable Security Logging**: Enable or disable security logging

The following events are logged when enabled:

- Failed login attempts
- Successful logins
- User registrations
- Password reset requests
- Password reset completions
- Plugin activation and deactivation
- Plugin settings changes

## Input Validation and Sanitization

The plugin includes comprehensive input validation and sanitization functions to protect against common vulnerabilities such as XSS and SQL injection.

### Validation Functions

The following validation functions are available:

- `hbl_sanitize_input($input, $type, $args)`: Sanitizes and validates input based on type
- `hbl_validate_form($data, $rules)`: Validates and sanitizes form data based on rules

### Input Types

The following input types are supported:

- `text`: Text input (sanitized with `sanitize_text_field`)
- `textarea`: Textarea input (sanitized with `sanitize_textarea_field`)
- `email`: Email input (sanitized with `sanitize_email` and validated with `is_email`)
- `url`: URL input (sanitized with `esc_url_raw`)
- `int`: Integer input (sanitized with `intval`)
- `float`: Float input (sanitized with `floatval`)
- `bool`: Boolean input (sanitized with `(bool)`)
- `select`: Select input (sanitized with `sanitize_text_field` and validated against allowed values)
- `html`: HTML input (sanitized with `wp_kses`)
- `phone`: Phone number input (sanitized with regex)
- `slug`: Slug input (sanitized with `sanitize_title`)
- `color`: Color input (sanitized with `sanitize_hex_color`)
- `date`: Date input (sanitized and validated as YYYY-MM-DD)
- `time`: Time input (sanitized and validated as HH:MM)
- `datetime`: Datetime input (sanitized and validated as YYYY-MM-DD HH:MM:SS)
- `array`: Array input (sanitized with `sanitize_text_field` for each element)

### Example Usage

```php
// Sanitize a single input
$email = hbl_sanitize_input($_POST['email'], 'email');

// Validate a form
$rules = array(
    'name' => array('type' => 'text', 'args' => array('required' => true)),
    'email' => array('type' => 'email', 'args' => array('required' => true)),
    'website' => array('type' => 'url'),
    'age' => array('type' => 'int', 'args' => array('min' => 18, 'max' => 100)),
    'message' => array('type' => 'textarea'),
);

$sanitized = hbl_validate_form($_POST, $rules);

if (is_wp_error($sanitized)) {
    // Handle validation errors
    $errors = $sanitized->get_error_data();
    foreach ($errors as $field => $error) {
        echo $error;
    }
} else {
    // Process sanitized data
    $name = $sanitized['name'];
    $email = $sanitized['email'];
    // ...
}
```

## CSRF Protection

The plugin includes CSRF protection for all forms and AJAX requests to prevent Cross-Site Request Forgery attacks.

### Nonce Functions

The following nonce functions are available:

- `hbl_verify_nonce($nonce_name, $action, $die)`: Verifies a nonce
- `hbl_nonce_field($action)`: Outputs a nonce field for a form

### Example Usage

```php
// Add nonce field to a form
<form method="post" action="">
    <?php echo hbl_nonce_field('my_action'); ?>
    <!-- Form fields -->
    <button type="submit">Submit</button>
</form>

// Verify nonce in form submission
if (isset($_POST['submit'])) {
    hbl_verify_nonce('hbl_nonce', 'my_action');
    
    // Process form
}

// Verify nonce in AJAX request
add_action('wp_ajax_my_action', 'my_action_callback');
function my_action_callback() {
    hbl_verify_nonce('hbl_nonce', 'my_action');
    
    // Process AJAX request
    wp_send_json_success();
}
```

## Capability Checks

The plugin includes proper capability checks for all administrative actions to ensure that only authorized users can perform sensitive operations.

### Capability Functions

The following capability functions are available:

- `hbl_check_capability($capability, $die)`: Checks if the current user has a capability

### Example Usage

```php
// Check if user can manage options
hbl_check_capability('manage_options');

// Check if user can edit a post
hbl_check_capability('edit_post', $post_id);

// Check capability without dying
if (hbl_check_capability('publish_posts', false)) {
    // User can publish posts
} else {
    // User cannot publish posts
}
```

## Rate Limiting

The plugin includes rate limiting to protect against brute force attacks and DoS attacks by limiting the number of requests a user can make within a specific time period.

### Rate Limiting Functions

The following rate limiting functions are available:

- `hbl_is_rate_limited($action, $limit, $time_period)`: Checks if a user is rate limited for an action
- `hbl_check_rate_limit($action)`: Checks if a user is rate limited for an action using the configured settings

### Example Usage

```php
// Check if user is rate limited for form submission
if (hbl_check_rate_limit('form_submission')) {
    wp_die(__('Too many submissions. Please try again later.', 'happy-business-listing'));
}

// Process form
// ...
```

## Security Headers

The plugin includes security headers to protect against common web vulnerabilities such as XSS, clickjacking, and MIME type sniffing.

### Security Header Functions

The following security header functions are available:

- `hbl_add_security_headers()`: Adds security headers to the current response
- `hbl_security_headers_enabled()`: Checks if security headers are enabled

### Example Usage

```php
// Add security headers to a custom page
if (hbl_security_headers_enabled()) {
    hbl_add_security_headers();
}
```

## Security Logging

The plugin includes comprehensive security logging to help you monitor and detect security threats.

### Security Logging Functions

The following security logging functions are available:

- `hbl_log_security_event($message, $type, $data)`: Logs a security event
- `hbl_security_logging_enabled()`: Checks if security logging is enabled

### Example Usage

```php
// Log a security event
if (hbl_security_logging_enabled()) {
    hbl_log_security_event(
        'User attempted to access restricted area',
        'warning',
        array(
            'user_id' => get_current_user_id(),
            'url' => $_SERVER['REQUEST_URI']
        )
    );
}
```

## API Key Protection

The plugin includes secure storage and handling of API keys to protect sensitive credentials.

### Best Practices for API Keys

1. **Use WordPress Options API**: Store API keys using the WordPress Options API
2. **Encrypt Sensitive Data**: Consider encrypting sensitive data before storing
3. **Limit Access**: Restrict access to API keys to administrators only
4. **Validate Input**: Validate and sanitize API keys before storing
5. **Use HTTPS**: Always use HTTPS for API requests

### Example Usage

```php
// Store API key
update_option('hbl_api_key', $api_key);

// Retrieve API key
$api_key = get_option('hbl_api_key');

// Make API request
$response = wp_remote_get('https://api.example.com/endpoint', array(
    'headers' => array(
        'Authorization' => 'Bearer ' . $api_key
    )
));
```

## Security Best Practices

Here are some general security best practices for using the Happy Business Listing plugin:

1. **Keep WordPress Updated**: Always keep WordPress, themes, and plugins updated to the latest versions
2. **Use Strong Passwords**: Use strong, unique passwords for all user accounts
3. **Limit Login Attempts**: Use a plugin to limit login attempts and prevent brute force attacks
4. **Use HTTPS**: Always use HTTPS for your website
5. **Regular Backups**: Regularly backup your website and database
6. **Monitor Logs**: Regularly check security logs for suspicious activity
7. **Principle of Least Privilege**: Give users only the permissions they need
8. **Input Validation**: Always validate and sanitize user input
9. **Output Escaping**: Always escape output to prevent XSS attacks
10. **Secure Hosting**: Use a secure hosting provider with good security practices

## Troubleshooting

### Common Issues

#### Rate Limiting

If users are being rate limited incorrectly:

1. Check the rate limit threshold and period in the security settings
2. Temporarily disable rate limiting to see if that resolves the issue
3. Check the security log for rate limiting events

#### Security Headers

If security headers are causing issues with your website:

1. Check if any third-party scripts are being blocked by the Content-Security-Policy
2. Temporarily disable security headers to see if that resolves the issue
3. Adjust the Content-Security-Policy to allow necessary scripts

#### Security Logging

If security logging is not working:

1. Check if the log file exists and is writable
2. Check if security logging is enabled in the settings
3. Check the WordPress error log for any related errors

## Reporting Security Issues

If you discover a security issue in the Happy Business Listing plugin, please report it to us immediately. Please do not disclose security issues publicly until they have been resolved.

To report a security issue, please email security@happypress.com with a description of the issue, steps to reproduce, and any other relevant information.