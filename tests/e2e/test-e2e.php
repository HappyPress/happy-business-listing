<?php
/**
 * End-to-end tests for Happy Business Listing
 *
 * These tests simulate a complete user journey through the plugin.
 *
 * @package Happy_Business_Listing
 */

/**
 * Class E2ETest
 */
class E2ETest extends WP_UnitTestCase {
    /**
     * Test user ID
     *
     * @var int
     */
    protected $user_id;
    
    /**
     * Test business ID
     *
     * @var int
     */
    protected $business_id;
    
    /**
     * Set up before class
     */
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        
        // Make sure all required files are loaded
        require_once HBL_PLUGIN_DIR . 'includes/helpers.php';
        require_once HBL_PLUGIN_DIR . 'includes/security.php';
        require_once HBL_PLUGIN_DIR . 'includes/form-shortcode.php';
        require_once HBL_PLUGIN_DIR . 'includes/user-registration.php';
        require_once HBL_PLUGIN_DIR . 'includes/templates.php';
        require_once HBL_PLUGIN_DIR . 'includes/whatsapp-integration.php';
    }
    
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Create a test user
        $this->user_id = $this->factory->user->create(array(
            'role' => 'business_user',
            'user_login' => 'testuser',
            'user_email' => 'testuser@example.com',
            'display_name' => 'Test User'
        ));
        
        // Set current user
        wp_set_current_user($this->user_id);
    }
    
    /**
     * Tear down after each test
     */
    public function tearDown(): void {
        parent::tearDown();
        
        // Reset current user
        wp_set_current_user(0);
    }
    
    /**
     * Test complete user journey
     */
    public function test_complete_user_journey() {
        // Step 1: Submit business registration form
        $this->business_id = $this->submit_business_registration_form();
        
        // Step 2: Verify business listing was created
        $this->verify_business_listing();
        
        // Step 3: Verify user was created and associated with business
        $this->verify_user_association();
        
        // Step 4: Add services to the business
        $service_ids = $this->add_services_to_business();
        
        // Step 5: Submit contact form to create a lead
        $lead_id = $this->submit_contact_form();
        
        // Step 6: Verify lead was created and associated with business
        $this->verify_lead($lead_id);
        
        // Step 7: Test WhatsApp integration
        $this->test_whatsapp_integration();
    }
    
    /**
     * Submit business registration form
     *
     * @return int Business ID
     */
    protected function submit_business_registration_form() {
        // Set up nonce verification to always return true for testing
        add_filter('wp_verify_nonce', '__return_true');
        
        // Set up POST data
        $_POST = array(
            'action' => 'hbl_register_business',
            'hbl_nonce' => 'test_nonce',
            'business_name' => 'E2E Test Business',
            'company_type' => 'Pvt Ltd',
            'gst_no' => 'GST12345678',
            'tan_pan' => 'TAN12345678',
            'location' => 'Test Location',
            'website' => 'https://example.com',
            'social_media' => 'facebook: https://facebook.com/testbusiness, twitter: https://twitter.com/testbusiness',
            'whatsapp_number' => '+1234567890',
            'email' => 'business@example.com',
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
        
        // Remove the nonce filter
        remove_filter('wp_verify_nonce', '__return_true');
        
        // Get the business ID
        $posts = get_posts(array(
            'post_type' => 'business_listing',
            'post_status' => 'publish',
            'meta_key' => 'business_name',
            'meta_value' => 'E2E Test Business'
        ));
        
        $this->assertCount(1, $posts);
        
        return $posts[0]->ID;
    }
    
    /**
     * Verify business listing
     */
    protected function verify_business_listing() {
        // Get the business
        $business = get_post($this->business_id);
        
        // Check business data
        $this->assertEquals('E2E Test Business', $business->post_title);
        $this->assertEquals('publish', $business->post_status);
        $this->assertEquals('business_listing', $business->post_type);
        
        // Check business meta data
        $this->assertEquals('E2E Test Business', get_post_meta($this->business_id, 'business_name', true));
        $this->assertEquals('Pvt Ltd', get_post_meta($this->business_id, 'company_type', true));
        $this->assertEquals('GST12345678', get_post_meta($this->business_id, 'gst_no', true));
        $this->assertEquals('TAN12345678', get_post_meta($this->business_id, 'tan_pan', true));
        $this->assertEquals('Test Location', get_post_meta($this->business_id, 'location', true));
        $this->assertEquals('https://example.com', get_post_meta($this->business_id, 'website', true));
        $this->assertEquals('+1234567890', get_post_meta($this->business_id, 'whatsapp_number', true));
        $this->assertEquals('business@example.com', get_post_meta($this->business_id, 'email', true));
        $this->assertEquals('Test Contact', get_post_meta($this->business_id, 'contact_name', true));
        $this->assertEquals('+1234567890', get_post_meta($this->business_id, 'phone', true));
    }
    
    /**
     * Verify user association
     */
    protected function verify_user_association() {
        // Check if a user was created and associated with the business
        $user_id = get_post_meta($this->business_id, 'user_id', true);
        
        $this->assertNotEmpty($user_id);
        
        // Get the user
        $user = get_userdata($user_id);
        
        // Check user data
        $this->assertEquals('business@example.com', $user->user_email);
        $this->assertEquals('E2E Test Business', $user->display_name);
        $this->assertEquals('business_user', $user->roles[0]);
        
        // Check user meta data
        $this->assertEquals($this->business_id, get_user_meta($user_id, 'user_business_id', true));
        $this->assertEquals('+1234567890', get_user_meta($user_id, 'whatsapp_number', true));
    }
    
    /**
     * Add services to business
     *
     * @return array Service IDs
     */
    protected function add_services_to_business() {
        // Create services
        $service_ids = array();
        
        for ($i = 1; $i <= 3; $i++) {
            $service_id = wp_insert_post(array(
                'post_title' => "Service $i",
                'post_content' => "This is service $i.",
                'post_status' => 'publish',
                'post_type' => 'service_product',
                'meta_input' => array(
                    'business_id' => $this->business_id,
                    'price' => $i * 100,
                    'service_type' => 'service'
                )
            ));
            
            $service_ids[] = $service_id;
        }
        
        // Verify services were created
        $this->assertCount(3, $service_ids);
        
        // Verify services are associated with the business
        $services = get_posts(array(
            'post_type' => 'service_product',
            'posts_per_page' => -1,
            'meta_key' => 'business_id',
            'meta_value' => $this->business_id
        ));
        
        $this->assertCount(3, $services);
        
        return $service_ids;
    }
    
    /**
     * Submit contact form
     *
     * @return int Lead ID
     */
    protected function submit_contact_form() {
        // Set up nonce verification to always return true for testing
        add_filter('wp_verify_nonce', '__return_true');
        
        // Set up POST data
        $_POST = array(
            'action' => 'hbl_contact_form',
            'hbl_nonce' => 'test_nonce',
            'business_id' => $this->business_id,
            'contact_name' => 'Lead Contact',
            'contact_email' => 'lead@example.com',
            'contact_phone' => '+9876543210',
            'contact_subject' => 'Test Lead',
            'contact_message' => 'This is a test lead message.'
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
        
        // Remove the nonce filter
        remove_filter('wp_verify_nonce', '__return_true');
        
        // Get the lead ID
        $posts = get_posts(array(
            'post_type' => 'lead',
            'post_status' => 'publish',
            'meta_key' => 'business_id',
            'meta_value' => $this->business_id
        ));
        
        $this->assertCount(1, $posts);
        
        return $posts[0]->ID;
    }
    
    /**
     * Verify lead
     *
     * @param int $lead_id Lead ID
     */
    protected function verify_lead($lead_id) {
        // Get the lead
        $lead = get_post($lead_id);
        
        // Check lead data
        $this->assertEquals('Test Lead', $lead->post_title);
        $this->assertEquals('This is a test lead message.', $lead->post_content);
        $this->assertEquals('publish', $lead->post_status);
        $this->assertEquals('lead', $lead->post_type);
        
        // Check lead meta data
        $this->assertEquals($this->business_id, get_post_meta($lead_id, 'business_id', true));
        $this->assertEquals('Lead Contact', get_post_meta($lead_id, 'lead_name', true));
        $this->assertEquals('lead@example.com', get_post_meta($lead_id, 'lead_email', true));
        $this->assertEquals('+9876543210', get_post_meta($lead_id, 'lead_phone', true));
        $this->assertEquals('new', get_post_meta($lead_id, 'lead_status', true));
    }
    
    /**
     * Test WhatsApp integration
     */
    protected function test_whatsapp_integration() {
        // Mock the WhatsApp API response
        add_filter('pre_http_request', function($preempt, $args, $url) {
            if (strpos($url, 'api.twilio.com') !== false || strpos($url, 'graph.facebook.com') !== false) {
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array(
                        'sid' => 'test_sid',
                        'messages' => array(
                            array('id' => 'test_message_id')
                        )
                    ))
                );
            }
            
            return $preempt;
        }, 10, 3);
        
        // Test sending a WhatsApp message
        $result = hbl_send_whatsapp_message('Test message', '+1234567890');
        
        // Check if the message was sent successfully
        $this->assertTrue($result);
        
        // Remove the filter
        remove_filter('pre_http_request', function(){}, 10);
    }
}