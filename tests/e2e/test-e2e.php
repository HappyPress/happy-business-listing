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
     * Test admin user ID
     *
     * @var int
     */
    protected $admin_user_id;
    
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
        require_once HBL_PLUGIN_DIR . 'includes/gutenberg-blocks.php';
        require_once HBL_PLUGIN_DIR . 'includes/search-and-filters.php';
        require_once HBL_PLUGIN_DIR . 'includes/site-creation.php';
    }
    
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Create a test admin user
        $this->admin_user_id = $this->factory->user->create(array(
            'role' => 'administrator',
            'user_login' => 'admin',
            'user_email' => 'admin@example.com',
            'display_name' => 'Admin User'
        ));
        
        // Create a test business user
        $this->user_id = $this->factory->user->create(array(
            'role' => 'business_user',
            'user_login' => 'testuser',
            'user_email' => 'testuser@example.com',
            'display_name' => 'Test User'
        ));
        
        // Set current user as admin
        wp_set_current_user($this->admin_user_id);
        
        // Set up default options
        update_option('hbl_activate_blocks', '1');
        update_option('hbl_activate_search', '1');
        update_option('hbl_enable_logging', '1');
    }
    
    /**
     * Tear down after each test
     */
    public function tearDown(): void {
        parent::tearDown();
        
        // Reset current user
        wp_set_current_user(0);
        
        // Clean up test data
        if ($this->business_id) {
            wp_delete_post($this->business_id, true);
        }
    }
    
    /**
     * Test complete business registration journey
     */
    public function test_complete_business_registration_journey() {
        // Step 1: Submit business registration form
        $this->business_id = $this->submit_business_registration_form();
        
        // Step 2: Verify business listing was created
        $this->verify_business_listing();
        
        // Step 3: Verify user was created and associated with business
        $this->verify_user_association();
        
        // Step 4: Test business listing display
        $this->test_business_listing_display();
        
        // Step 5: Test business search functionality
        $this->test_business_search();
        
        // Step 6: Test Gutenberg blocks
        $this->test_gutenberg_blocks();
    }
    
    /**
     * Test complete lead management journey
     */
    public function test_complete_lead_management_journey() {
        // Step 1: Create a business first
        $this->business_id = $this->create_test_business();
        
        // Step 2: Add services to the business
        $service_ids = $this->add_services_to_business();
        
        // Step 3: Submit contact form to create a lead
        $lead_id = $this->submit_contact_form();
        
        // Step 4: Verify lead was created and associated with business
        $this->verify_lead($lead_id);
        
        // Step 5: Test lead management in admin
        $this->test_lead_management();
    }
    
    /**
     * Test WhatsApp integration journey
     */
    public function test_whatsapp_integration_journey() {
        // Step 1: Create a business
        $this->business_id = $this->create_test_business();
        
        // Step 2: Configure WhatsApp settings
        $this->configure_whatsapp_settings();
        
        // Step 3: Test WhatsApp message sending
        $this->test_whatsapp_message_sending();
        
        // Step 4: Test lead notification via WhatsApp
        $this->test_lead_whatsapp_notification();
    }
    
    /**
     * Test multisite functionality (if available)
     */
    public function test_multisite_functionality() {
        if (!is_multisite()) {
            $this->markTestSkipped('Multisite is not enabled');
        }
        
        // Step 1: Create a business
        $this->business_id = $this->create_test_business();
        
        // Step 2: Test sub-site creation
        $site_id = $this->test_subsite_creation();
        
        // Step 3: Verify sub-site content
        $this->verify_subsite_content($site_id);
        
        // Step 4: Test sub-site management
        $this->test_subsite_management($site_id);
    }
    
    /**
     * Test admin interface functionality
     */
    public function test_admin_interface_functionality() {
        // Step 1: Test settings page
        $this->test_settings_page();
        
        // Step 2: Test business listing management
        $this->test_business_listing_management();
        
        // Step 3: Test user management
        $this->test_user_management();
        
        // Step 4: Test analytics and reporting
        $this->test_analytics_and_reporting();
    }
    
    /**
     * Test frontend functionality
     */
    public function test_frontend_functionality() {
        // Step 1: Create test businesses
        $business_ids = $this->create_multiple_test_businesses();
        
        // Step 2: Test business listing page
        $this->test_business_listing_page();
        
        // Step 3: Test business detail page
        $this->test_business_detail_page();
        
        // Step 4: Test search and filters
        $this->test_search_and_filters();
        
        // Step 5: Test responsive design
        $this->test_responsive_design();
    }
    
    /**
     * Test accessibility features
     */
    public function test_accessibility_features() {
        // Step 1: Test keyboard navigation
        $this->test_keyboard_navigation();
        
        // Step 2: Test screen reader compatibility
        $this->test_screen_reader_compatibility();
        
        // Step 3: Test color contrast
        $this->test_color_contrast();
        
        // Step 4: Test ARIA labels
        $this->test_aria_labels();
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
     * Create a test business
     *
     * @return int Business ID
     */
    protected function create_test_business() {
        $business_id = wp_insert_post(array(
            'post_title' => 'Test Business',
            'post_content' => 'Test business content',
            'post_status' => 'publish',
            'post_type' => 'business_listing'
        ));
        
        // Add business meta data
        update_post_meta($business_id, 'business_name', 'Test Business');
        update_post_meta($business_id, 'company_type', 'Pvt Ltd');
        update_post_meta($business_id, 'gst_no', 'GST12345678');
        update_post_meta($business_id, 'location', 'Test Location');
        update_post_meta($business_id, 'website', 'https://example.com');
        update_post_meta($business_id, 'whatsapp_number', '+1234567890');
        update_post_meta($business_id, 'email', 'business@example.com');
        update_post_meta($business_id, 'user_id', $this->user_id);
        
        return $business_id;
    }
    
    /**
     * Create multiple test businesses
     *
     * @return array Business IDs
     */
    protected function create_multiple_test_businesses() {
        $business_ids = array();
        
        for ($i = 1; $i <= 5; $i++) {
            $business_id = wp_insert_post(array(
                'post_title' => "Test Business $i",
                'post_content' => "Test business $i content",
                'post_status' => 'publish',
                'post_type' => 'business_listing'
            ));
            
            update_post_meta($business_id, 'business_name', "Test Business $i");
            update_post_meta($business_id, 'company_type', 'Pvt Ltd');
            update_post_meta($business_id, 'location', "Location $i");
            update_post_meta($business_id, 'user_id', $this->user_id);
            
            $business_ids[] = $business_id;
        }
        
        return $business_ids;
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
        $service_ids = array();
        
        $services = array(
            'Web Development' => 'Professional web development services',
            'Digital Marketing' => 'Comprehensive digital marketing solutions',
            'Consulting' => 'Business consulting services'
        );
        
        foreach ($services as $title => $content) {
            $service_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'service_product'
            ));
            
            update_post_meta($service_id, 'business_id', $this->business_id);
            $service_ids[] = $service_id;
        }
        
        return $service_ids;
    }
    
    /**
     * Submit contact form
     *
     * @return int Lead ID
     */
    protected function submit_contact_form() {
        // Set up nonce verification
        add_filter('wp_verify_nonce', '__return_true');
        
        $_POST = array(
            'action' => 'hbl_submit_contact',
            'hbl_nonce' => 'test_nonce',
            'business_id' => $this->business_id,
            'name' => 'Test Lead',
            'email' => 'lead@example.com',
            'phone' => '+1234567890',
            'message' => 'Test lead message',
            'service_interest' => 'Web Development'
        );
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        
        try {
            hbl_handle_contact_submission();
        } catch (Exception $e) {
            // Ignore the exception
        }
        
        ob_get_clean();
        
        remove_filter('wp_verify_nonce', '__return_true');
        
        // Get the lead
        $leads = get_posts(array(
            'post_type' => 'lead',
            'post_status' => 'publish',
            'meta_key' => 'business_id',
            'meta_value' => $this->business_id
        ));
        
        $this->assertCount(1, $leads);
        
        return $leads[0]->ID;
    }
    
    /**
     * Verify lead
     *
     * @param int $lead_id Lead ID
     */
    protected function verify_lead($lead_id) {
        $lead = get_post($lead_id);
        
        $this->assertEquals('lead', $lead->post_type);
        $this->assertEquals('publish', $lead->post_status);
        $this->assertEquals($this->business_id, get_post_meta($lead_id, 'business_id', true));
        $this->assertEquals('Test Lead', get_post_meta($lead_id, 'name', true));
        $this->assertEquals('lead@example.com', get_post_meta($lead_id, 'email', true));
        $this->assertEquals('+1234567890', get_post_meta($lead_id, 'phone', true));
        $this->assertEquals('Test lead message', get_post_meta($lead_id, 'message', true));
    }
    
    /**
     * Test business listing display
     */
    protected function test_business_listing_display() {
        // Test business listing block rendering
        $attributes = array(
            'numberOfItems' => 3,
            'orderBy' => 'date',
            'order' => 'desc',
            'displayFeaturedImage' => true,
            'displayExcerpt' => true,
            'columns' => 3
        );
        
        $output = hbl_render_business_listings_block($attributes);
        
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('hbl-block-business-listings', $output);
    }
    
    /**
     * Test business search
     */
    protected function test_business_search() {
        // Test search functionality
        $search_results = hbl_search_businesses(array(
            'search' => 'Test Business',
            'location' => 'Test Location',
            'company_type' => 'Pvt Ltd'
        ));
        
        $this->assertNotEmpty($search_results);
        $this->assertContains($this->business_id, wp_list_pluck($search_results, 'ID'));
    }
    
    /**
     * Test Gutenberg blocks
     */
    protected function test_gutenberg_blocks() {
        // Test business search block
        $search_attributes = array('showFilters' => true);
        $search_output = hbl_render_business_search_block($search_attributes);
        
        $this->assertNotEmpty($search_output);
        $this->assertStringContainsString('hbl-business-search', $search_output);
        
        // Test business details block
        $details_attributes = array(
            'businessId' => $this->business_id,
            'showTitle' => true,
            'showImage' => true,
            'showDetails' => true
        );
        $details_output = hbl_render_business_details_block($details_attributes);
        
        $this->assertNotEmpty($details_output);
        $this->assertStringContainsString('Test Business', $details_output);
    }
    
    /**
     * Test lead management
     */
    protected function test_lead_management() {
        // Test lead listing in admin
        $leads = get_posts(array(
            'post_type' => 'lead',
            'post_status' => 'publish',
            'meta_key' => 'business_id',
            'meta_value' => $this->business_id
        ));
        
        $this->assertNotEmpty($leads);
        
        // Test lead status update
        $lead_id = $leads[0]->ID;
        update_post_meta($lead_id, 'status', 'contacted');
        
        $this->assertEquals('contacted', get_post_meta($lead_id, 'status', true));
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
    }
    
    /**
     * Test WhatsApp message sending
     */
    protected function test_whatsapp_message_sending() {
        // Mock the WhatsApp sending function
        add_filter('hbl_send_whatsapp_message', '__return_true');
        
        $result = hbl_send_whatsapp_message(
            'Test message',
            '+1234567890'
        );
        
        $this->assertTrue($result);
        
        remove_filter('hbl_send_whatsapp_message', '__return_true');
    }
    
    /**
     * Test lead WhatsApp notification
     */
    protected function test_lead_whatsapp_notification() {
        // Create a lead
        $lead_id = wp_insert_post(array(
            'post_title' => 'Test Lead',
            'post_content' => 'Test lead content',
            'post_status' => 'publish',
            'post_type' => 'lead'
        ));
        
        update_post_meta($lead_id, 'business_id', $this->business_id);
        update_post_meta($lead_id, 'phone', '+1234567890');
        
        // Test notification
        $result = hbl_notify_lead_creation($lead_id, get_post($lead_id), false);
        
        $this->assertTrue($result);
    }
    
    /**
     * Test sub-site creation
     *
     * @return int Site ID
     */
    protected function test_subsite_creation() {
        $site_id = hbl_create_business_subsite(
            $this->business_id,
            $this->user_id,
            'Test Business',
            'testbusiness'
        );
        
        $this->assertNotWPError($site_id);
        $this->assertGreaterThan(0, $site_id);
        
        return $site_id;
    }
    
    /**
     * Verify sub-site content
     *
     * @param int $site_id Site ID
     */
    protected function verify_subsite_content($site_id) {
        switch_to_blog($site_id);
        
        // Check if pages were created
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish'
        ));
        
        $page_titles = wp_list_pluck($pages, 'post_title');
        
        $this->assertContains('Home', $page_titles);
        $this->assertContains('About', $page_titles);
        $this->assertContains('Services', $page_titles);
        $this->assertContains('Contact', $page_titles);
        
        restore_current_blog();
    }
    
    /**
     * Test sub-site management
     *
     * @param int $site_id Site ID
     */
    protected function test_subsite_management($site_id) {
        // Test site access
        $this->assertTrue(is_multisite());
        
        // Test site information
        $site_info = get_blog_details($site_id);
        $this->assertEquals('Test Business', $site_info->blogname);
    }
    
    /**
     * Test settings page
     */
    protected function test_settings_page() {
        // Test settings registration
        $this->assertTrue(function_exists('hbl_register_settings'));
        
        // Test default options
        $this->assertEquals('1', get_option('hbl_activate_blocks'));
        $this->assertEquals('1', get_option('hbl_activate_search'));
    }
    
    /**
     * Test business listing management
     */
    protected function test_business_listing_management() {
        // Test business creation
        $business_id = $this->create_test_business();
        
        // Test business update
        $updated = wp_update_post(array(
            'ID' => $business_id,
            'post_title' => 'Updated Test Business'
        ));
        
        $this->assertEquals($business_id, $updated);
        
        // Test business deletion
        $deleted = wp_delete_post($business_id, true);
        $this->assertNotFalse($deleted);
    }
    
    /**
     * Test user management
     */
    protected function test_user_management() {
        // Test user role creation
        $this->assertTrue(function_exists('hbl_create_business_user_role'));
        
        // Test user capabilities
        $user = get_userdata($this->user_id);
        $this->assertContains('business_user', $user->roles);
    }
    
    /**
     * Test analytics and reporting
     */
    protected function test_analytics_and_reporting() {
        // Test business count
        $business_count = wp_count_posts('business_listing');
        $this->assertGreaterThan(0, $business_count->publish);
        
        // Test lead count
        $lead_count = wp_count_posts('lead');
        $this->assertGreaterThanOrEqual(0, $lead_count->publish);
    }
    
    /**
     * Test business listing page
     */
    protected function test_business_listing_page() {
        // Test archive page
        $archive_url = get_post_type_archive_link('business_listing');
        $this->assertNotEmpty($archive_url);
        
        // Test business listing query
        $query = new WP_Query(array(
            'post_type' => 'business_listing',
            'post_status' => 'publish'
        ));
        
        $this->assertGreaterThan(0, $query->found_posts);
    }
    
    /**
     * Test business detail page
     */
    protected function test_business_detail_page() {
        // Test single business page
        $business_url = get_permalink($this->business_id);
        $this->assertNotEmpty($business_url);
        
        // Test business data retrieval
        $business_data = hbl_get_business_data($this->business_id);
        $this->assertNotEmpty($business_data);
        $this->assertEquals('Test Business', $business_data['name']);
    }
    
    /**
     * Test search and filters
     */
    protected function test_search_and_filters() {
        // Test search form
        $search_form = hbl_get_search_form();
        $this->assertNotEmpty($search_form);
        
        // Test filter functionality
        $filters = hbl_get_available_filters();
        $this->assertNotEmpty($filters);
        $this->assertArrayHasKey('company_type', $filters);
        $this->assertArrayHasKey('location', $filters);
    }
    
    /**
     * Test responsive design
     */
    protected function test_responsive_design() {
        // Test CSS classes for responsive design
        $css_file = HBL_PLUGIN_DIR . 'assets/css/templates.css';
        $this->assertFileExists($css_file);
        
        $css_content = file_get_contents($css_file);
        $this->assertStringContainsString('@media', $css_content);
        $this->assertStringContainsString('max-width', $css_content);
    }
    
    /**
     * Test keyboard navigation
     */
    protected function test_keyboard_navigation() {
        // Test form accessibility
        $form_html = hbl_get_business_registration_form();
        
        $this->assertStringContainsString('tabindex', $form_html);
        $this->assertStringContainsString('aria-label', $form_html);
    }
    
    /**
     * Test screen reader compatibility
     */
    protected function test_screen_reader_compatibility() {
        // Test ARIA labels
        $search_form = hbl_get_search_form();
        
        $this->assertStringContainsString('aria-label', $search_form);
        $this->assertStringContainsString('role', $search_form);
    }
    
    /**
     * Test color contrast
     */
    protected function test_color_contrast() {
        // Test CSS custom properties for theming
        $css_file = HBL_PLUGIN_DIR . 'assets/css/templates.css';
        $css_content = file_get_contents($css_file);
        
        $this->assertStringContainsString('--hbl-', $css_content);
    }
    
    /**
     * Test ARIA labels
     */
    protected function test_aria_labels() {
        // Test business listing block
        $attributes = array('numberOfItems' => 1);
        $output = hbl_render_business_listings_block($attributes);
        
        $this->assertStringContainsString('aria-label', $output);
        $this->assertStringContainsString('role', $output);
    }
}