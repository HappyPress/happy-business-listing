<?php
/**
 * Class UserRegistrationTest
 *
 * @package Happy_Business_Listing
 */

/**
 * User registration test case.
 */
class UserRegistrationTest extends WP_UnitTestCase {
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Make sure the user registration functions are loaded
        require_once HBL_PLUGIN_DIR . 'includes/user-registration.php';
    }
    
    /**
     * Test user creation from business listing
     */
    public function test_user_creation_from_business_listing() {
        // Create a test business
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_status' => 'publish',
            'meta_input' => array(
                'business_name' => 'Test Business',
                'email' => 'test@example.com',
                'whatsapp_number' => '+1234567890'
            )
        ));
        
        // Get the post object
        $post = get_post($post_id);
        
        // Call the user creation function
        $user_id = hbl_create_user_and_site($post_id, $post, false);
        
        // Check if a user was created
        $this->assertIsInt($user_id);
        $this->assertGreaterThan(0, $user_id);
        
        // Check if the user has the correct data
        $user = get_userdata($user_id);
        
        $this->assertEquals('test@example.com', $user->user_email);
        $this->assertEquals('Test Business', $user->display_name);
        $this->assertEquals('business_user', $user->roles[0]);
        
        // Check if the user is associated with the business
        $this->assertEquals($user_id, get_post_meta($post_id, 'user_id', true));
        $this->assertEquals($post_id, get_user_meta($user_id, 'user_business_id', true));
        $this->assertEquals('+1234567890', get_user_meta($user_id, 'whatsapp_number', true));
    }
    
    /**
     * Test business user capabilities
     */
    public function test_business_user_capabilities() {
        // Create a business user
        $user_id = $this->factory->user->create(array(
            'role' => 'business_user'
        ));
        
        // Get the user
        $user = get_userdata($user_id);
        
        // Check basic capabilities
        $this->assertTrue($user->has_cap('read'));
        $this->assertTrue($user->has_cap('edit_posts'));
        $this->assertTrue($user->has_cap('delete_posts'));
        $this->assertTrue($user->has_cap('publish_posts'));
        $this->assertTrue($user->has_cap('upload_files'));
        
        // Check service_product capabilities
        $this->assertTrue($user->has_cap('edit_service_product'));
        $this->assertTrue($user->has_cap('read_service_product'));
        $this->assertTrue($user->has_cap('delete_service_product'));
        $this->assertTrue($user->has_cap('edit_service_products'));
        $this->assertTrue($user->has_cap('edit_published_service_products'));
        $this->assertTrue($user->has_cap('publish_service_products'));
        $this->assertTrue($user->has_cap('delete_published_service_products'));
        
        // Check lead capabilities
        $this->assertTrue($user->has_cap('read_lead'));
        $this->assertTrue($user->has_cap('edit_lead'));
        $this->assertTrue($user->has_cap('delete_lead'));
        $this->assertTrue($user->has_cap('edit_leads'));
        $this->assertTrue($user->has_cap('edit_published_leads'));
        $this->assertTrue($user->has_cap('publish_leads'));
        $this->assertTrue($user->has_cap('delete_published_leads'));
    }
    
    /**
     * Test content filtering for business users
     */
    public function test_content_filtering_for_business_users() {
        // Create a business user
        $user_id = $this->factory->user->create(array(
            'role' => 'business_user'
        ));
        
        // Create a business for the user
        $business_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'User Business',
            'post_status' => 'publish'
        ));
        
        // Associate user with business
        update_post_meta($business_id, 'user_id', $user_id);
        update_user_meta($user_id, 'user_business_id', $business_id);
        
        // Create a service for the business
        $service_id = $this->factory->post->create(array(
            'post_type' => 'service_product',
            'post_title' => 'User Service',
            'post_status' => 'publish',
            'meta_input' => array(
                'business_id' => $business_id
            )
        ));
        
        // Create another business
        $other_business_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Other Business',
            'post_status' => 'publish'
        ));
        
        // Create a service for the other business
        $other_service_id = $this->factory->post->create(array(
            'post_type' => 'service_product',
            'post_title' => 'Other Service',
            'post_status' => 'publish',
            'meta_input' => array(
                'business_id' => $other_business_id
            )
        ));
        
        // Set current user
        wp_set_current_user($user_id);
        
        // Mock the admin screen
        global $pagenow, $typenow;
        $pagenow = 'edit.php';
        $typenow = 'service_product';
        
        // Create a query
        $query = new WP_Query(array(
            'post_type' => 'service_product',
            'posts_per_page' => -1
        ));
        
        // Apply the filter
        hbl_filter_business_user_content($query);
        
        // Check if the query has been modified
        $this->assertEquals('business_id', $query->get('meta_key'));
        $this->assertEquals($business_id, $query->get('meta_value'));
        
        // Run the query
        $query->get_posts();
        
        // Check if only the user's service is returned
        $this->assertEquals(1, $query->post_count);
        $this->assertEquals($service_id, $query->posts[0]->ID);
    }
}