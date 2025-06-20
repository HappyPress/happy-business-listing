<?php
/**
 * Class HelpersTest
 *
 * @package Happy_Business_Listing
 */

/**
 * Helpers functions test case.
 */
class HelpersTest extends WP_UnitTestCase {
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Make sure the helpers functions are loaded
        require_once HBL_PLUGIN_DIR . 'includes/helpers.php';
    }
    
    /**
     * Test format price function
     */
    public function test_format_price() {
        // Test with default currency (USD)
        $this->assertEquals('$100.00', hbl_format_price(100));
        $this->assertEquals('$1,234.56', hbl_format_price(1234.56));
        
        // Test with specific currencies
        $this->assertEquals('₹100.00', hbl_format_price(100, 'INR'));
        $this->assertEquals('€100.00', hbl_format_price(100, 'EUR'));
        $this->assertEquals('£100.00', hbl_format_price(100, 'GBP'));
        $this->assertEquals('¥100', hbl_format_price(100, 'JPY'));
    }
    
    /**
     * Test get field function
     */
    public function test_get_field() {
        // Create a test post
        $post_id = $this->factory->post->create(array(
            'post_title' => 'Test Post',
            'post_status' => 'publish'
        ));
        
        // Add some meta data
        update_post_meta($post_id, 'test_field', 'Test Value');
        
        // Test getting field
        $this->assertEquals('Test Value', hbl_get_field('test_field', $post_id));
        
        // Test default value for non-existent field
        $this->assertEquals('', hbl_get_field('non_existent_field', $post_id));
        $this->assertEquals('Default Value', hbl_get_field('non_existent_field', $post_id, 'Default Value'));
    }
    
    /**
     * Test update field function
     */
    public function test_update_field() {
        // Create a test post
        $post_id = $this->factory->post->create(array(
            'post_title' => 'Test Post',
            'post_status' => 'publish'
        ));
        
        // Update field
        $this->assertTrue(hbl_update_field('test_field', 'Test Value', $post_id));
        
        // Verify field was updated
        $this->assertEquals('Test Value', get_post_meta($post_id, 'test_field', true));
    }
    
    /**
     * Test sanitize phone function
     */
    public function test_sanitize_phone() {
        // Test with valid phone number
        $this->assertEquals('+1-234-567-8901', hbl_sanitize_phone('+1-234-567-8901'));
        
        // Test with phone number containing invalid characters
        $this->assertEquals('+1-234-567-8901', hbl_sanitize_phone('+1-234-567-8901 ext. 123'));
    }
    
    /**
     * Test is valid URL function
     */
    public function test_is_valid_url() {
        // Test with valid URLs
        $this->assertTrue(hbl_is_valid_url('https://example.com'));
        $this->assertTrue(hbl_is_valid_url('http://example.com'));
        $this->assertTrue(hbl_is_valid_url('https://example.com/path/to/page?query=string#hash'));
        
        // Test with invalid URLs
        $this->assertFalse(hbl_is_valid_url('not-a-url'));
        $this->assertFalse(hbl_is_valid_url('example.com'));
        $this->assertFalse(hbl_is_valid_url('ftp://example.com'));
    }
    
    /**
     * Test get asset URL function
     */
    public function test_get_asset_url() {
        // Test with relative path
        $this->assertEquals(HBL_PLUGIN_URL . 'assets/css/style.css', hbl_get_asset_url('css/style.css'));
        
        // Test with path starting with slash
        $this->assertEquals(HBL_PLUGIN_URL . 'assets/js/script.js', hbl_get_asset_url('/js/script.js'));
    }
    
    /**
     * Test get default logo URL function
     */
    public function test_get_default_logo_url() {
        $this->assertEquals(HBL_PLUGIN_URL . 'assets/img/default-logo.svg', hbl_get_default_logo_url());
    }
    
    /**
     * Test user owns business function
     */
    public function test_user_owns_business() {
        // Create a test user
        $user_id = $this->factory->user->create(array(
            'role' => 'business_user'
        ));
        
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Associate user with business
        update_post_meta($post_id, 'user_id', $user_id);
        update_user_meta($user_id, 'user_business_id', $post_id);
        
        // Set current user
        wp_set_current_user($user_id);
        
        // Test user owns business
        $this->assertTrue(hbl_user_owns_business($post_id));
        
        // Test with different business
        $other_post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Other Business',
            'post_status' => 'publish'
        ));
        
        $this->assertFalse(hbl_user_owns_business($other_post_id));
        
        // Test with admin user
        $admin_id = $this->factory->user->create(array(
            'role' => 'administrator'
        ));
        
        wp_set_current_user($admin_id);
        
        // Admin should be able to edit any business
        $this->assertTrue(hbl_user_owns_business($post_id));
        $this->assertTrue(hbl_user_owns_business($other_post_id));
    }
}