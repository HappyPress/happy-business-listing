<?php

class SiteCreationTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the site creation functions
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/site-creation.php');
    }

    public function test_hbl_create_business_site() {
        // Mock input data
        $business_data = array(
            'business_name' => 'Test Business',
            'user_email' => 'test@testbusiness.com',
            'domain' => 'testbusiness.com'
        );

        // Mock the WordPress functions for site creation
        add_filter('wpmu_create_blog', function($blog_id, $domain, $path, $site_title, $user_id) {
            return 1; // Return a mock blog ID
        }, 10, 5);

        // Call the function
        $result = hbl_create_business_site($business_data);

        // Assert that the function returns the mock blog ID
        $this->assertEquals(1, $result);
    }

    public function test_hbl_create_business_site_invalid_data() {
        // Test with missing required data
        $business_data = array(
            'business_name' => 'Test Business',
            // Missing user_email and domain
        );

        $result = hbl_create_business_site($business_data);

        // Assert that the function returns false for invalid data
        $this->assertFalse($result);
    }

    public function test_hbl_setup_business_site() {
        // Mock a new site ID
        $blog_id = 1;

        // Call the function
        hbl_setup_business_site($blog_id);

        // Switch to the new blog to check its setup
        switch_to_blog($blog_id);

        // Assert that the necessary plugins are activated
        $this->assertTrue(is_plugin_active('happy-business-listing/happy-business-listing.php'));
        $this->assertTrue(is_plugin_active('advanced-custom-fields/acf.php'));

        // Assert that the business listing post type is registered
        $this->assertTrue(post_type_exists('business_listing'));

        // Assert that the default pages are created
        $home_page = get_page_by_title('Home');
        $this->assertNotNull($home_page);

        $about_page = get_page_by_title('About Us');
        $this->assertNotNull($about_page);

        $contact_page = get_page_by_title('Contact');
        $this->assertNotNull($contact_page);

        // Switch back to the main site
        restore_current_blog();
    }
}