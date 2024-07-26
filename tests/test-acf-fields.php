<?php

class ACFFieldsTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the ACF field registration
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/acf-fields.php');
    }

    public function test_acf_field_group_exists() {
        $field_group = acf_get_field_group('group_business_listing_fields');
        $this->assertNotFalse($field_group);
    }

    public function test_business_name_field_exists() {
        $field = acf_get_field('business_name');
        $this->assertNotFalse($field);
        $this->assertEquals('text', $field['type']);
    }

    public function test_company_type_field_exists() {
        $field = acf_get_field('company_type');
        $this->assertNotFalse($field);
        $this->assertEquals('select', $field['type']);
    }

    public function test_gst_no_field_exists() {
        $field = acf_get_field('gst_no');
        $this->assertNotFalse($field);
        $this->assertEquals('text', $field['type']);
    }

    public function test_tan_pan_field_exists() {
        $field = acf_get_field('tan_pan');
        $this->assertNotFalse($field);
        $this->assertEquals('text', $field['type']);
    }

    public function test_location_field_exists() {
        $field = acf_get_field('location');
        $this->assertNotFalse($field);
        $this->assertEquals('text', $field['type']);
    }

    public function test_website_field_exists() {
        $field = acf_get_field('website');
        $this->assertNotFalse($field);
        $this->assertEquals('url', $field['type']);
    }

    public function test_social_media_field_exists() {
        $field = acf_get_field('social_media');
        $this->assertNotFalse($field);
        $this->assertEquals('repeater', $field['type']);
    }

    public function test_whatsapp_number_field_exists() {
        $field = acf_get_field('whatsapp_number');
        $this->assertNotFalse($field);
        $this->assertEquals('text', $field['type']);
    }
}