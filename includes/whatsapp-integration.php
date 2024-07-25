<?php
// Send WhatsApp message function
function hbl_send_whatsapp_message($message, $to) {
    $integration_type = get_option('hbl_whatsapp_integration');
    if ($integration_type == 'twilio') {
        $twilio_api_key = get_option('hbl_twilio_api');
        // Add Twilio API integration code here
    } elseif ($integration_type == 'whatsapp_business') {
        $whatsapp_business_api_key = get_option('hbl_whatsapp_business_api');
        // Add WhatsApp Business API integration code here
    }
}

// Hook into user registration to get WhatsApp number
function hbl_get_whatsapp_number($user_id) {
    $user = get_userdata($user_id);
    $whatsapp_number = get_user_meta($user_id, 'whatsapp_number', true);

    if ($whatsapp_number) {
        hbl_send_whatsapp_message("Welcome to our service!", $whatsapp_number);
    }
}
add_action('user_register', 'hbl_get_whatsapp_number');
?>
