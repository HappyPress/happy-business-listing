<?php

class WhatsAppIntegrationTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();
        // Include the file containing the WhatsApp integration functions
        require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/whatsapp-integration.php');
    }

    public function test_hbl_send_whatsapp_message_twilio() {
        // Mock Twilio API call
        add_filter('pre_http_request', function($preempt, $args, $url) {
            if (strpos($url, 'api.twilio.com') !== false) {
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array('sid' => 'TEST_SID'))
                );
            }
            return $preempt;
        }, 10, 3);

        // Set Twilio API key option
        update_option('hbl_twilio_api', 'test_api_key');

        $result = hbl_send_whatsapp_message('1234567890', 'Test message');
        $this->assertTrue($result);
    }

    public function test_hbl_send_whatsapp_message_business_api() {
        // Mock WhatsApp Business API call
        add_filter('pre_http_request', function($preempt, $args, $url) {
            if (strpos($url, 'graph.facebook.com') !== false) {
                return array(
                    'response' => array('code' => 200),
                    'body' => json_encode(array('messages' => array(array('id' => 'TEST_ID'))))
                );
            }
            return $preempt;
        }, 10, 3);

        // Set WhatsApp Business API key option
        update_option('hbl_whatsapp_business_api', 'test_api_key');

        $result = hbl_send_whatsapp_message('1234567890', 'Test message');
        $this->assertTrue($result);
    }

    public function test_hbl_send_whatsapp_message_invalid_api() {
        // Set invalid API key
        update_option('hbl_twilio_api', '');
        update_option('hbl_whatsapp_business_api', '');

        $result = hbl_send_whatsapp_message('1234567890', 'Test message');
        $this->assertFalse($result);
    }

    public function test_hbl_format_whatsapp_number() {
        $this->assertEquals('+11234567890', hbl_format_whatsapp_number('1234567890'));
        $this->assertEquals('+11234567890', hbl_format_whatsapp_number('+11234567890'));
        $this->assertEquals('+11234567890', hbl_format_whatsapp_number('(123) 456-7890'));
    }
}