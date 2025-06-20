<?php
/**
 * E2E Tests for WhatsApp Integration
 *
 * @package Happy_Business_Listing
 */

/**
 * Class WhatsAppIntegrationE2ETest
 */
class WhatsAppIntegrationE2ETest extends WP_UnitTestCase {
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
        
        require_once HBL_PLUGIN_DIR . 'includes/helpers.php';
        require_once HBL_PLUGIN_DIR . 'includes/whatsapp-integration.php';
        require_once HBL_PLUGIN_DIR . 'includes/security.php';
    }
    
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Create test user
        $this->user_id = $this->factory->user->create(array(
            'role' => 'business_user',
            'user_login' => 'whatsapp_test_user',
            'user_email' => 'whatsapp@example.com'
        ));
        
        // Create test business
        $this->business_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'WhatsApp Test Business',
            'post_status' => 'publish'
        ));
        
        update_post_meta($this->business_id, 'whatsapp_number', '+1234567890');
        update_post_meta($this->business_id, 'user_id', $this->user_id);
        
        wp_set_current_user($this->user_id);
    }
    
    /**
     * Test complete WhatsApp integration workflow
     */
    public function test_complete_whatsapp_workflow() {
        // Step 1: Configure WhatsApp settings
        $this->configure_whatsapp_settings();
        
        // Step 2: Test message sending
        $this->test_message_sending();
        
        // Step 3: Test lead notifications
        $this->test_lead_notifications();
        
        // Step 4: Test welcome messages
        $this->test_welcome_messages();
        
        // Step 5: Test error handling
        $this->test_error_handling();
        
        // Step 6: Test rate limiting
        $this->test_rate_limiting();
    }
    
    /**
     * Test Twilio integration
     */
    public function test_twilio_integration() {
        // Configure Twilio settings
        update_option('hbl_whatsapp_integration', 'twilio');
        update_option('hbl_twilio_account_sid', 'test_account_sid');
        update_option('hbl_twilio_auth_token', 'test_auth_token');
        update_option('hbl_twilio_from_number', '+1234567890');
        
        // Mock Twilio API response
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        $result = hbl_send_via_twilio('Test message', '+1234567890');
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test WhatsApp Business API integration
     */
    public function test_whatsapp_business_integration() {
        // Configure WhatsApp Business API settings
        update_option('hbl_whatsapp_integration', 'whatsapp_business');
        update_option('hbl_whatsapp_business_api_key', 'test_api_key');
        update_option('hbl_whatsapp_business_phone_id', 'test_phone_id');
        
        // Mock WhatsApp Business API response
        add_filter('pre_http_request', array($this, 'mock_whatsapp_business_response'), 10, 3);
        
        $result = hbl_send_via_whatsapp_business('Test message', '+1234567890');
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_whatsapp_business_response'));
    }
    
    /**
     * Test message validation
     */
    public function test_message_validation() {
        // Test empty message
        $result = hbl_send_whatsapp_message('', '+1234567890');
        $this->assertWPError($result);
        $this->assertEquals('empty_message', $result->get_error_code());
        
        // Test invalid phone number
        $result = hbl_send_whatsapp_message('Test message', 'invalid');
        $this->assertWPError($result);
        $this->assertEquals('invalid_number', $result->get_error_code());
        
        // Test short phone number
        $result = hbl_send_whatsapp_message('Test message', '123');
        $this->assertWPError($result);
        $this->assertEquals('invalid_number', $result->get_error_code());
    }
    
    /**
     * Test attachment handling
     */
    public function test_attachment_handling() {
        // Test valid attachments
        $attachments = array(
            'https://example.com/image1.jpg',
            'https://example.com/image2.png'
        );
        
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        $result = hbl_send_whatsapp_message('Test message with attachments', '+1234567890', $attachments);
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
        
        // Test invalid attachment URL
        $invalid_attachments = array('not-a-url');
        $result = hbl_send_whatsapp_message('Test message', '+1234567890', $invalid_attachments);
        $this->assertWPError($result);
    }
    
    /**
     * Test lead notification workflow
     */
    public function test_lead_notification_workflow() {
        // Create a test lead
        $lead_id = $this->factory->post->create(array(
            'post_type' => 'lead',
            'post_title' => 'Test Lead',
            'post_status' => 'publish'
        ));
        
        update_post_meta($lead_id, 'business_id', $this->business_id);
        update_post_meta($lead_id, 'phone', '+1234567890');
        update_post_meta($lead_id, 'name', 'Test Lead');
        update_post_meta($lead_id, 'email', 'lead@example.com');
        update_post_meta($lead_id, 'message', 'Test lead message');
        
        // Configure WhatsApp settings
        $this->configure_whatsapp_settings();
        
        // Mock API response
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        // Test lead notification
        $result = hbl_notify_lead_creation($lead_id, get_post($lead_id), false);
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test welcome message workflow
     */
    public function test_welcome_message_workflow() {
        // Configure welcome message
        update_option('hbl_welcome_message', 'Welcome to our business! We are excited to have you on board.');
        
        // Mock API response
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        // Test welcome message sending
        $result = hbl_send_welcome_message($this->business_id);
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test message filtering
     */
    public function test_message_filtering() {
        $original_message = 'Test message';
        $phone_number = '+1234567890';
        
        // Add filter to modify message
        add_filter('hbl_whatsapp_message', function($message, $to) {
            return $message . ' [Modified by filter]';
        }, 10, 2);
        
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        $result = hbl_send_whatsapp_message($original_message, $phone_number);
        
        $this->assertTrue($result);
        
        remove_filter('hbl_whatsapp_message', function(){});
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test logging functionality
     */
    public function test_logging_functionality() {
        // Enable logging
        update_option('hbl_enable_logging', '1');
        
        // Mock API response
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        // Send a message
        hbl_send_whatsapp_message('Test message for logging', '+1234567890');
        
        // Check if log file exists
        $log_file = WP_CONTENT_DIR . '/hbl-error.log';
        $this->assertFileExists($log_file);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Configure WhatsApp settings
     */
    protected function configure_whatsapp_settings() {
        update_option('hbl_whatsapp_integration', 'twilio');
        update_option('hbl_twilio_account_sid', 'test_account_sid');
        update_option('hbl_twilio_auth_token', 'test_auth_token');
        update_option('hbl_twilio_from_number', '+1234567890');
        update_option('hbl_welcome_message', 'Welcome to our business!');
        update_option('hbl_enable_whatsapp_rate_limiting', '1');
        update_option('hbl_whatsapp_rate_limit', '10');
    }
    
    /**
     * Test message sending
     */
    protected function test_message_sending() {
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        $result = hbl_send_whatsapp_message('Test message', '+1234567890');
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test lead notifications
     */
    protected function test_lead_notifications() {
        $lead_id = $this->factory->post->create(array(
            'post_type' => 'lead',
            'post_title' => 'Test Lead',
            'post_status' => 'publish'
        ));
        
        update_post_meta($lead_id, 'business_id', $this->business_id);
        update_post_meta($lead_id, 'phone', '+1234567890');
        
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        $result = hbl_notify_lead_creation($lead_id, get_post($lead_id), false);
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test welcome messages
     */
    protected function test_welcome_messages() {
        add_filter('pre_http_request', array($this, 'mock_twilio_response'), 10, 3);
        
        $result = hbl_send_welcome_message($this->business_id);
        
        $this->assertTrue($result);
        
        remove_filter('pre_http_request', array($this, 'mock_twilio_response'));
    }
    
    /**
     * Test error handling
     */
    protected function test_error_handling() {
        // Test missing credentials
        update_option('hbl_twilio_account_sid', '');
        
        $result = hbl_send_whatsapp_message('Test message', '+1234567890');
        
        $this->assertWPError($result);
        $this->assertEquals('missing_credentials', $result->get_error_code());
    }
    
    /**
     * Test rate limiting
     */
    protected function test_rate_limiting() {
        // Mock rate limit exceeded
        add_filter('hbl_check_rate_limit', '__return_true');
        
        $result = hbl_send_whatsapp_message('Test message', '+1234567890');
        
        $this->assertWPError($result);
        $this->assertEquals('rate_limited', $result->get_error_code());
        
        remove_filter('hbl_check_rate_limit', '__return_true');
    }
    
    /**
     * Mock Twilio API response
     */
    public function mock_twilio_response($preempt, $args, $url) {
        if (strpos($url, 'api.twilio.com') !== false) {
            return array(
                'response' => array('code' => 200),
                'body' => json_encode(array(
                    'sid' => 'test_sid_' . uniqid(),
                    'status' => 'sent'
                ))
            );
        }
        return $preempt;
    }
    
    /**
     * Mock WhatsApp Business API response
     */
    public function mock_whatsapp_business_response($preempt, $args, $url) {
        if (strpos($url, 'graph.facebook.com') !== false) {
            return array(
                'response' => array('code' => 200),
                'body' => json_encode(array(
                    'id' => 'test_message_id_' . uniqid(),
                    'status' => 'sent'
                ))
            );
        }
        return $preempt;
    }
} 