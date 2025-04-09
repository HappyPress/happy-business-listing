<?php
/**
 * Class FormSubmissionTest
 *
 * @package Happy_Business_Listing
 */

/**
 * Form submission test case.
 */
class FormSubmissionTest extends WP_UnitTestCase {
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Make sure the form shortcode functions are loaded
        require_once HBL_PLUGIN_DIR . 'includes/form-shortcode.php';
        
        // Set up nonce verification to always return true for testing
        add_filter('wp_verify_nonce', '__return_true');
    }
    
    /**
     * Tear down after each test
     */
    public function tearDown(): void {
        parent::tearDown();
        
        // Remove the nonce filter
        remove_filter('wp_verify_nonce', '__return_true');
    }
    
    /**
     * Test business registration form submission
     */
    public function test_business_registration_form_submission() {
        // Set up POST data
        $_POST = array(
            'action' => 'hbl_register_business',
            'hbl_nonce' => wp_create_nonce('hbl_register_business'),
            'business_name' => 'Test Business',
            'company_type' => 'Pvt Ltd',
            'gst_no' => 'GST12345678',
            'tan_pan' => 'TAN12345678',
            'location' => 'Test Location',
            'website' => 'https://example.com',
            'social_media' => 'facebook: https://facebook.com/testbusiness, twitter: https://twitter.com/testbusiness',
            'whatsapp_number' => '+1234567890',
            'email' => 'test@example.com',
            'contact_name' => 'Test Contact',
            'phone' => '+1234567890',
            'terms_agreement' => '1'
        );
        
        // Set up the request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture the output
        ob_start();
        
        // Call the handler function
        try {
            // We need to catch the exit call
            hbl_handle_business_registration();
        } catch (Exception $e) {
            // Ignore the exception
        }
        
        // Get the output
        $output = ob_get_clean();
        
        // Check if a business listing was created
        $posts = get_posts(array(
            'post_type' => 'business_listing',
            'post_status' => 'publish',
            'meta_key' => 'business_name',
            'meta_value' => 'Test Business'
        ));
        
        $this->assertCount(1, $posts);
        
        // Check if the business listing has the correct meta data
        $post_id = $posts[0]->ID;
        
        $this->assertEquals('Test Business', get_post_meta($post_id, 'business_name', true));
        $this->assertEquals('Pvt Ltd', get_post_meta($post_id, 'company_type', true));
        $this->assertEquals('GST12345678', get_post_meta($post_id, 'gst_no', true));
        $this->assertEquals('TAN12345678', get_post_meta($post_id, 'tan_pan', true));
        $this->assertEquals('Test Location', get_post_meta($post_id, 'location', true));
        $this->assertEquals('https://example.com', get_post_meta($post_id, 'website', true));
        $this->assertEquals('+1234567890', get_post_meta($post_id, 'whatsapp_number', true));
        $this->assertEquals('test@example.com', get_post_meta($post_id, 'email', true));
        $this->assertEquals('Test Contact', get_post_meta($post_id, 'contact_name', true));
        $this->assertEquals('+1234567890', get_post_meta($post_id, 'phone', true));
    }
    
    /**
     * Test contact form submission
     */
    public function test_contact_form_submission() {
        // Create a test business
        $business_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish',
            'meta_input' => array(
                'email' => 'business@example.com'
            )
        ));
        
        // Set up POST data
        $_POST = array(
            'action' => 'hbl_contact_form',
            'hbl_nonce' => wp_create_nonce('hbl_contact_form'),
            'business_id' => $business_id,
            'contact_name' => 'Test Contact',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '+1234567890',
            'contact_subject' => 'Test Subject',
            'contact_message' => 'Test Message'
        );
        
        // Set up the request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture the output
        ob_start();
        
        // Call the handler function
        try {
            // We need to catch the exit call
            hbl_handle_contact_form();
        } catch (Exception $e) {
            // Ignore the exception
        }
        
        // Get the output
        $output = ob_get_clean();
        
        // Check if a lead was created
        $posts = get_posts(array(
            'post_type' => 'lead',
            'post_status' => 'publish',
            'meta_key' => 'business_id',
            'meta_value' => $business_id
        ));
        
        $this->assertCount(1, $posts);
        
        // Check if the lead has the correct meta data
        $post_id = $posts[0]->ID;
        
        $this->assertEquals($business_id, get_post_meta($post_id, 'business_id', true));
        $this->assertEquals('Test Contact', get_post_meta($post_id, 'lead_name', true));
        $this->assertEquals('contact@example.com', get_post_meta($post_id, 'lead_email', true));
        $this->assertEquals('+1234567890', get_post_meta($post_id, 'lead_phone', true));
        $this->assertEquals('new', get_post_meta($post_id, 'lead_status', true));
    }
}