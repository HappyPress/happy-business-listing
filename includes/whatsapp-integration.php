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
    if (get_option('hbl_enable_logging') == 1) {
        error_log("[HBL] Attempting to send WhatsApp message to: $to");
    }
    
    // Sanitize phone number (remove non-numeric characters)
    $to = preg_replace('/[^0-9]/', '', $to);
    
    // Check if number is valid
    if (strlen($to) < 10) {
        return new WP_Error('invalid_number', 'Invalid WhatsApp number');
    }
    
    $integration_type = get_option('hbl_whatsapp_integration', 'twilio');
    
    if ($integration_type == 'twilio') {
        return hbl_send_via_twilio($message, $to, $attachments);
    } elseif ($integration_type == 'whatsapp_business') {
        return hbl_send_via_whatsapp_business($message, $to, $attachments);
    }
    
    return new WP_Error('invalid_integration', 'Invalid WhatsApp integration type');
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
    $twilio_account_sid = get_option('hbl_twilio_account_sid');
    $twilio_auth_token = get_option('hbl_twilio_auth_token');
    $twilio_from_number = get_option('hbl_twilio_from_number');
    
    if (empty($twilio_account_sid) || empty($twilio_auth_token) || empty($twilio_from_number)) {
        return new WP_Error('missing_credentials', 'Twilio credentials are not configured');
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
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] Twilio API error: " . $response->get_error_message());
        }
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($response_code >= 200 && $response_code < 300) {
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] WhatsApp message sent successfully via Twilio. SID: " . $response_body['sid']);
        }
        return true;
    } else {
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] Twilio API error: " . json_encode($response_body));
        }
        return new WP_Error('twilio_error', $response_body['message'] ?? 'Unknown Twilio error');
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
    $whatsapp_business_api_key = get_option('hbl_whatsapp_business_api');
    $whatsapp_business_phone_id = get_option('hbl_whatsapp_business_phone_id');
    
    if (empty($whatsapp_business_api_key) || empty($whatsapp_business_phone_id)) {
        return new WP_Error('missing_credentials', 'WhatsApp Business API credentials are not configured');
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
        
        // Determine media type based on extension
        if (in_array($file_extension, array('jpg', 'jpeg', 'png'))) {
            $body['type'] = 'image';
            $body['image'] = array(
                'link' => $attachment_url
            );
            unset($body['text']);
        } elseif (in_array($file_extension, array('pdf', 'doc', 'docx', 'xls', 'xlsx'))) {
            $body['type'] = 'document';
            $body['document'] = array(
                'link' => $attachment_url
            );
            unset($body['text']);
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
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] WhatsApp Business API error: " . $response->get_error_message());
        }
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($response_code >= 200 && $response_code < 300) {
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] WhatsApp message sent successfully via WhatsApp Business API. ID: " . $response_body['messages'][0]['id']);
        }
        return true;
    } else {
        if (get_option('hbl_enable_logging') == 1) {
            error_log("[HBL] WhatsApp Business API error: " . json_encode($response_body));
        }
        return new WP_Error('whatsapp_business_error', $response_body['error']['message'] ?? 'Unknown WhatsApp Business API error');
    }
}

/**
 * Add lead to WhatsApp group
 * 
 * @param int $lead_id The lead post ID
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hbl_add_lead_to_whatsapp_group($lead_id) {
    $lead = get_post($lead_id);
    if (!$lead || $lead->post_type !== 'lead') {
        return new WP_Error('invalid_lead', 'Invalid lead ID');
    }
    
    $lead_details = get_field('lead_details', $lead_id);
    $lead_phone = get_field('lead_phone', $lead_id);
    
    if (empty($lead_phone)) {
        return new WP_Error('missing_phone', 'Lead phone number is missing');
    }
    
    // Format the message
    $message = "New Lead: " . $lead->post_title . "\n\n";
    $message .= "Details: " . $lead_details . "\n\n";
    $message .= "Contact: " . $lead_phone;
    
    // Get business WhatsApp number
    $business_id = get_field('business_id', $lead_id);
    if ($business_id) {
        $business_whatsapp = get_field('whatsapp_number', $business_id);
        if ($business_whatsapp) {
            // Send notification to business owner
            hbl_send_whatsapp_message($message, $business_whatsapp);
        }
    }
    
    return true;
}

/**
 * Hook into user registration to get WhatsApp number and send welcome message
 */
function hbl_get_whatsapp_number($user_id) {
    $user = get_userdata($user_id);
    $whatsapp_number = get_user_meta($user_id, 'whatsapp_number', true);

    if ($whatsapp_number) {
        $welcome_message = get_option('hbl_welcome_message', 'Welcome to our business listing service!');
        hbl_send_whatsapp_message($welcome_message, $whatsapp_number);
    }
}
add_action('user_register', 'hbl_get_whatsapp_number');

/**
 * Hook into lead creation to notify business owner
 */
function hbl_notify_lead_creation($post_id, $post, $update) {
    if ($post->post_type != 'lead' || $update) {
        return;
    }
    
    hbl_add_lead_to_whatsapp_group($post_id);
}
add_action('save_post', 'hbl_notify_lead_creation', 10, 3);

/**
 * Add WhatsApp settings fields to the settings page
 */
function hbl_add_whatsapp_settings() {
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
    
    register_setting('hbl_options_group', 'hbl_twilio_account_sid', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_twilio_auth_token', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_twilio_from_number', 'hbl_sanitize_phone');
    register_setting('hbl_options_group', 'hbl_whatsapp_business_phone_id', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_welcome_message', 'sanitize_textarea_field');
}
add_action('admin_init', 'hbl_add_whatsapp_settings');

// Callback functions for settings fields
function hbl_twilio_account_sid_callback() {
    $value = get_option('hbl_twilio_account_sid');
    echo '<input type="text" id="hbl_twilio_account_sid" name="hbl_twilio_account_sid" value="' . esc_attr($value) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter your Twilio Account SID', 'happy-business-listing') . '</p>';
}

function hbl_twilio_auth_token_callback() {
    $value = get_option('hbl_twilio_auth_token');
    echo '<input type="password" id="hbl_twilio_auth_token" name="hbl_twilio_auth_token" value="' . esc_attr($value) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter your Twilio Auth Token', 'happy-business-listing') . '</p>';
}

function hbl_twilio_from_number_callback() {
    $value = get_option('hbl_twilio_from_number');
    echo '<input type="text" id="hbl_twilio_from_number" name="hbl_twilio_from_number" value="' . esc_attr($value) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter your Twilio WhatsApp number (with country code, e.g., +1234567890)', 'happy-business-listing') . '</p>';
}

function hbl_whatsapp_business_phone_id_callback() {
    $value = get_option('hbl_whatsapp_business_phone_id');
    echo '<input type="text" id="hbl_whatsapp_business_phone_id" name="hbl_whatsapp_business_phone_id" value="' . esc_attr($value) . '" class="regular-text">';
    echo '<p class="description">' . __('Enter your WhatsApp Business Phone ID (from Facebook Developer Dashboard)', 'happy-business-listing') . '</p>';
}

function hbl_welcome_message_callback() {
    $value = get_option('hbl_welcome_message', 'Welcome to our business listing service!');
    echo '<textarea id="hbl_welcome_message" name="hbl_welcome_message" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">' . __('Enter the welcome message to send to new users', 'happy-business-listing') . '</p>';
}

function hbl_sanitize_phone($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
}
?>
