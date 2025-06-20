<?php

class UserRegistrationTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the user registration functions
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/user-registration.php');
    }

    public function test_hbl_register_business_user() {
        $_POST = array(
            'business_name' => 'Test Business',
            'company_type' => 'LLC',
            'gst_no' => '123456789',
            'tan_pan' => '987654321',
            'location' => 'Test City',
            'website' => 'https://testbusiness.com',
            'social_media' => 'https://facebook.com/testbusiness',
            'whatsapp_number' => '1234567890',
            'user_email' => 'test@testbusiness.com',
            'user_pass' => 'testpassword123'
        );

        // Mock the wp_insert_user function
        add_filter('wp_insert_user_data', function($data) {
            return array('ID' => 1); // Return a mock user ID
        });

        // Call the function
        $result = hbl_register_business_user();

        // Assert that the function returns true (successful registration)
        $this->assertTrue($result);

        // Check if the user meta was set correctly
        $this->assertEquals('Test Business', get_user_meta(1, 'business_name', true));
        $this->assertEquals('LLC', get_user_meta(1, 'company_type', true));
        $this->assertEquals('123456789', get_user_meta(1, 'gst_no', true));
        $this->assertEquals('987654321', get_user_meta(1, 'tan_pan', true));
        $this->assertEquals('Test City', get_user_meta(1, 'location', true));
        $this->assertEquals('https://testbusiness.com', get_user_meta(1, 'website', true));
        $this->assertEquals('https://facebook.com/testbusiness', get_user_meta(1, 'social_media', true));
        $this->assertEquals('1234567890', get_user_meta(1, 'whatsapp_number', true));
    }

    public function test_hbl_register_business_user_missing_fields() {
        $_POST = array(
            'business_name' => 'Test Business',
            // Missing other required fields
        );

        // Call the function
        $result = hbl_register_business_user();

        // Assert that the function returns false (failed registration)
        $this->assertFalse($result);
    }

    public function test_hbl_register_business_user_existing_email() {
        // Create a user with the same email
        wp_create_user('existinguser', 'password', 'test@testbusiness.com');

        $_POST = array(
            'business_name' => 'Test Business',
            'company_type' => 'LLC',
            'gst_no' => '123456789',
            'tan_pan' => '987654321',
            'location' => 'Test City',
            'website' => 'https://testbusiness.com',
            'social_media' => 'https://facebook.com/testbusiness',
            'whatsapp_number' => '1234567890',
            'user_email' => 'test@testbusiness.com',
            'user_pass' => 'testpassword123'
        );

        // Call the function
        $result = hbl_register_business_user();

        // Assert that the function returns false (failed registration due to existing email)
        $this->assertFalse($result);
    }
}