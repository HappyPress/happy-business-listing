<?php

class CustomPostTypesTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the custom post type registration
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/custom-post-types.php');
    }

    public function test_business_listing_post_type_exists() {
        $this->assertTrue(post_type_exists('business_listing'));
    }

    public function test_business_listing_post_type_labels() {
        $post_type = get_post_type_object('business_listing');
        $this->assertEquals('Business Listings', $post_type->labels->name);
        $this->assertEquals('Business Listing', $post_type->labels->singular_name);
    }

    public function test_business_listing_post_type_supports() {
        $post_type = get_post_type_object('business_listing');
        $this->assertTrue(post_type_supports('business_listing', 'title'));
        $this->assertTrue(post_type_supports('business_listing', 'editor'));
        $this->assertTrue(post_type_supports('business_listing', 'thumbnail'));
    }

    public function test_business_listing_post_type_public() {
        $post_type = get_post_type_object('business_listing');
        $this->assertTrue($post_type->public);
    }

    public function test_business_listing_post_type_has_archive() {
        $post_type = get_post_type_object('business_listing');
        $this->assertTrue($post_type->has_archive);
    }

    public function test_business_listing_post_type_rewrite() {
        $post_type = get_post_type_object('business_listing');
        $this->assertEquals('businesses', $post_type->rewrite['slug']);
    }
}