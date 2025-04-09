<?php
/**
 * WhatsApp Integration for Happy Business Listing
 * 
 * Provides integration with Twilio API and WhatsApp Business API
 * for sending messages and managing leads.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Send WhatsApp message function
 * 
 * @param string $message The message to send
 * @param string $to The recipient's WhatsApp number
 * @param array $attachments Optional array of attachments (URLs)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hbl_send_whatsapp_message($message, $to, $attachments = array()) {
    // Log attempt if logging is enabled
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Attempting to send WhatsApp message',
            'info',
            array(
                'recipient' => $to,
                'has_attachments' => !empty($attachments)
            )
        );
    }
    
    // Validate and sanitize inputs
    if (empty($message)) {
        return new WP_Error('empty_message', __('Message cannot be empty.', 'happy-business-listing'));
    }
    
    // Sanitize phone number (remove non-numeric characters except +)
    $to = hbl_sanitize_input($to, 'phone');
    
    if (is_wp_error($to)) {
        return $to;
    }
    
    // Check if number is valid (at least 10 digits)
    $digits_only = preg_replace('/[^0-9]/', '', $to);
    if (strlen($digits_only) < 10) {
        return new WP_Error('invalid_number', __('Invalid WhatsApp number. Must contain at least 10 digits.', 'happy-business-listing'));
    }
    
    // Validate attachments
    if (!empty($attachments)) {
        if (!is_array($attachments)) {
            return new WP_Error('invalid_attachments', __('Attachments must be an array.', 'happy-business-listing'));
        }
        
        foreach ($attachments as $key => $url) {
            // Validate URL
            $sanitized_url = hbl_sanitize_input($url, 'url');
            
            if (is_wp_error($sanitized_url)) {
                return new WP_Error('invalid_attachment_url', sprintf(__('Invalid attachment URL at index %d.', 'happy-business-listing'), $key));
            }
            
            // Replace with sanitized URL
            $attachments[$key] = $sanitized_url;
        }
    }
    
    // Check rate limiting
    if (hbl_check_rate_limit('whatsapp_message')) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Rate limit exceeded for WhatsApp messages',
                'warning',
                array(
                    'recipient' => $to
                )
            );
        }
        
        return new WP_Error('rate_limited', __('Rate limit exceeded. Please try again later.', 'happy-business-listing'));
    }
    
    // Get integration type
    $integration_type = get_option('hbl_whatsapp_integration', 'twilio');
    
    // Send message based on integration type
    if ($integration_type == 'twilio') {
        return hbl_send_via_twilio($message, $to, $attachments);
    } elseif ($integration_type == 'whatsapp_business') {
        return hbl_send_via_whatsapp_business($message, $to, $attachments);
    }
    
    return new WP_Error('invalid_integration', __('Invalid WhatsApp integration type.', 'happy-business-listing'));
}

/**
 * Send message via Twilio API
 * 
 * @param string $message The message to send
 * @param string $to The recipient's WhatsApp number
 * @param array $attachments Optional array of attachments (URLs)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hbl_send_via_twilio($message, $to, $attachments = array()) {
    // Get API credentials
    $twilio_account_sid = get_option('hbl_twilio_account_sid');
    $twilio_auth_token = get_option('hbl_twilio_auth_token');
    $twilio_from_number = get_option('hbl_twilio_from_number');
    
    // Validate credentials
    if (empty($twilio_account_sid) || empty($twilio_auth_token) || empty($twilio_from_number)) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Missing Twilio credentials',
                'error'
            );
        }
        
        return new WP_Error('missing_credentials', __('Twilio credentials are not configured.', 'happy-business-listing'));
    }
    
    // Prepare the API URL
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$twilio_account_sid}/Messages.json";
    
    // Prepare the request body
    $body = array(
        'From' => 'whatsapp:' . $twilio_from_number,
        'Body' => $message,
        'To' => 'whatsapp:' . $to
    );
    
    // Add media URLs if attachments are provided
    if (!empty($attachments) && is_array($attachments)) {
        $body['MediaUrl'] = $attachments;
    }
    
    // Make the API request
    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($twilio_account_sid . ':' . $twilio_auth_token),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => $body,
        'sslverify' => true, // Ensure SSL verification is enabled
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Twilio API error: ' . $response->get_error_message(),
                'error',
                array(
                    'recipient' => $to
                )
            );
        }
        
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($response_code >= 200 && $response_code < 300) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'WhatsApp message sent successfully via Twilio',
                'info',
                array(
                    'recipient' => $to,
                    'sid' => $response_body['sid']
                )
            );
        }
        
        return true;
    } else {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Twilio API error: ' . ($response_body['message'] ?? 'Unknown error'),
                'error',
                array(
                    'recipient' => $to,
                    'response_code' => $response_code
                )
            );
        }
        
        return new WP_Error('twilio_error', $response_body['message'] ?? __('Unknown Twilio error.', 'happy-business-listing'));
    }
}

/**
 * Send message via WhatsApp Business API
 * 
 * @param string $message The message to send
 * @param string $to The recipient's WhatsApp number
 * @param array $attachments Optional array of attachments (URLs)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hbl_send_via_whatsapp_business($message, $to, $attachments = array()) {
    // Get API credentials
    $whatsapp_business_api_key = get_option('hbl_whatsapp_business_api');
    $whatsapp_business_phone_id = get_option('hbl_whatsapp_business_phone_id');
    
    // Validate credentials
    if (empty($whatsapp_business_api_key) || empty($whatsapp_business_phone_id)) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Missing WhatsApp Business API credentials',
                'error'
            );
        }
        
        return new WP_Error('missing_credentials', __('WhatsApp Business API credentials are not configured.', 'happy-business-listing'));
    }
    
    // Prepare the API URL (Meta/Facebook Graph API for WhatsApp Business)
    $url = "https://graph.facebook.com/v17.0/{$whatsapp_business_phone_id}/messages";
    
    // Prepare the request body
    $body = array(
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => array(
            'preview_url' => false,
            'body' => $message
        )
    );
    
    // If attachments are provided, change the message type
    if (!empty($attachments) && is_array($attachments) && count($attachments) > 0) {
        // For simplicity, we'll just use the first attachment
        $attachment_url = $attachments[0];
        $file_extension = pathinfo($attachment_url, PATHINFO_EXTENSION);
        
        // Validate file extension
        $allowed_image_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $allowed_document_extensions = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt');
        
        // Determine media type based on extension
        if (in_array(strtolower($file_extension), $allowed_image_extensions)) {
            $body['type'] = 'image';
            $body['image'] = array(
                'link' => $attachment_url
            );
            unset($body['text']);
        } elseif (in_array(strtolower($file_extension), $allowed_document_extensions)) {
            $body['type'] = 'document';
            $body['document'] = array(
                'link' => $attachment_url
            );
            unset($body['text']);
        } else {
            if (hbl_security_logging_enabled()) {
                hbl_log_security_event(
                    'Unsupported file type for WhatsApp Business API',
                    'warning',
                    array(
                        'file_extension' => $file_extension
                    )
                );
            }
            
            return new WP_Error('unsupported_file_type', __('Unsupported file type for WhatsApp Business API.', 'happy-business-listing'));
        }
    }
    
    // Make the API request
    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Authorization' => 'Bearer ' . $whatsapp_business_api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($body),
        'sslverify' => true, // Ensure SSL verification is enabled
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'WhatsApp Business API error: ' . $response->get_error_message(),
                'error',
                array(
                    'recipient' => $to
                )
            );
        }
        
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($response_code >= 200 && $response_code < 300) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'WhatsApp message sent successfully via WhatsApp Business API',
                'info',
                array(
                    'recipient' => $to,
                    'message_id' => $response_body['messages'][0]['id'] ?? 'unknown'
                )
            );
        }
        
        return true;
    } else {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'WhatsApp Business API error: ' . ($response_body['error']['message'] ?? 'Unknown error'),
                'error',
                array(
                    'recipient' => $to,
                    'response_code' => $response_code
                )
            );
        }
        
        return new WP_Error('whatsapp_business_error', $response_body['error']['message'] ?? __('Unknown WhatsApp Business API error.', 'happy-business-listing'));
    }
}

/**
 * Add lead to WhatsApp group
 * 
 * @param int $lead_id The lead post ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hbl_add_lead_to_whatsapp_group($lead_id) {
    // Validate lead ID
    $lead_id = absint($lead_id);
    $lead = get_post($lead_id);
    
    if (!$lead || $lead->post_type !== 'lead') {
        return new WP_Error('invalid_lead', __('Invalid lead ID.', 'happy-business-listing'));
    }
    
    // Get lead details
    $lead_details = hbl_get_business_field('lead_details', $lead_id);
    $lead_phone = hbl_get_business_field('lead_phone', $lead_id);
    $lead_name = hbl_get_business_field('lead_name', $lead_id);
    
    if (empty($lead_phone)) {
        return new WP_Error('missing_phone', __('Lead phone number is missing.', 'happy-business-listing'));
    }
    
    // Format the message
    $message = sprintf(__("New Lead: %s\n\n", 'happy-business-listing'), $lead->post_title);
    
    if (!empty($lead_name)) {
        $message .= sprintf(__("Name: %s\n", 'happy-business-listing'), $lead_name);
    }
    
    if (!empty($lead_details)) {
        $message .= sprintf(__("Details: %s\n\n", 'happy-business-listing'), $lead_details);
    }
    
    $message .= sprintf(__("Contact: %s", 'happy-business-listing'), $lead_phone);
    
    // Get business WhatsApp number
    $business_id = hbl_get_business_field('business_id', $lead_id);
    if ($business_id) {
        $business_whatsapp = hbl_get_business_field('whatsapp_number', $business_id);
        if ($business_whatsapp) {
            // Send notification to business owner
            $result = hbl_send_whatsapp_message($message, $business_whatsapp);
            
            if (is_wp_error($result)) {
                if (hbl_security_logging_enabled()) {
                    hbl_log_security_event(
                        'Failed to send lead notification to business owner',
                        'error',
                        array(
                            'lead_id' => $lead_id,
                            'business_id' => $business_id,
                            'error' => $result->get_error_message()
                        )
                    );
                }
                
                return $result;
            }
            
            if (hbl_security_logging_enabled()) {
                hbl_log_security_event(
                    'Lead notification sent to business owner',
                    'info',
                    array(
                        'lead_id' => $lead_id,
                        'business_id' => $business_id
                    )
                );
            }
        }
    }
    
    return true;
}

/**
 * Hook into user registration to get WhatsApp number and send welcome message
 *
 * @param int $user_id The user ID
 */
function hbl_get_whatsapp_number($user_id) {
    // Validate user ID
    $user_id = absint($user_id);
    $user = get_userdata($user_id);
    
    if (!$user) {
        return;
    }
    
    // Only send welcome message to business users
    if (!in_array('business_user', $user->roles)) {
        return;
    }
    
    // Get WhatsApp number
    $whatsapp_number = get_user_meta($user_id, 'whatsapp_number', true);
    
    if (empty($whatsapp_number)) {
        return;
    }
    
    // Get welcome message
    $welcome_message = get_option('hbl_welcome_message', __('Welcome to our business listing service!', 'happy-business-listing'));
    
    // Replace placeholders in welcome message
    $welcome_message = str_replace(
        array('{user_name}', '{business_name}', '{site_name}'),
        array($user->display_name, $user->display_name, get_bloginfo('name')),
        $welcome_message
    );
    
    // Send welcome message
    $result = hbl_send_whatsapp_message($welcome_message, $whatsapp_number);
    
    if (is_wp_error($result) && hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Failed to send welcome message to new user',
            'error',
            array(
                'user_id' => $user_id,
                'error' => $result->get_error_message()
            )
        );
    } elseif (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Welcome message sent to new user',
            'info',
            array(
                'user_id' => $user_id
            )
        );
    }
}
add_action('user_register', 'hbl_get_whatsapp_number');

/**
 * Hook into lead creation to notify business owner
 *
 * @param int $post_id The post ID
 * @param WP_Post $post The post object
 * @param bool $update Whether this is an update or a new post
 */
function hbl_notify_lead_creation($post_id, $post, $update) {
    // Only proceed for new leads
    if ($post->post_type != 'lead' || $update) {
        return;
    }
    
    // Add lead to WhatsApp group
    $result = hbl_add_lead_to_whatsapp_group($post_id);
    
    if (is_wp_error($result) && hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Failed to add lead to WhatsApp group',
            'error',
            array(
                'lead_id' => $post_id,
                'error' => $result->get_error_message()
            )
        );
    }
}
add_action('save_post', 'hbl_notify_lead_creation', 10, 3);

/**
 * Add WhatsApp settings fields to the settings page
 */
function hbl_add_whatsapp_settings() {
    // Add settings section
    add_settings_section(
        'hbl_whatsapp_section',
        __('WhatsApp Integration Settings', 'happy-business-listing'),
        'hbl_whatsapp_section_callback',
        'hbl_options_group'
    );
    
    // Add settings fields
    add_settings_field(
        'hbl_whatsapp_integration',
        __('Integration Type', 'happy-business-listing'),
        'hbl_whatsapp_integration_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_twilio_account_sid',
        __('Twilio Account SID', 'happy-business-listing'),
        'hbl_twilio_account_sid_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_twilio_auth_token',
        __('Twilio Auth Token', 'happy-business-listing'),
        'hbl_twilio_auth_token_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_twilio_from_number',
        __('Twilio WhatsApp From Number', 'happy-business-listing'),
        'hbl_twilio_from_number_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_whatsapp_business_api',
        __('WhatsApp Business API Key', 'happy-business-listing'),
        'hbl_whatsapp_business_api_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_whatsapp_business_phone_id',
        __('WhatsApp Business Phone ID', 'happy-business-listing'),
        'hbl_whatsapp_business_phone_id_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_welcome_message',
        __('Welcome Message', 'happy-business-listing'),
        'hbl_welcome_message_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_enable_whatsapp_rate_limiting',
        __('Enable Rate Limiting', 'happy-business-listing'),
        'hbl_enable_whatsapp_rate_limiting_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    add_settings_field(
        'hbl_whatsapp_rate_limit',
        __('Rate Limit', 'happy-business-listing'),
        'hbl_whatsapp_rate_limit_callback',
        'hbl_options_group',
        'hbl_whatsapp_section'
    );
    
    // Register settings
    register_setting('hbl_options_group', 'hbl_whatsapp_integration', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_twilio_account_sid', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_twilio_auth_token', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_twilio_from_number', 'hbl_sanitize_phone');
    register_setting('hbl_options_group', 'hbl_whatsapp_business_api', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_whatsapp_business_phone_id', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_welcome_message', 'sanitize_textarea_field');
    register_setting('hbl_options_group', 'hbl_enable_whatsapp_rate_limiting', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_whatsapp_rate_limit', 'absint');
}
add_action('admin_init', 'hbl_add_whatsapp_settings');

/**
 * WhatsApp settings section callback
 */
function hbl_whatsapp_section_callback() {
    echo '<p>' . __('Configure WhatsApp integration settings for sending messages to businesses and leads.', 'happy-business-listing') . '</p>';
}

/**
 * WhatsApp integration type callback
 */
function hbl_whatsapp_integration_callback() {
    $value = get_option('hbl_whatsapp_integration', 'twilio');
    ?>
    <select name="hbl_whatsapp_integration" id="hbl_whatsapp_integration">
        <option value="twilio" <?php selected($value, 'twilio'); ?>><?php _e('Twilio', 'happy-business-listing'); ?></option>
        <option value="whatsapp_business" <?php selected($value, 'whatsapp_business'); ?>><?php _e('WhatsApp Business API', 'happy-business-listing'); ?></option>
    </select>
    <p class="description"><?php _e('Select the WhatsApp integration type.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Twilio account SID callback
 */
function hbl_twilio_account_sid_callback() {
    $value = get_option('hbl_twilio_account_sid');
    ?>
    <input type="text" id="hbl_twilio_account_sid" name="hbl_twilio_account_sid" value="<?php echo esc_attr($value); ?>" class="regular-text">
    <p class="description"><?php _e('Enter your Twilio Account SID.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Twilio auth token callback
 */
function hbl_twilio_auth_token_callback() {
    $value = get_option('hbl_twilio_auth_token');
    ?>
    <input type="password" id="hbl_twilio_auth_token" name="hbl_twilio_auth_token" value="<?php echo esc_attr($value); ?>" class="regular-text">
    <p class="description"><?php _e('Enter your Twilio Auth Token.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Twilio from number callback
 */
function hbl_twilio_from_number_callback() {
    $value = get_option('hbl_twilio_from_number');
    ?>
    <input type="text" id="hbl_twilio_from_number" name="hbl_twilio_from_number" value="<?php echo esc_attr($value); ?>" class="regular-text">
    <p class="description"><?php _e('Enter your Twilio WhatsApp number (with country code, e.g., +1234567890).', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * WhatsApp Business API key callback
 */
function hbl_whatsapp_business_api_callback() {
    $value = get_option('hbl_whatsapp_business_api');
    ?>
    <input type="password" id="hbl_whatsapp_business_api" name="hbl_whatsapp_business_api" value="<?php echo esc_attr($value); ?>" class="regular-text">
    <p class="description"><?php _e('Enter your WhatsApp Business API Key (from Facebook Developer Dashboard).', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * WhatsApp Business Phone ID callback
 */
function hbl_whatsapp_business_phone_id_callback() {
    $value = get_option('hbl_whatsapp_business_phone_id');
    ?>
    <input type="text" id="hbl_whatsapp_business_phone_id" name="hbl_whatsapp_business_phone_id" value="<?php echo esc_attr($value); ?>" class="regular-text">
    <p class="description"><?php _e('Enter your WhatsApp Business Phone ID (from Facebook Developer Dashboard).', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Welcome message callback
 */
function hbl_welcome_message_callback() {
    $value = get_option('hbl_welcome_message', __('Welcome to our business listing service!', 'happy-business-listing'));
    ?>
    <textarea id="hbl_welcome_message" name="hbl_welcome_message" rows="4" class="large-text"><?php echo esc_textarea($value); ?></textarea>
    <p class="description">
        <?php _e('Enter the welcome message to send to new users. You can use the following placeholders:', 'happy-business-listing'); ?>
        <br>
        <code>{user_name}</code> - <?php _e('The user\'s display name', 'happy-business-listing'); ?>
        <br>
        <code>{business_name}</code> - <?php _e('The business name', 'happy-business-listing'); ?>
        <br>
        <code>{site_name}</code> - <?php _e('The site name', 'happy-business-listing'); ?>
    </p>
    <?php
}

/**
 * Enable WhatsApp rate limiting callback
 */
function hbl_enable_whatsapp_rate_limiting_callback() {
    $value = get_option('hbl_enable_whatsapp_rate_limiting', '1');
    ?>
    <label>
        <input type="checkbox" name="hbl_enable_whatsapp_rate_limiting" value="1" <?php checked($value, '1'); ?>>
        <?php _e('Enable rate limiting for WhatsApp messages', 'happy-business-listing'); ?>
    </label>
    <p class="description"><?php _e('Limit the number of WhatsApp messages that can be sent within a time period.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * WhatsApp rate limit callback
 */
function hbl_whatsapp_rate_limit_callback() {
    $value = get_option('hbl_whatsapp_rate_limit', 10);
    ?>
    <input type="number" id="hbl_whatsapp_rate_limit" name="hbl_whatsapp_rate_limit" value="<?php echo esc_attr($value); ?>" min="1" max="100" step="1">
    <p class="description"><?php _e('Maximum number of WhatsApp messages that can be sent per minute.', 'happy-business-listing'); ?></p>
    <?php
}

/**
 * Sanitize API key
 *
 * @param string $key The API key to sanitize
 * @return string The sanitized API key
 */
function hbl_sanitize_api_key($key) {
    // Remove whitespace
    $key = trim($key);
    
    // Remove any HTML tags
    $key = wp_strip_all_tags($key);
    
    // Log changes to API keys
    if (hbl_security_logging_enabled()) {
        $option_name = current_filter();
        $option_name = str_replace('sanitize_option_', '', $option_name);
        
        hbl_log_security_event(
            'API key updated',
            'info',
            array(
                'option_name' => $option_name
            )
        );
    }
    
    return $key;
}

/**
 * Test WhatsApp integration
 */
function hbl_test_whatsapp_integration() {
    // Check nonce
    hbl_verify_nonce('hbl_nonce', 'test_whatsapp');
    
    // Check capability
    hbl_check_capability('manage_options');
    
    // Get test number
    $test_number = isset($_POST['test_number']) ? hbl_sanitize_input($_POST['test_number'], 'phone') : '';
    
    if (empty($test_number) || is_wp_error($test_number)) {
        wp_send_json_error(array(
            'message' => __('Please enter a valid phone number.', 'happy-business-listing')
        ));
    }
    
    // Send test message
    $test_message = __('This is a test message from your Happy Business Listing plugin.', 'happy-business-listing');
    $result = hbl_send_whatsapp_message($test_message, $test_number);
    
    if (is_wp_error($result)) {
        wp_send_json_error(array(
            'message' => $result->get_error_message()
        ));
    }
    
    wp_send_json_success(array(
        'message' => __('Test message sent successfully!', 'happy-business-listing')
    ));
}
add_action('wp_ajax_hbl_test_whatsapp', 'hbl_test_whatsapp_integration');

/**
 * Add test WhatsApp integration button to settings page
 */
function hbl_add_test_whatsapp_button() {
    // Only add to WhatsApp settings section
    add_action('hbl_after_whatsapp_section', 'hbl_output_test_whatsapp_button');
}
add_action('admin_init', 'hbl_add_test_whatsapp_button');

/**
 * Output test WhatsApp integration button
 */
function hbl_output_test_whatsapp_button() {
    ?>
    <div class="hbl-test-whatsapp">
        <h3><?php _e('Test WhatsApp Integration', 'happy-business-listing'); ?></h3>
        <p><?php _e('Send a test message to verify your WhatsApp integration is working correctly.', 'happy-business-listing'); ?></p>
        
        <div class="hbl-test-form">
            <input type="text" id="hbl-test-number" placeholder="<?php esc_attr_e('Enter WhatsApp number with country code', 'happy-business-listing'); ?>">
            <button type="button" id="hbl-test-whatsapp-button" class="button button-primary"><?php _e('Send Test Message', 'happy-business-listing'); ?></button>
            <span class="spinner"></span>
        </div>
        
        <div id="hbl-test-result" style="margin-top: 10px;"></div>
        
        <script>
            jQuery(document).ready(function($) {
                $('#hbl-test-whatsapp-button').on('click', function() {
                    var number = $('#hbl-test-number').val();
                    var $button = $(this);
                    var $spinner = $button.next('.spinner');
                    var $result = $('#hbl-test-result');
                    
                    if (!number) {
                        $result.html('<div class="notice notice-error inline"><p><?php echo esc_js(__('Please enter a WhatsApp number.', 'happy-business-listing')); ?></p></div>');
                        return;
                    }
                    
                    $button.prop('disabled', true);
                    $spinner.css('visibility', 'visible');
                    $result.html('');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hbl_test_whatsapp',
                            test_number: number,
                            hbl_nonce: '<?php echo wp_create_nonce('test_whatsapp'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                            } else {
                                $result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
                            }
                        },
                        error: function() {
                            $result.html('<div class="notice notice-error inline"><p><?php echo esc_js(__('An error occurred. Please try again.', 'happy-business-listing')); ?></p></div>');
                        },
                        complete: function() {
                            $button.prop('disabled', false);
                            $spinner.css('visibility', 'hidden');
                        }
                    });
                });
            });
        </script>
        
        <style>
            .hbl-test-form {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .hbl-test-form input {
                margin-right: 10px;
                width: 250px;
            }
            
            .hbl-test-form .spinner {
                float: none;
                margin-left: 10px;
            }
        </style>
    </div>
    <?php
}

/**
 * Add WhatsApp tab to settings page
 *
 * @param array $tabs The existing tabs
 * @return array The modified tabs
 */
function hbl_add_whatsapp_tab($tabs) {
    $tabs['whatsapp'] = __('WhatsApp', 'happy-business-listing');
    return $tabs;
}
add_filter('hbl_settings_tabs', 'hbl_add_whatsapp_tab');

/**
 * Display WhatsApp tab content
 */
function hbl_display_whatsapp_tab() {
    ?>
    <div id="hbl-whatsapp-tab" class="hbl-tab-content">
        <h2><?php _e('WhatsApp Integration Settings', 'happy-business-listing'); ?></h2>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('hbl_options_group');
            do_settings_sections('hbl_options_group');
            do_action('hbl_after_whatsapp_section');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
add_action('hbl_settings_tab_whatsapp', 'hbl_display_whatsapp_tab');