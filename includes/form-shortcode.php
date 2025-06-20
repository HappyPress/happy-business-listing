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
    <div class="hbl-business-registration-form">
        <div class="form-header">
            <h2><?php _e('Register Your Business', 'happy-business-listing'); ?></h2>
            <p class="form-description"><?php _e('Join our business directory and get discovered by potential customers.', 'happy-business-listing'); ?></p>
        </div>
        
        <form id="hbl-business-registration-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="hbl_register_business">
            <?php echo hbl_nonce_field('hbl_register_business'); ?>
            
            <div class="form-section">
                <h3 class="section-title"><?php _e('Basic Information', 'happy-business-listing'); ?></h3>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="business_name"><?php _e('Business Name:', 'happy-business-listing'); ?> <span class="required">*</span></label>
                        <input type="text" name="business_name" id="business_name" required placeholder="<?php _e('Enter your business name', 'happy-business-listing'); ?>">
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="company_type"><?php _e('Type of Company:', 'happy-business-listing'); ?></label>
                        <select name="company_type" id="company_type">
                            <option value=""><?php _e('Select type', 'happy-business-listing'); ?></option>
                            <option value="Pvt Ltd"><?php _e('Pvt Ltd', 'happy-business-listing'); ?></option>
                            <option value="LLP"><?php _e('LLP', 'happy-business-listing'); ?></option>
                            <option value="OPC"><?php _e('OPC', 'happy-business-listing'); ?></option>
                            <option value="Sole Proprietorship"><?php _e('Sole Proprietorship', 'happy-business-listing'); ?></option>
                            <option value="Partnership"><?php _e('Partnership', 'happy-business-listing'); ?></option>
                            <option value="Other"><?php _e('Other', 'happy-business-listing'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="gst_no"><?php _e('GST No.:', 'happy-business-listing'); ?></label>
                        <input type="text" name="gst_no" id="gst_no" placeholder="<?php _e('Enter GST number', 'happy-business-listing'); ?>">
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="tan_pan"><?php _e('TAN/PAN:', 'happy-business-listing'); ?></label>
                        <input type="text" name="tan_pan" id="tan_pan" placeholder="<?php _e('Enter TAN/PAN', 'happy-business-listing'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location"><?php _e('Location/s:', 'happy-business-listing'); ?> <span class="required">*</span></label>
                    <input type="text" name="location" id="location" required placeholder="<?php _e('Enter business location', 'happy-business-listing'); ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="section-title"><?php _e('Contact Information', 'happy-business-listing'); ?></h3>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="email"><?php _e('Business Email:', 'happy-business-listing'); ?> <span class="required">*</span></label>
                        <input type="email" name="email" id="email" required placeholder="<?php _e('business@example.com', 'happy-business-listing'); ?>">
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="phone"><?php _e('Phone Number:', 'happy-business-listing'); ?></label>
                        <input type="tel" name="phone" id="phone" placeholder="<?php _e('+1 (555) 123-4567', 'happy-business-listing'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="website"><?php _e('Website:', 'happy-business-listing'); ?></label>
                        <input type="url" name="website" id="website" placeholder="<?php _e('https://www.example.com', 'happy-business-listing'); ?>">
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="whatsapp_number"><?php _e('WhatsApp Number:', 'happy-business-listing'); ?></label>
                        <input type="tel" name="whatsapp_number" id="whatsapp_number" placeholder="<?php _e('+1 (555) 123-4567', 'happy-business-listing'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="social_media_handles"><?php _e('Social Media:', 'happy-business-listing'); ?></label>
                    <input type="text" name="social_media_handles" id="social_media_handles" placeholder="<?php _e('Facebook, Instagram, Twitter handles', 'happy-business-listing'); ?>">
                    <small class="form-help"><?php _e('Separate multiple social media handles with commas', 'happy-business-listing'); ?></small>
                </div>
            </div>
            
            <div class="form-section">
                <h3 class="section-title"><?php _e('Business Description', 'happy-business-listing'); ?></h3>
                
                <div class="form-group">
                    <label for="business_description"><?php _e('Tell us about your business:', 'happy-business-listing'); ?></label>
                    <textarea name="business_description" id="business_description" rows="5" placeholder="<?php _e('Describe your business, services, and what makes you unique...', 'happy-business-listing'); ?>"></textarea>
                </div>
            </div>
            
            <?php 
            // Add honeypot field for spam protection
            ?>
            <div class="form-group" style="display:none;">
                <label for="website_url"><?php _e('Website URL:', 'happy-business-listing'); ?></label>
                <input type="text" name="website_url" id="website_url" autocomplete="off">
            </div>
            
            <div class="form-section">
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms_agreement" value="1" required>
                        <span class="checkmark"></span>
                        <?php _e('I agree to the', 'happy-business-listing'); ?> <a href="/terms" target="_blank"><?php _e('Terms and Conditions', 'happy-business-listing'); ?></a> <span class="required">*</span>
                    </label>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="marketing_consent" value="1">
                        <span class="checkmark"></span>
                        <?php _e('I agree to receive marketing communications', 'happy-business-listing'); ?>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">
                    <span class="button-text"><?php _e('Submit Business Listing', 'happy-business-listing'); ?></span>
                    <span class="button-loading" style="display:none;"><?php _e('Processing...', 'happy-business-listing'); ?></span>
                </button>
            </div>
        </form>
    </div>
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
    $thank_you_page_id = get_option('hbl_thank_you_page_id');
    if ($thank_you_page_id) {
        $redirect_url = get_permalink($thank_you_page_id);
    } else {
        $redirect_url = home_url('/thank-you');
    }
    
    wp_redirect($redirect_url);
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
 * Happy Business Listing Forms CSS - Modern Design
 */

.hbl-business-registration-form {
    max-width: 900px;
    margin: 0 auto;
    padding: 0;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
}

.form-header h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 600;
    letter-spacing: -0.5px;
}

.form-description {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
    line-height: 1.5;
}

form {
    padding: 40px 30px;
}

.form-section {
    margin-bottom: 40px;
    padding: 0;
}

.form-section:last-of-type {
    margin-bottom: 20px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
}

.section-title:before {
    content: "";
    width: 4px;
    height: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin-right: 12px;
    border-radius: 2px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group-half {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2d3748;
    font-size: 14px;
    letter-spacing: 0.025em;
}

.required {
    color: #e53e3e;
    font-weight: 600;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="url"],
.form-group input[type="tel"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    background-color: #ffffff;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-group input[type="text"]:focus,
.form-group input[type="email"]:focus,
.form-group input[type="url"]:focus,
.form-group input[type="tel"]:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background-color: #ffffff;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
    line-height: 1.5;
}

.form-group select {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
}

.form-help {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #718096;
    line-height: 1.4;
}

/* Checkbox Styling */
.checkbox-group {
    margin: 16px 0;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    font-size: 14px;
    line-height: 1.5;
    color: #4a5568;
}

.checkbox-label input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: relative;
    top: 2px;
    height: 18px;
    width: 18px;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 4px;
    margin-right: 12px;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.checkbox-label:hover .checkmark {
    border-color: #667eea;
}

.checkbox-label input:checked ~ .checkmark {
    background-color: #667eea;
    border-color: #667eea;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label input:checked ~ .checkmark:after {
    display: block;
}

/* Submit Button */
.form-actions {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.submit-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    border: none;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 200px;
    position: relative;
    letter-spacing: 0.025em;
}

.submit-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.submit-button:active {
    transform: translateY(0);
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.button-loading {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
}

/* Error Styling */
.form-error {
    color: #e53e3e;
    font-size: 13px;
    margin-top: 6px;
    display: block;
    font-weight: 500;
}

.form-group.has-error input,
.form-group.has-error select,
.form-group.has-error textarea {
    border-color: #e53e3e;
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .hbl-business-registration-form {
        margin: 20px;
        border-radius: 8px;
    }
    
    .form-header {
        padding: 30px 20px;
    }
    
    .form-header h2 {
        font-size: 24px;
    }
    
    form {
        padding: 30px 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .form-group-half {
        margin-bottom: 20px;
    }
    
    .submit-button {
        width: 100%;
        padding: 18px;
    }
}

@media screen and (max-width: 480px) {
    .hbl-business-registration-form {
        margin: 10px;
    }
    
    .form-header {
        padding: 25px 15px;
    }
    
    form {
        padding: 25px 15px;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"],
    .form-group input[type="tel"],
    .form-group select,
    .form-group textarea {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}

/* Success Messages */
.form-success {
    background-color: #f0fff4;
    border: 1px solid #9ae6b4;
    color: #276749;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

/* Loading State */
.form-loading {
    position: relative;
    pointer-events: none;
}

.form-loading:after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}
';
        
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
 * Happy Business Listing Forms JS - Enhanced
 */

(function($) {
    "use strict";
    
    // Initialize form enhancements
    $(document).ready(function() {
        initFormEnhancements();
    });
    
    function initFormEnhancements() {
        var $form = $("#hbl-business-registration-form");
        
        if ($form.length === 0) return;
        
        // Add loading functionality
        addLoadingStates($form);
        
        // Add real-time validation
        addRealTimeValidation($form);
        
        // Add form submission handling
        addSubmissionHandling($form);
        
        // Add helpful UX enhancements
        addUXEnhancements($form);
    }
    
    function addLoadingStates($form) {
        $form.on("submit", function() {
            var $button = $form.find(".submit-button");
            var $buttonText = $button.find(".button-text");
            var $buttonLoading = $button.find(".button-loading");
            
            $button.prop("disabled", true);
            $buttonText.hide();
            $buttonLoading.show();
            $form.addClass("form-loading");
        });
    }
    
    function addRealTimeValidation($form) {
        // Email validation
        $form.find("input[type=\'email\']").on("blur", function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidEmail(value)) {
                showFieldError($field, hbl_forms.email);
            } else {
                clearFieldError($field);
            }
        });
        
        // URL validation
        $form.find("input[type=\'url\']").on("blur", function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidURL(value)) {
                showFieldError($field, hbl_forms.url);
            } else {
                clearFieldError($field);
            }
        });
        
        // Phone validation
        $form.find("input[type=\'tel\']").on("blur", function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidPhone(value)) {
                showFieldError($field, hbl_forms.phone);
            } else {
                clearFieldError($field);
            }
        });
        
        // Required field validation
        $form.find("[required]").on("blur", function() {
            var $field = $(this);
            
            if (!$field.val().trim()) {
                showFieldError($field, hbl_forms.required);
            } else {
                clearFieldError($field);
            }
        });
    }
    
    function addSubmissionHandling($form) {
        $form.on("submit", function(e) {
            var valid = validateForm($form);
            
            if (!valid) {
                e.preventDefault();
                
                // Reset button state
                var $button = $form.find(".submit-button");
                var $buttonText = $button.find(".button-text");
                var $buttonLoading = $button.find(".button-loading");
                
                $button.prop("disabled", false);
                $buttonText.show();
                $buttonLoading.hide();
                $form.removeClass("form-loading");
                
                // Scroll to first error
                var $firstError = $form.find(".form-error").first();
                if ($firstError.length) {
                    $("html, body").animate({
                        scrollTop: $firstError.closest(".form-group").offset().top - 100
                    }, 500);
                }
            }
        });
    }
    
    function addUXEnhancements($form) {
        // Auto-format phone numbers
        $form.find("input[type=\'tel\']").on("input", function() {
            var $field = $(this);
            var value = $field.val().replace(/\\D/g, "");
            
            if (value.length >= 10) {
                var formatted = value.replace(/(\\d{3})(\\d{3})(\\d{4})/, "($1) $2-$3");
                $field.val(formatted);
            }
        });
        
        // Auto-correct URLs
        $form.find("input[type=\'url\']").on("blur", function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !value.match(/^https?:\\/\\//)) {
                $field.val("https://" + value);
            }
        });
        
        // Character counter for description
        var $description = $form.find("#business_description");
        if ($description.length) {
            var $counter = $("<div class=\\"character-counter\\"></div>");
            $description.after($counter);
            
            $description.on("input", function() {
                var length = $(this).val().length;
                var maxLength = 500;
                var remaining = maxLength - length;
                
                $counter.text(remaining + " characters remaining");
                
                if (remaining < 50) {
                    $counter.addClass("warning");
                } else {
                    $counter.removeClass("warning");
                }
            });
            
            $description.trigger("input");
        }
    }
    
    function validateForm($form) {
        var valid = true;
        var firstError = null;
        
        // Clear all existing errors
        $form.find(".form-error").remove();
        $form.find(".form-group").removeClass("has-error");
        
        // Validate required fields
        $form.find("[required]").each(function() {
            var $field = $(this);
            
            if (!$field.val().trim()) {
                showFieldError($field, hbl_forms.required);
                valid = false;
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate email fields
        $form.find("input[type=\'email\']").each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidEmail(value)) {
                showFieldError($field, hbl_forms.email);
                valid = false;
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate URL fields
        $form.find("input[type=\'url\']").each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidURL(value)) {
                showFieldError($field, hbl_forms.url);
                valid = false;
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate phone fields
        $form.find("input[type=\'tel\']").each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value && !isValidPhone(value)) {
                showFieldError($field, hbl_forms.phone);
                valid = false;
                
                if (!firstError) {
                    firstError = $field;
                }
            }
        });
        
        // Validate terms checkbox
        if (!$form.find("input[name=\'terms_agreement\']:checked").length) {
            var $field = $form.find("input[name=\'terms_agreement\']");
            showFieldError($field, hbl_forms.terms);
            valid = false;
            
            if (!firstError) {
                firstError = $field;
            }
        }
        
        return valid;
    }
    
    function showFieldError($field, message) {
        var $group = $field.closest(".form-group");
        $group.addClass("has-error");
        
        // Remove existing error
        $group.find(".form-error").remove();
        
        // Add new error
        $group.append("<span class=\\"form-error\\">" + message + "</span>");
    }
    
    function clearFieldError($field) {
        var $group = $field.closest(".form-group");
        $group.removeClass("has-error");
        $group.find(".form-error").remove();
    }
    
    function isValidEmail(email) {
        var re = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        return re.test(email);
    }
    
    function isValidURL(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
    
    function isValidPhone(phone) {
        var cleaned = phone.replace(/\\D/g, "");
        return cleaned.length >= 10 && cleaned.length <= 15;
    }
    
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