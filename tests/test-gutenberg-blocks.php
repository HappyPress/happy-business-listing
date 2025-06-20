<?php

class GutenbergBlocksTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the Gutenberg block registration
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/gutenberg-blocks.php');
    }

    public function test_blocks_are_registered() {
        // Assuming you have a function named hbl_register_blocks() that registers your blocks
        hbl_register_blocks();

        // Test if the blocks are registered
        $this->assertTrue(WP_Block_Type_Registry::get_instance()->is_registered('happy-business-listing/business-card'));
        $this->assertTrue(WP_Block_Type_Registry::get_instance()->is_registered('happy-business-listing/business-list'));
    }

    public function test_business_card_block_renders() {
        // Create a test business listing
        $post_id = $this->factory->post->create([
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
        ]);

        // Set some test meta data
        update_post_meta($post_id, 'business_name', 'Test Business');
        update_post_meta($post_id, 'location', 'Test City');
        update_post_meta($post_id, 'whatsapp_number', '1234567890');

        // Render the block
        $block_content = render_block([
            'blockName' => 'happy-business-listing/business-card',
            'attrs' => ['businessId' => $post_id],
        ]);

        // Assert that the rendered content contains expected information
        $this->assertStringContainsString('Test Business', $block_content);
        $this->assertStringContainsString('Test City', $block_content);
        $this->assertStringContainsString('1234567890', $block_content);
    }

    public function test_business_list_block_renders() {
        // Create multiple test business listings
        $post_ids = $this->factory->post->create_many(3, [
            'post_type' => 'business_listing',
        ]);

        // Render the block
        $block_content = render_block([
            'blockName' => 'happy-business-listing/business-list',
            'attrs' => ['numberOfListings' => 3],
        ]);

        // Assert that the rendered content contains multiple business listings
        foreach ($post_ids as $post_id) {
            $this->assertStringContainsString(get_the_title($post_id), $block_content);
        }
    }
}