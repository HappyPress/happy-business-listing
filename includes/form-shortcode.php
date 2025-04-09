<?php
/**
 * Form Shortcode for Happy Business Listing
 * 
 * Provides shortcodes for business registration and other forms
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register form shortcode
 *
 * @return string The form HTML
 */
function hbl_register_form_shortcode() {
    ob_start();
    ?>
    <form id="hbl-business-registration-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <input type="hidden" name="action" value="hbl_register_business">
        <?php echo hbl_nonce_field('hbl_register_business'); ?>
        
        <div class="form-group">
            <label for="business_name"><?php _e('Business Name:', 'happy-business-listing'); ?></label>
            <input type="text" name="business_name" id="business_name" required>
        </div>

        <div class="form-group">
            <label for="company_type"><?php _e('Type of Company:', 'happy-business-listing'); ?></label>
            <select name="company_type" id="company_type" required>
                <option value=""><?php _e('Select Company Type', 'happy-business-listing'); ?></option>
                <option value="Pvt Ltd"><?php _e('Pvt Ltd', 'happy-business-listing'); ?></option>
                <option value="LLP"><?php _e('LLP', 'happy-business-listing'); ?></option>
                <option value="OPC"><?php _e('OPC', 'happy-business-listing'); ?></option>
                <option value="Partnership"><?php _e('Partnership', 'happy-business-listing'); ?></option>
                <option value="Proprietorship"><?php _e('Proprietorship', 'happy-business-listing'); ?></option>
                <option value="Other"><?php _e('Other', 'happy-business-listing'); ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="gst_no"><?php _e('GST No.:', 'happy-business-listing'); ?></label>
            <input type="text" name="gst_no" id="gst_no" required>
        </div>

        <div class="form-group">
            <label for="tan_pan"><?php _e('TAN/PAN:', 'happy-business-listing'); ?></label>
            <input type="text" name="tan_pan" id="tan_pan" required>
        </div>

        <div class="form-group">
            <label for="location"><?php _e('Location/s:', 'happy-business-listing'); ?></label>
            <input type="text" name="location" id="location" required>
        </div>

        <div class="form-group">
            <label for="website"><?php _e('Website:', 'happy-business-listing'); ?></label>
            <input type="url" name="website" id="website" required>
        </div>

        <div class="form-group">
            <label for="social_media"><?php _e('Social Media:', 'happy-business-listing'); ?></label>
            <input type="text" name="social_media" id="social_media" placeholder="<?php _e('Facebook, Twitter, Instagram, etc.', 'happy-business-listing'); ?>" required>
        </div>

        <div class="form-group">
            <label for="whatsapp_number"><?php _e('WhatsApp Number:', 'happy-business-listing'); ?></label>
            <input type="text" name="whatsapp_number" id="whatsapp_number" required>
        </div>

        <div class="form-group">
            <label for="email"><?php _e('Email Address:', 'happy-business-listing'); ?></label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="contact_name"><?php _e('Contact Person:', 'happy-business-listing'); ?></label>
            <input type="text" name="contact_name" id="contact_name" required>
        </div>

        <div class="form-group">
            <label for="phone"><?php _e('Phone Number:', 'happy-business-listing'); ?></label>
            <input type="tel" name="phone" id="phone" required>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="terms_agreement" value="1" required>
                <?php _e('I agree to the terms and conditions', 'happy-business-listing'); ?>
            </label>
        </div>

        <?php 
        // Add honeypot field for spam protection
        ?>
        <div class="form-group" style="display:none;">
            <label for="website_url"><?php _e('Website URL:', 'happy-business-listing'); ?></label>
            <input type="text" name="website_url" id="website_url" autocomplete="off">
        </div>

        <div class="form-actions">
            <input type="submit" value="<?php _e('Register Business', 'happy-business-listing'); ?>" class="submit-button">
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('business_signup_form', 'hbl_register_form_shortcode');

/**
 * Handle form submission
 */
function hbl_handle_business_registration() {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url());
        exit;
    }
    
    // Verify nonce
    hbl_verify_nonce('hbl_nonce', 'hbl_register_business');
    
    // Check for honeypot field (spam protection)
    if (!empty($_POST['website_url'])) {
        // This is likely a spam submission
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Spam submission detected (honeypot field filled)',
                'warning',
                array(
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                )
            );
        }
        
        wp_redirect(home_url('/thank-you'));
        exit;
    }
    
    // Check rate limiting
    if (hbl_check_rate_limit('business_registration')) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Rate limit exceeded for business registration',
                'warning',
                array(
                    'ip' => $_SERVER['REMOTE_ADDR']
                )
            );
        }
        
        wp_die(__('Too many submissions. Please try again later.', 'happy-business-listing'), __('Rate Limit Exceeded', 'happy-business-listing'), array('response' => 429));
    }
    
    // Define validation rules
    $rules = array(
        'business_name' => array('type' => 'text', 'args' => array('required' => true)),
        'company_type' => array('type' => 'select', 'args' => array(
            'required' => true,
            'allowed_values' => array('Pvt Ltd', 'LLP', 'OPC', 'Partnership', 'Proprietorship', 'Other')
        )),
        'gst_no' => array('type' => 'text', 'args' => array('required' => true)),
        'tan_pan' => array('type' => 'text', 'args' => array('required' => true)),
        'location' => array('type' => 'text', 'args' => array('required' => true)),
        'website' => array('type' => 'url', 'args' => array('required' => true)),
        'social_media' => array('type' => 'text', 'args' => array('required' => true)),
        'whatsapp_number' => array('type' => 'phone', 'args' => array('required' => true)),
        'email' => array('type' => 'email', 'args' => array('required' => true)),
        'contact_name' => array('type' => 'text', 'args' => array('required' => true)),
        'phone' => array('type' => 'phone', 'args' => array('required' => true)),
        'terms_agreement' => array('type' => 'bool', 'args' => array('required' => true))
    );
    
    // Validate form data
    $sanitized = hbl_validate_form($_POST, $rules);
    
    // Check for validation errors
    if (is_wp_error($sanitized)) {
        $errors = $sanitized->get_error_data();
        $error_messages = '';
        
        foreach ($errors as $field => $error) {
            $error_messages .= '<p>' . $error . '</p>';
        }
        
        wp_die($error_messages, __('Validation Error', 'happy-business-listing'), array('response' => 400, 'back_link' => true));
    }
    
    // Create a new business listing
    $post_id = wp_insert_post(array(
        'post_title' => $sanitized['business_name'],
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'business_listing',
    ));
    
    if (is_wp_error($post_id)) {
        // Log error
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error creating business listing: ' . $post_id->get_error_message(),
                'error',
                array(
                    'business_name' => $sanitized['business_name']
                )
            );
        }
        
        wp_die(__('Error creating business listing. Please try again.', 'happy-business-listing'), __('Error', 'happy-business-listing'), array('response' => 500, 'back_link' => true));
    }
    
    // Save custom fields
    $fields = array(
        'business_name',
        'company_type',
        'gst_no',
        'tan_pan',
        'location',
        'website',
        'social_media',
        'whatsapp_number',
        'email',
        'contact_name',
        'phone'
    );
    
    foreach ($fields as $field) {
        if (isset($sanitized[$field])) {
            hbl_update_field($field, $sanitized[$field], $post_id);
        }
    }
    
    // Set verification status to pending
    hbl_update_field('verification_status', 'pending', $post_id);
    
    // Trigger user and sub-site creation
    $result = hbl_create_user_and_site($post_id, get_post($post_id), false);
    
    if (is_wp_error($result)) {
        // Log error
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error creating user and site: ' . $result->get_error_message(),
                'error',
                array(
                    'business_id' => $post_id,
                    'business_name' => $sanitized['business_name']
                )
            );
        }
        
        // Don't show error to user, just redirect to thank you page
        // The admin will need to manually create the user and site
    }
    
    // Log successful registration
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business registration successful',
            'info',
            array(
                'business_id' => $post_id,
                'business_name' => $sanitized['business_name']
            )
        );
    }
    
    // Redirect after successful registration
    wp_redirect(home_url('/thank-you'));
    exit;
}
add_action('admin_post_nopriv_hbl_register_business', 'hbl_handle_business_registration');
add_action('admin_post_hbl_register_business', 'hbl_handle_business_registration');

/**
 * Enqueue form styles and scripts
 */
function hbl_enqueue_form_assets() {
    wp_register_style(
        'hbl-forms',
        HBL_PLUGIN_URL . 'assets/css/forms.css',
        array(),
        HBL_VERSION
    );
    
    wp_register_script(
        'hbl-forms',
        HBL_PLUGIN_URL . 'assets/js/forms.js',
        array('jquery'),
        HBL_VERSION,
        true
    );
    
    // Only enqueue on pages with our shortcode
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'business_signup_form')) {
        wp_enqueue_style('hbl-forms');
        wp_enqueue_script('hbl-forms');
        
        // Add form validation messages
        wp_localize_script('hbl-forms', 'hbl_forms', array(
            'required' => __('This field is required.', 'happy-business-listing'),
            'email' => __('Please enter a valid email address.', 'happy-business-listing'),
            'url' => __('Please enter a valid URL.', 'happy-business-listing'),
            'phone' => __('Please enter a valid phone number.', 'happy-business-listing'),
            'terms' => __('You must agree to the terms and conditions.', 'happy-business-listing')
        ));
    }
}
add_action('wp_enqueue_scripts', 'hbl_enqueue_form_assets');

/**
 * Create CSS file for forms if it doesn't exist
 */
function hbl_create_form_css() {
    $css_file = HBL_PLUGIN_DIR . 'assets/css/forms.css';
    
    if (!file_exists($css_file)) {
        $css = '/**
 * Happy Business Listing Forms CSS
 */

.hbl-business-registration-form {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="url"],
.form-group input[type="tel"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group input[type="checkbox"] {
    margin-right: 10px;
}

.form-actions {
    margin-top: 30px;
}

.submit-button {
    background-color: #4a90e2;
    color: #fff;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.submit-button:hover {
    background-color: #3a80d2;
}

.form-error {
    color: #dc3545;
    font-size: 14px;
    margin-top: 5px;
}

/* Responsive styles */
@media screen and (max-width: 768px) {
    .hbl-business-registration-form {
        padding: 15px;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"],
    .form-group input[type="tel"],
    .form-group select,
    .form-group textarea {
        font-size: 14px;
    }
    
    .submit-button {
        width: 100%;
    }
}';
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($css_file))) {
            wp_mkdir_p(dirname($css_file));
        }
        
        // Write CSS file
        file_put_contents($css_file, $css);
    }
}
add_action('init', 'hbl_create_form_css');

/**
 * Create JS file for forms if it doesn't exist
 */
function hbl_create_form_js() {
    $js_file = HBL_PLUGIN_DIR . 'assets/js/forms.js';
    
    if (!file_exists($js_file)) {
        $js = '/**
 * Happy Business Listing Forms JS
 */

(function($) {
    "use strict";
    
    // Form validation
    $("#hbl-business-registration-form").on("submit", function(e) {
        var valid = true;
        var firstError = null;
        
        // Remove existing error messages
        $(".form-error").remove();
        
        // Validate required fields
        $(this).find("[required]").each(function() {
            var $field = $(this);
            
            if ($field.val() === "") {
                valid = false;
                var errorMessage = hbl_forms.required;
                
                $field.after("<span class=\'form-error\'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate email fields
        $(this).find("input[type=\'email\']").each(function() {
            var $field = $(this);
            var value = $field.val();
            
            if (value !== "" && !/^[^@]+@[^@]+\.[a-z]{2,}$/i.test(value)) {
                valid = false;
                var errorMessage = hbl_forms.email;
                
                $field.after("<span class=\'form-error\'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate URL fields
        $(this).find("input[type=\'url\']").each(function() {
            var $field = $(this);
            var value = $field.val();
            
            if (value !== "" && !/^https?:\/\/[^\s/$.?#].[^\s]*$/i.test(value)) {
                valid = false;
                var errorMessage = hbl_forms.url;
                
                $field.after("<span class=\'form-error\'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate phone fields
        $(this).find("input[type=\'tel\']").each(function() {
            var $field = $(this);
            var value = $field.val();
            
            if (value !== "" && !/^[0-9+\-() ]{7,}$/.test(value)) {
                valid = false;
                var errorMessage = hbl_forms.phone;
                
                $field.after("<span class=\'form-error\'>" + errorMessage + "</span>");
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate terms checkbox
        if ($(this).find("input[name=\'terms_agreement\']").length && !$(this).find("input[name=\'terms_agreement\']:checked").length) {
            valid = false;
            var $field = $(this).find("input[name=\'terms_agreement\']");
            var errorMessage = hbl_forms.terms;
            
            $field.parent().after("<span class=\'form-error\'>" + errorMessage + "</span>");
            
            if (!firstError) {
                firstError = $field;
            }
        }
        
        // If not valid, prevent form submission and scroll to first error
        if (!valid) {
            e.preventDefault();
            
            if (firstError) {
                $("html, body").animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
                
                firstError.focus();
            }
        }
    });
})(jQuery);';
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($js_file))) {
            wp_mkdir_p(dirname($js_file));
        }
        
        // Write JS file
        file_put_contents($js_file, $js);
    }
}
add_action('init', 'hbl_create_form_js');

/**
 * Contact form shortcode
 *
 * @param array $atts Shortcode attributes
 * @return string The form HTML
 */
function hbl_contact_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'business_id' => 0,
        'title' => __('Contact Us', 'happy-business-listing'),
        'submit_text' => __('Send Message', 'happy-business-listing')
    ), $atts);
    
    $business_id = intval($atts['business_id']);
    
    // If no business ID provided, try to get it from the current post
    if ($business_id === 0 && is_singular('business_listing')) {
        $business_id = get_the_ID();
    }
    
    // If still no business ID, return error message
    if ($business_id === 0) {
        return '<p class="hbl-error">' . __('No business specified for contact form.', 'happy-business-listing') . '</p>';
    }
    
    ob_start();
    ?>
    <div class="hbl-contact-form-container">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <form id="hbl-contact-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="hbl_contact_form">
            <input type="hidden" name="business_id" value="<?php echo esc_attr($business_id); ?>">
            <?php echo hbl_nonce_field('hbl_contact_form'); ?>
            
            <div class="form-group">
                <label for="contact_name"><?php _e('Your Name:', 'happy-business-listing'); ?></label>
                <input type="text" name="contact_name" id="contact_name" required>
            </div>
            
            <div class="form-group">
                <label for="contact_email"><?php _e('Your Email:', 'happy-business-listing'); ?></label>
                <input type="email" name="contact_email" id="contact_email" required>
            </div>
            
            <div class="form-group">
                <label for="contact_phone"><?php _e('Your Phone:', 'happy-business-listing'); ?></label>
                <input type="tel" name="contact_phone" id="contact_phone">
            </div>
            
            <div class="form-group">
                <label for="contact_subject"><?php _e('Subject:', 'happy-business-listing'); ?></label>
                <input type="text" name="contact_subject" id="contact_subject" required>
            </div>
            
            <div class="form-group">
                <label for="contact_message"><?php _e('Message:', 'happy-business-listing'); ?></label>
                <textarea name="contact_message" id="contact_message" rows="5" required></textarea>
            </div>
            
            <?php 
            // Add honeypot field for spam protection
            ?>
            <div class="form-group" style="display:none;">
                <label for="contact_website"><?php _e('Website:', 'happy-business-listing'); ?></label>
                <input type="text" name="contact_website" id="contact_website" autocomplete="off">
            </div>
            
            <div class="form-actions">
                <input type="submit" value="<?php echo esc_attr($atts['submit_text']); ?>" class="submit-button">
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('business_contact_form', 'hbl_contact_form_shortcode');

/**
 * Handle contact form submission
 */
function hbl_handle_contact_form() {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url());
        exit;
    }
    
    // Verify nonce
    hbl_verify_nonce('hbl_nonce', 'hbl_contact_form');
    
    // Check for honeypot field (spam protection)
    if (!empty($_POST['contact_website'])) {
        // This is likely a spam submission
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Spam submission detected in contact form (honeypot field filled)',
                'warning',
                array(
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                )
            );
        }
        
        wp_redirect(home_url('/thank-you'));
        exit;
    }
    
    // Check rate limiting
    if (hbl_check_rate_limit('contact_form')) {
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Rate limit exceeded for contact form',
                'warning',
                array(
                    'ip' => $_SERVER['REMOTE_ADDR']
                )
            );
        }
        
        wp_die(__('Too many submissions. Please try again later.', 'happy-business-listing'), __('Rate Limit Exceeded', 'happy-business-listing'), array('response' => 429));
    }
    
    // Define validation rules
    $rules = array(
        'business_id' => array('type' => 'int', 'args' => array('required' => true, 'min' => 1)),
        'contact_name' => array('type' => 'text', 'args' => array('required' => true)),
        'contact_email' => array('type' => 'email', 'args' => array('required' => true)),
        'contact_phone' => array('type' => 'phone'),
        'contact_subject' => array('type' => 'text', 'args' => array('required' => true)),
        'contact_message' => array('type' => 'textarea', 'args' => array('required' => true))
    );
    
    // Validate form data
    $sanitized = hbl_validate_form($_POST, $rules);
    
    // Check for validation errors
    if (is_wp_error($sanitized)) {
        $errors = $sanitized->get_error_data();
        $error_messages = '';
        
        foreach ($errors as $field => $error) {
            $error_messages .= '<p>' . $error . '</p>';
        }
        
        wp_die($error_messages, __('Validation Error', 'happy-business-listing'), array('response' => 400, 'back_link' => true));
    }
    
    // Get business details
    $business_id = $sanitized['business_id'];
    $business = get_post($business_id);
    
    if (!$business || $business->post_type !== 'business_listing') {
        wp_die(__('Invalid business ID.', 'happy-business-listing'), __('Error', 'happy-business-listing'), array('response' => 400, 'back_link' => true));
    }
    
    // Get business email
    $business_email = hbl_get_business_field('email', $business_id);
    
    if (empty($business_email)) {
        // Fallback to admin email
        $business_email = get_option('admin_email');
    }
    
    // Create lead post
    $lead_id = wp_insert_post(array(
        'post_title' => $sanitized['contact_subject'],
        'post_content' => $sanitized['contact_message'],
        'post_status' => 'publish',
        'post_type' => 'business_lead',
        'meta_input' => array(
            'business_id' => $business_id,
            'contact_name' => $sanitized['contact_name'],
            'contact_email' => $sanitized['contact_email'],
            'contact_phone' => isset($sanitized['contact_phone']) ? $sanitized['contact_phone'] : '',
            'lead_status' => 'new'
        )
    ));
    
    if (is_wp_error($lead_id)) {
        // Log error
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error creating lead: ' . $lead_id->get_error_message(),
                'error',
                array(
                    'business_id' => $business_id
                )
            );
        }
        
        wp_die(__('Error creating lead. Please try again.', 'happy-business-listing'), __('Error', 'happy-business-listing'), array('response' => 500, 'back_link' => true));
    }
    
    // Send email notification
    $to = $business_email;
    $subject = sprintf(__('[%s] New Contact Form Submission: %s', 'happy-business-listing'), get_bloginfo('name'), $sanitized['contact_subject']);
    
    $message = sprintf(__('You have received a new contact form submission from your business listing on %s.', 'happy-business-listing'), get_bloginfo('name')) . "\n\n";
    $message .= sprintf(__('Business: %s', 'happy-business-listing'), $business->post_title) . "\n";
    $message .= sprintf(__('Name: %s', 'happy-business-listing'), $sanitized['contact_name']) . "\n";
    $message .= sprintf(__('Email: %s', 'happy-business-listing'), $sanitized['contact_email']) . "\n";
    
    if (!empty($sanitized['contact_phone'])) {
        $message .= sprintf(__('Phone: %s', 'happy-business-listing'), $sanitized['contact_phone']) . "\n";
    }
    
    $message .= sprintf(__('Subject: %s', 'happy-business-listing'), $sanitized['contact_subject']) . "\n\n";
    $message .= sprintf(__('Message:', 'happy-business-listing')) . "\n";
    $message .= $sanitized['contact_message'] . "\n\n";
    $message .= sprintf(__('You can view and manage all leads in your dashboard: %s', 'happy-business-listing'), admin_url('edit.php?post_type=business_lead')) . "\n";
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $sanitized['contact_name'] . ' <' . $sanitized['contact_email'] . '>'
    );
    
    $email_sent = wp_mail($to, $subject, $message, $headers);
    
    if (!$email_sent) {
        // Log error but don't show to user
        if (hbl_security_logging_enabled()) {
            hbl_log_security_event(
                'Error sending contact form email notification',
                'error',
                array(
                    'business_id' => $business_id,
                    'lead_id' => $lead_id
                )
            );
        }
    }
    
    // Log successful submission
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Contact form submission successful',
            'info',
            array(
                'business_id' => $business_id,
                'lead_id' => $lead_id
            )
        );
    }
    
    // Redirect after successful submission
    wp_redirect(home_url('/thank-you'));
    exit;
}
add_action('admin_post_nopriv_hbl_contact_form', 'hbl_handle_contact_form');
add_action('admin_post_hbl_contact_form', 'hbl_handle_contact_form');