<?php

class FormShortcodeTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the shortcode function
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/form-shortcode.php');
    }

    public function test_form_shortcode() {
        // Register the shortcode
        add_shortcode('business_signup_form', 'hbl_register_form_shortcode');

        // Generate the shortcode content
        $shortcode_content = do_shortcode('[business_signup_form]');

        // Assert that the form contains expected elements
        $this->assertStringContainsString('<form', $shortcode_content);
        $this->assertStringContainsString('method="POST"', $shortcode_content);
        $this->assertStringContainsString('id="hbl-business-registration-form"', $shortcode_content);
        
        // Check for specific form fields
        $this->assertStringContainsString('name="business_name"', $shortcode_content);
        $this->assertStringContainsString('name="company_type"', $shortcode_content);
        $this->assertStringContainsString('name="gst_no"', $shortcode_content);
        $this->assertStringContainsString('name="tan_pan"', $shortcode_content);
        $this->assertStringContainsString('name="location"', $shortcode_content);
        $this->assertStringContainsString('name="website"', $shortcode_content);
        $this->assertStringContainsString('name="social_media"', $shortcode_content);
        $this->assertStringContainsString('name="whatsapp_number"', $shortcode_content);
        
        // Check for submit button
        $this->assertStringContainsString('type="submit"', $shortcode_content);
        $this->assertStringContainsString('value="Register Business"', $shortcode_content);
    }
}