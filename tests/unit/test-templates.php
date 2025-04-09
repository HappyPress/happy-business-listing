<?php
/**
 * Class TemplatesTest
 *
 * @package Happy_Business_Listing
 */

/**
 * Templates functions test case.
 */
class TemplatesTest extends WP_UnitTestCase {
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Make sure the templates functions are loaded
        require_once HBL_PLUGIN_DIR . 'includes/templates.php';
    }
    
    /**
     * Test template loading function
     */
    public function test_load_plugin_templates() {
        // Mock a business listing post
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Set the current post
        global $post;
        $post = get_post($post_id);
        
        // Mock is_singular function
        $this->assertTrue(is_singular('business_listing'));
        
        // Test template loading
        $template = hbl_load_plugin_templates('default-template.php');
        
        // The template should be the plugin's single business listing template
        $this->assertEquals(HBL_PLUGIN_DIR . 'templates/single-business_listing.php', $template);
    }
    
    /**
     * Test business field function
     */
    public function test_get_business_field() {
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Add some meta data
        update_post_meta($post_id, 'business_name', 'Test Business');
        update_post_meta($post_id, 'company_type', 'Pvt Ltd');
        update_post_meta($post_id, 'location', 'Test Location');
        
        // Test getting fields
        $this->assertEquals('Test Business', hbl_get_business_field('business_name', $post_id));
        $this->assertEquals('Pvt Ltd', hbl_get_business_field('company_type', $post_id));
        $this->assertEquals('Test Location', hbl_get_business_field('location', $post_id));
        
        // Test default value for non-existent field
        $this->assertEquals('', hbl_get_business_field('non_existent_field', $post_id));
        $this->assertEquals('Default Value', hbl_get_business_field('non_existent_field', $post_id, 'Default Value'));
    }
    
    /**
     * Test social media links function
     */
    public function test_get_social_media_links() {
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Add social media data
        update_post_meta($post_id, 'social_media_handles', json_encode(array(
            'facebook' => 'https://facebook.com/testbusiness',
            'twitter' => 'https://twitter.com/testbusiness',
            'instagram' => 'https://instagram.com/testbusiness'
        )));
        
        // Test getting social media links
        $links = hbl_get_social_media_links($post_id);
        
        // Should contain the social media links
        $this->assertStringContainsString('https://facebook.com/testbusiness', $links);
        $this->assertStringContainsString('https://twitter.com/testbusiness', $links);
        $this->assertStringContainsString('https://instagram.com/testbusiness', $links);
        
        // Test with show_labels option
        $links = hbl_get_social_media_links($post_id, array('show_labels' => true));
        
        // Should contain the social media labels
        $this->assertStringContainsString('Facebook', $links);
        $this->assertStringContainsString('Twitter', $links);
        $this->assertStringContainsString('Instagram', $links);
    }
    
    /**
     * Test contact info function
     */
    public function test_get_contact_info() {
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Add contact info data
        update_post_meta($post_id, 'phone', '+1234567890');
        update_post_meta($post_id, 'business_email', 'test@example.com');
        update_post_meta($post_id, 'website', 'https://example.com');
        update_post_meta($post_id, 'whatsapp_number', '+1234567890');
        update_post_meta($post_id, 'location', 'Test Location');
        
        // Test getting contact info
        $info = hbl_get_contact_info($post_id);
        
        // Should contain the contact info
        $this->assertStringContainsString('+1234567890', $info);
        $this->assertStringContainsString('test@example.com', $info);
        $this->assertStringContainsString('https://example.com', $info);
        $this->assertStringContainsString('Test Location', $info);
        
        // Test with show_labels option
        $info = hbl_get_contact_info($post_id, array('show_labels' => true));
        
        // Should contain the labels
        $this->assertStringContainsString('Phone:', $info);
        $this->assertStringContainsString('Email:', $info);
        $this->assertStringContainsString('Website:', $info);
        $this->assertStringContainsString('WhatsApp:', $info);
        $this->assertStringContainsString('Location:', $info);
        
        // Test with selective display
        $info = hbl_get_contact_info($post_id, array(
            'show_phone' => true,
            'show_email' => true,
            'show_website' => false,
            'show_whatsapp' => false,
            'show_location' => false
        ));
        
        // Should contain only phone and email
        $this->assertStringContainsString('+1234567890', $info);
        $this->assertStringContainsString('test@example.com', $info);
        $this->assertStringNotContainsString('https://example.com', $info);
        $this->assertStringNotContainsString('Test Location', $info);
    }
    
    /**
     * Test business details function
     */
    public function test_get_business_details() {
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Add business details data
        update_post_meta($post_id, 'company_type', 'Pvt Ltd');
        update_post_meta($post_id, 'gst_no', 'GST12345678');
        update_post_meta($post_id, 'tan_pan', 'TAN12345678');
        update_post_meta($post_id, 'verification_status', 'verified');
        
        // Test getting business details
        $details = hbl_get_business_details($post_id);
        
        // Should contain the business details
        $this->assertStringContainsString('Pvt Ltd', $details);
        $this->assertStringContainsString('GST12345678', $details);
        $this->assertStringContainsString('TAN12345678', $details);
        $this->assertStringContainsString('verified', $details);
        
        // Test with custom fields
        $details = hbl_get_business_details($post_id, array(
            'custom_fields' => array(
                'established_year' => 'Established',
                'business_hours' => 'Business Hours'
            )
        ));
        
        // Should contain the custom field labels
        $this->assertStringContainsString('Established', $details);
        $this->assertStringContainsString('Business Hours', $details);
    }
    
    /**
     * Test business image function
     */
    public function test_get_business_image() {
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish'
        ));
        
        // Test getting business image without featured image
        $image = hbl_get_business_image($post_id);
        
        // Should contain the default image
        $this->assertStringContainsString('default-logo', $image);
        
        // Test with custom default image
        $image = hbl_get_business_image($post_id, 'medium', array(
            'default_image' => 'https://example.com/custom-default.png'
        ));
        
        // Should contain the custom default image
        $this->assertStringContainsString('https://example.com/custom-default.png', $image);
    }
}