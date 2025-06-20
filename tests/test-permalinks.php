<?php

class PermalinksTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Register the custom post type
        register_post_type('business_listing', array('public' => true));
    }

    public function test_single_permalink_structure() {
        $post_id = $this->factory->post->create(array('post_type' => 'business_listing'));
        update_option('hbl_single_permalink_structure', 'business/%postname%');

        $permalink = get_permalink($post_id);
        $this->assertStringContainsString('business/', $permalink);
    }

    public function test_archive_permalink_structure() {
        update_option('hbl_archive_permalink_structure', 'businesses');

        $archive_url = get_post_type_archive_link('business_listing');
        $this->assertStringContainsString('businesses', $archive_url);
    }

    public function test_custom_permalink_filter() {
        $post_id = $this->factory->post->create(array('post_type' => 'business_listing'));
        update_option('hbl_single_permalink_structure', 'custom-business/%postname%');

        $permalink = get_permalink($post_id);
        $this->assertStringContainsString('custom-business/', $permalink);
    }

    public function test_custom_archive_permalink_filter() {
        update_option('hbl_archive_permalink_structure', 'custom-businesses');

        $archive_url = get_post_type_archive_link('business_listing');
        $this->assertStringContainsString('custom-businesses', $archive_url);
    }
}