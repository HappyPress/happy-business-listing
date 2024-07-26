<?php

class TemplatesTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Register the custom post type
        register_post_type('business_listing', array('public' => true));
    }

    public function test_single_template_exists() {
        $template_path = plugin_dir_path(dirname(__FILE__)) . 'templates/single-business_listing.php';
        $this->assertFileExists($template_path);
    }

    public function test_archive_template_exists() {
        $template_path = plugin_dir_path(dirname(__FILE__)) . 'templates/archive-business_listing.php';
        $this->assertFileExists($template_path);
    }

    public function test_single_template_filter() {
        $post_id = $this->factory->post->create(array('post_type' => 'business_listing'));
        $template = get_single_template();
        $expected_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-business_listing.php';
        $this->assertEquals($expected_template, $template);
    }

    public function test_archive_template_filter() {
        $template = get_archive_template();
        $expected_template = plugin_dir_path(dirname(__FILE__)) . 'templates/archive-business_listing.php';
        $this->assertEquals($expected_template, $template);
    }

    public function test_single_template_content() {
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
            'post_content' => 'This is a test business listing'
        ));
        $this->go_to(get_permalink($post_id));

        ob_start();
        include(plugin_dir_path(dirname(__FILE__)) . 'templates/single-business_listing.php');
        $content = ob_get_clean();

        $this->assertStringContainsString('Test Business', $content);
        $this->assertStringContainsString('This is a test business listing', $content);
    }

    public function test_archive_template_content() {
        $post_id = $this->factory->post->create(array(
            'post_type' => 'business_listing',
            'post_title' => 'Test Business',
        ));
        $this->go_to(get_post_type_archive_link('business_listing'));

        ob_start();
        include(plugin_dir_path(dirname(__FILE__)) . 'templates/archive-business_listing.php');
        $content = ob_get_clean();

        $this->assertStringContainsString('Test Business', $content);
    }
}