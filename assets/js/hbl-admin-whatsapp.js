(function ($) {
    /*
     * Toggle visibility of settings rows depending on the selected WhatsApp
     * integration. Supports both <select> and <input type="radio"> controls
     * (in case the markup changes in the future).
     */
    function getIntegrationValue() {
        // Prefer radio buttons if they exist and one is checked
        var $radio = $('input[name="hbl_whatsapp_integration"]:checked');
        if ($radio.length) {
            return $radio.val();
        }
        // Fallback to the <select> element
        return $('#hbl_whatsapp_integration').val();
    }

    function toggle() {
        var selected = getIntegrationValue();

        var isTwilio = selected === 'twilio';
        var isBusiness = selected === 'whatsapp_business';

        // Show/hide Twilio-specific rows
        $('#hbl_twilio_account_sid, #hbl_twilio_auth_token, #hbl_twilio_from_number')
            .closest('tr, .hbl-setting-item')
            .toggle(isTwilio);

        // Show/hide WhatsApp Business API-specific rows
        $('#hbl_whatsapp_business_api, #hbl_whatsapp_business_phone_id')
            .closest('tr, .hbl-setting-item')
            .toggle(isBusiness);
    }

    $(document).ready(function () {
        // Initial state
        toggle();

        // Listen for changes on both radio and select controls
        $('input[name="hbl_whatsapp_integration"]').on('change', toggle);
        $('#hbl_whatsapp_integration').on('change', toggle);
    });
})(jQuery); 