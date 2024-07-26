<?php

class SearchAndFiltersTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Register the custom post type
        register_post_type('business_listing', array('public' => true));
    }

    public function test_search_functionality() {
        // Create some test business listings
        $post_id1 = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business 1',
            'post_content' => 'This is a test business'
        ));
        $post_id2 = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Another Business',
            'post_content' => 'This is another test'
        ));

        // Perform a search
        $query = new WP_Query(array(
            'post_type' => 'business_listing',
            's' => 'test'
        ));

        // Assert that the search returns the correct results
        $this->assertEquals(2, $query->post_count);
        $this->assertTrue(in_array($post_id1, wp_list_pluck($query->posts, 'ID')));
        $this->assertTrue(in_array($post_id2, wp_list_pluck($query->posts, 'ID')));
    }

    public function test_filter_functionality() {
        // Create some test business listings with custom fields
        $post_id1 = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Business A'
        ));
        update_post_meta($post_id1, 'business_category', 'Restaurant');

        $post_id2 = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Business B'
        ));
        update_post_meta($post_id2, 'business_category', 'Retail');

        // Perform a filtered query
        $query = new WP_Query(array(
            'post_type' => 'business_listing',
            'meta_query' => array(
                array(
                    'key' => 'business_category',
                    'value' => 'Restaurant',
                    'compare' => '='
                )
            )
        ));

        // Assert that the filter returns the correct results
        $this->assertEquals(1, $query->post_count);
        $this->assertEquals($post_id1, $query->posts[0]->ID);
    }

    public function test_search_and_filter_combination() {
        // Create some test business listings with custom fields
        $post_id1 = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Restaurant',
            'post_content' => 'This is a test restaurant'
        ));
        update_post_meta($post_id1, 'business_category', 'Restaurant');

        $post_id2 = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Another Test Shop',
            'post_content' => 'This is another test retail shop'
        ));
        update_post_meta($post_id2, 'business_category', 'Retail');

        // Perform a combined search and filter query
        $query = new WP_Query(array(
            'post_type' => 'business_listing',
            's' => 'test',
            'meta_query' => array(
                array(
                    'key' => 'business_category',
                    'value' => 'Restaurant',
                    'compare' => '='
                )
            )
        ));

        // Assert that the combined search and filter returns the correct results
        $this->assertEquals(1, $query->post_count);
        $this->assertEquals($post_id1, $query->posts[0]->ID);
    }
}