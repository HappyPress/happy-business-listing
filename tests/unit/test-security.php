<?php
/**
 * Class SecurityTest
 *
 * @package Happy_Business_Listing
 */

/**
 * Security functions test case.
 */
class SecurityTest extends WP_UnitTestCase {
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Make sure the security functions are loaded
        require_once HBL_PLUGIN_DIR . 'includes/security.php';
    }
    
    /**
     * Test sanitize input function with text type
     */
    public function test_sanitize_input_text() {
        $input = '<script>alert("XSS");</script>Hello World';
        $sanitized = hbl_sanitize_input($input, 'text');
        
        $this->assertEquals('Hello World', $sanitized);
    }
    
    /**
     * Test sanitize input function with email type
     */
    public function test_sanitize_input_email() {
        // Valid email
        $input = 'test@example.com';
        $sanitized = hbl_sanitize_input($input, 'email');
        
        $this->assertEquals('test@example.com', $sanitized);
        
        // Invalid email
        $input = 'not-an-email';
        $sanitized = hbl_sanitize_input($input, 'email');
        
        $this->assertInstanceOf('WP_Error', $sanitized);
        $this->assertEquals('invalid_email', $sanitized->get_error_code());
    }
    
    /**
     * Test sanitize input function with URL type
     */
    public function test_sanitize_input_url() {
        // Valid URL
        $input = 'https://example.com';
        $sanitized = hbl_sanitize_input($input, 'url');
        
        $this->assertEquals('https://example.com', $sanitized);
        
        // Invalid URL
        $input = 'not-a-url';
        $sanitized = hbl_sanitize_input($input, 'url');
        
        $this->assertInstanceOf('WP_Error', $sanitized);
        $this->assertEquals('invalid_url', $sanitized->get_error_code());
    }
    
    /**
     * Test sanitize input function with integer type
     */
    public function test_sanitize_input_int() {
        // Valid integer
        $input = '123';
        $sanitized = hbl_sanitize_input($input, 'int');
        
        $this->assertEquals(123, $sanitized);
        
        // String that converts to integer
        $input = '123abc';
        $sanitized = hbl_sanitize_input($input, 'int');
        
        $this->assertEquals(123, $sanitized);
        
        // With min/max constraints
        $input = '50';
        $sanitized = hbl_sanitize_input($input, 'int', array('min' => 10, 'max' => 100));
        
        $this->assertEquals(50, $sanitized);
        
        // Below min constraint
        $input = '5';
        $sanitized = hbl_sanitize_input($input, 'int', array('min' => 10, 'max' => 100));
        
        $this->assertInstanceOf('WP_Error', $sanitized);
        $this->assertEquals('min_value', $sanitized->get_error_code());
        
        // Above max constraint
        $input = '150';
        $sanitized = hbl_sanitize_input($input, 'int', array('min' => 10, 'max' => 100));
        
        $this->assertInstanceOf('WP_Error', $sanitized);
        $this->assertEquals('max_value', $sanitized->get_error_code());
    }
    
    /**
     * Test sanitize input function with float type
     */
    public function test_sanitize_input_float() {
        // Valid float
        $input = '123.45';
        $sanitized = hbl_sanitize_input($input, 'float');
        
        $this->assertEquals(123.45, $sanitized);
        
        // String that converts to float
        $input = '123.45abc';
        $sanitized = hbl_sanitize_input($input, 'float');
        
        $this->assertEquals(123.45, $sanitized);
    }
    
    /**
     * Test sanitize input function with boolean type
     */
    public function test_sanitize_input_bool() {
        // True values
        $this->assertTrue(hbl_sanitize_input('1', 'bool'));
        $this->assertTrue(hbl_sanitize_input('true', 'bool'));
        $this->assertTrue(hbl_sanitize_input('yes', 'bool'));
        $this->assertTrue(hbl_sanitize_input(1, 'bool'));
        $this->assertTrue(hbl_sanitize_input(true, 'bool'));
        
        // False values
        $this->assertFalse(hbl_sanitize_input('0', 'bool'));
        $this->assertFalse(hbl_sanitize_input('false', 'bool'));
        $this->assertFalse(hbl_sanitize_input('no', 'bool'));
        $this->assertFalse(hbl_sanitize_input(0, 'bool'));
        $this->assertFalse(hbl_sanitize_input(false, 'bool'));
        $this->assertFalse(hbl_sanitize_input('', 'bool'));
    }
    
    /**
     * Test sanitize input function with select type
     */
    public function test_sanitize_input_select() {
        // Valid option
        $input = 'option1';
        $sanitized = hbl_sanitize_input($input, 'select', array('allowed_values' => array('option1', 'option2', 'option3')));
        
        $this->assertEquals('option1', $sanitized);
        
        // Invalid option
        $input = 'option4';
        $sanitized = hbl_sanitize_input($input, 'select', array('allowed_values' => array('option1', 'option2', 'option3')));
        
        $this->assertInstanceOf('WP_Error', $sanitized);
        $this->assertEquals('invalid_option', $sanitized->get_error_code());
    }
    
    /**
     * Test sanitize input function with phone type
     */
    public function test_sanitize_input_phone() {
        // Valid phone number
        $input = '+1 (234) 567-8901';
        $sanitized = hbl_sanitize_input($input, 'phone');
        
        $this->assertEquals('+1 (234) 567-8901', $sanitized);
        
        // Phone number with invalid characters
        $input = '+1 (234) 567-8901 ext. 123';
        $sanitized = hbl_sanitize_input($input, 'phone');
        
        $this->assertEquals('+1 (234) 567-8901 123', $sanitized);
    }
    
    /**
     * Test validate form function
     */
    public function test_validate_form() {
        // Valid form data
        $data = array(
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => '30',
            'website' => 'https://example.com',
            'agree' => '1'
        );
        
        $rules = array(
            'name' => array('type' => 'text', 'args' => array('required' => true)),
            'email' => array('type' => 'email', 'args' => array('required' => true)),
            'age' => array('type' => 'int', 'args' => array('min' => 18, 'max' => 100)),
            'website' => array('type' => 'url'),
            'agree' => array('type' => 'bool', 'args' => array('required' => true))
        );
        
        $sanitized = hbl_validate_form($data, $rules);
        
        $this->assertIsArray($sanitized);
        $this->assertEquals('John Doe', $sanitized['name']);
        $this->assertEquals('john@example.com', $sanitized['email']);
        $this->assertEquals(30, $sanitized['age']);
        $this->assertEquals('https://example.com', $sanitized['website']);
        $this->assertTrue($sanitized['agree']);
        
        // Invalid form data
        $data = array(
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'age' => '15',
            'website' => 'not-a-url',
            'agree' => '0'
        );
        
        $sanitized = hbl_validate_form($data, $rules);
        
        $this->assertInstanceOf('WP_Error', $sanitized);
        $this->assertEquals('validation_failed', $sanitized->get_error_code());
        
        $errors = $sanitized->get_error_data();
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertArrayHasKey('website', $errors);
        $this->assertArrayHasKey('agree', $errors);
    }
    
    /**
     * Test rate limiting functions
     */
    public function test_rate_limiting() {
        // Mock the IP address
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        
        // First request should not be rate limited
        $this->assertFalse(hbl_is_rate_limited('test_action', 2, 60));
        
        // Second request should not be rate limited
        $this->assertFalse(hbl_is_rate_limited('test_action', 2, 60));
        
        // Third request should be rate limited
        $this->assertTrue(hbl_is_rate_limited('test_action', 2, 60));
        
        // Clean up
        delete_option('hbl_rate_limits');
    }
}