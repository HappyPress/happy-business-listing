<?php
// Register form shortcode
function hbl_register_form_shortcode() {
    ob_start();
    ?>
    <form id="hbl-business-registration-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <input type="hidden" name="action" value="hbl_register_business">
        <label for="business_name">Business Name:</label>
        <input type="text" name="business_name" id="business_name" required><br>

        <label for="company_type">Type of Company:</label>
        <select name="company_type" id="company_type" required>
            <option value="Pvt Ltd">Pvt Ltd</option>
            <option value="LLP">LLP</option>
            <option value="OPC">OPC</option>
        </select><br>

        <label for="gst_no">GST No.:</label>
        <input type="text" name="gst_no" id="gst_no" required><br>

        <label for="tan_pan">TAN/PAN:</label>
        <input type="text" name="tan_pan" id="tan_pan" required><br>

        <label for="location">Location/s:</label>
        <input type="text" name="location" id="location" required><br>

        <label for="website">Website:</label>
        <input type="url" name="website" id="website" required><br>

        <label for="social_media">Social Media:</label>
        <input type="text" name="social_media" id="social_media" required><br>

        <label for="whatsapp_number">WhatsApp Number:</label>
        <input type="text" name="whatsapp_number" id="whatsapp_number" required><br>

        <input type="submit" value="Register Business">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('business_signup_form', 'hbl_register_form_shortcode');

// Handle form submission
function hbl_handle_business_registration() {
    if (isset($_POST['business_name'])) {
        $business_name = sanitize_text_field($_POST['business_name']);
        $company_type = sanitize_text_field($_POST['company_type']);
        $gst_no = sanitize_text_field($_POST['gst_no']);
        $tan_pan = sanitize_text_field($_POST['tan_pan']);
        $location = sanitize_text_field($_POST['location']);
        $website = esc_url($_POST['website']);
        $social_media = sanitize_text_field($_POST['social_media']);
        $whatsapp_number = sanitize_text_field($_POST['whatsapp_number']);

        // Create a new business listing
        $post_id = wp_insert_post(array(
            'post_title' => $business_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'business_listing',
        ));

        if (!is_wp_error($post_id)) {
            // Save custom fields
            update_field('business_name', $business_name, $post_id);
            update_field('company_type', $company_type, $post_id);
            update_field('gst_no', $gst_no, $post_id);
            update_field('tan_pan', $tan_pan, $post_id);
            update_field('location', $location, $post_id);
            update_field('website', $website, $post_id);
            update_field('social_media', $social_media, $post_id);
            update_field('whatsapp_number', $whatsapp_number, $post_id);

            // Trigger user and sub-site creation
            hbl_create_user_and_site($post_id, get_post($post_id), false);

            // Redirect after successful registration
            wp_redirect(home_url('/thank-you'));
            exit;
        }
    }
}
add_action('admin_post_nopriv_hbl_register_business', 'hbl_handle_business_registration');
add_action('admin_post_hbl_register_business', 'hbl_handle_business_registration');
