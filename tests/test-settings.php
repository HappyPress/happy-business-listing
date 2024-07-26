<?php
class SettingsTest extends WP_UnitTestCase {
    public function test_hbl_sanitize_checkbox() {
        $this->assertEquals('1', hbl_sanitize_checkbox('1'));
        $this->assertEquals('0', hbl_sanitize_checkbox(''));
        $this->assertEquals('0', hbl_sanitize_checkbox(null));
    }

    public function test_hbl_sanitize_whatsapp_integration() {
        $this->assertEquals('twilio', hbl_sanitize_whatsapp_integration('twilio'));
        $this->assertEquals('whatsapp_business', hbl_sanitize_whatsapp_integration('whatsapp_business'));
        $this->assertEquals('twilio', hbl_sanitize_whatsapp_integration('invalid_option'));
    }

    public function test_hbl_sanitize_api_key() {
        $this->assertEquals('test_key', hbl_sanitize_api_key('test_key'));
        $this->assertEquals('', hbl_sanitize_api_key(''));
    }

    public function test_hbl_sanitize_permalink() {
        $this->assertEquals('business/listing', hbl_sanitize_permalink('/business/listing/'));
        $this->assertEquals('business/listing', hbl_sanitize_permalink('business/listing'));
    }

    public function test_hbl_register_settings() {
        global $wp_registered_settings;
        hbl_register_settings();
        
        $this->assertArrayHasKey('hbl_activate_search', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_activate_blocks', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_whatsapp_integration', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_twilio_api', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_whatsapp_business_api', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_single_permalink_structure', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_archive_permalink_structure', $wp_registered_settings);
        $this->assertArrayHasKey('hbl_enable_logging', $wp_registered_settings);
    }

    public function test_hbl_register_options_page() {
        global $submenu;
        hbl_register_options_page();
        
        $this->assertArrayHasKey('edit.php?post_type=business_listing', $submenu);
        $this->assertContains('Settings', $submenu['edit.php?post_type=business_listing'][1]);
    }
}