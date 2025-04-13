/**
 * Handle business registration form submission
 *
 * @return void
 */
function hbl_handle_business_registration() {
    if (!isset($_POST['hbl_business_registration_nonce']) || !wp_verify_nonce($_POST['hbl_business_registration_nonce'], 'hbl_business_registration')) {
        return;
    }
    
    $data = array(
        'business_name' => isset($_POST['business_name']) ? sanitize_text_field($_POST['business_name']) : '',
        'business_email' => isset($_POST['business_email']) ? sanitize_email($_POST['business_email']) : '',
        'phone' => isset($_POST['phone']) ? hbl_sanitize_input_phone($_POST['phone']) : '',
        'website' => isset($_POST['website']) ? esc_url_raw($_POST['website']) : '',
        'address' => isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '',
        'company_type' => isset($_POST['company_type']) ? sanitize_text_field($_POST['company_type']) : '',
        'gst_no' => isset($_POST['gst_no']) ? sanitize_text_field($_POST['gst_no']) : '',
        'tan_pan' => isset($_POST['tan_pan']) ? sanitize_text_field($_POST['tan_pan']) : '',
        'established_date' => isset($_POST['established_date']) ? sanitize_text_field($_POST['established_date']) : ''
    );
    
    $validated = hbl_validate_form($data);
    
    if (is_wp_error($validated)) {
        hbl_set_form_errors($validated);
        return;
    }
    
    $post_data = array(
        'post_title' => $validated['business_name'],
        'post_type' => 'business_listing',
        'post_status' => 'pending'
    );
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        hbl_set_form_errors($post_id);
        return;
    }
    
    foreach ($validated as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
    
    hbl_set_form_success(__('Business registration submitted successfully.', 'happy-business-listing'));
}

/**
 * Handle contact form submission
 *
 * @return void
 */
function hbl_handle_contact_form() {
    if (!isset($_POST['hbl_contact_form_nonce']) || !wp_verify_nonce($_POST['hbl_contact_form_nonce'], 'hbl_contact_form')) {
        return;
    }
    
    $data = array(
        'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
        'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
        'phone' => isset($_POST['phone']) ? hbl_sanitize_input_phone($_POST['phone']) : '',
        'message' => isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '',
        'business_id' => isset($_POST['business_id']) ? absint($_POST['business_id']) : 0
    );
    
    if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
        hbl_set_form_errors(new WP_Error('required_fields', __('Please fill in all required fields.', 'happy-business-listing')));
        return;
    }
    
    if (!is_email($data['email'])) {
        hbl_set_form_errors(new WP_Error('invalid_email', __('Please enter a valid email address.', 'happy-business-listing')));
        return;
    }
    
    $post_data = array(
        'post_title' => sprintf(__('Contact Form: %s', 'happy-business-listing'), $data['name']),
        'post_type' => 'contact_form',
        'post_status' => 'private'
    );
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        hbl_set_form_errors($post_id);
        return;
    }
    
    foreach ($data as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
    
    hbl_set_form_success(__('Your message has been sent successfully.', 'happy-business-listing'));
} 