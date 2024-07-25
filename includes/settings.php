<?php

// Register settings and add options page
function hbl_register_settings() {
    add_option('hbl_custom_template');
    add_option('hbl_activate_search');
    add_option('hbl_activate_blocks');
    add_option('hbl_whatsapp_integration');
    add_option('hbl_twilio_api');
    add_option('hbl_whatsapp_business_api');
    add_option('hbl_custom_permalinks');

    register_setting('hbl_options_group', 'hbl_custom_template');
    register_setting('hbl_options_group', 'hbl_activate_search');
    register_setting('hbl_options_group', 'hbl_activate_blocks');
    register_setting('hbl_options_group', 'hbl_whatsapp_integration');
    register_setting('hbl_options_group', 'hbl_twilio_api');
    register_setting('hbl_options_group', 'hbl_whatsapp_business_api');
    register_setting('hbl_options_group', 'hbl_custom_permalinks');
}

function hbl_register_options_page() {
    add_options_page('Happy Business Listing Settings', 'Business Listing', 'manage_options', 'hbl_settings', 'hbl_options_page');
}
add_action('admin_menu', 'hbl_register_options_page');
add_action('admin_init', 'hbl_register_settings');

function hbl_options_page() {
?>
    <div>
        <h2>Happy Business Listing Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('hbl_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="hbl_custom_template">Custom Template</label></th>
                    <td><input type="text" id="hbl_custom_template" name="hbl_custom_template" value="<?php echo get_option('hbl_custom_template'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="hbl_activate_search">Activate Search and Filters</label></th>
                    <td><input type="checkbox" id="hbl_activate_search" name="hbl_activate_search" value="1" <?php checked(1, get_option('hbl_activate_search'), true); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="hbl_activate_blocks">Activate Custom Blocks</label></th>
                    <td><input type="checkbox" id="hbl_activate_blocks" name="hbl_activate_blocks" value="1" <?php checked(1, get_option('hbl_activate_blocks'), true); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="hbl_whatsapp_integration">WhatsApp Integration</label></th>
                    <td>
                        <input type="radio" id="hbl_twilio" name="hbl_whatsapp_integration" value="twilio" <?php checked('twilio', get_option('hbl_whatsapp_integration')); ?> />
                        <label for="hbl_twilio">Twilio API</label><br>
                        <input type="radio" id="hbl_whatsapp_business" name="hbl_whatsapp_integration" value="whatsapp_business" <?php checked('whatsapp_business', get_option('hbl_whatsapp_integration')); ?> />
                        <label for="hbl_whatsapp_business">WhatsApp Business API</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="hbl_twilio_api">Twilio API Key</label></th>
                    <td><input type="text" id="hbl_twilio_api" name="hbl_twilio_api" value="<?php echo get_option('hbl_twilio_api'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="hbl_whatsapp_business_api">WhatsApp Business API Key</label></th>
                    <td><input type="text" id="hbl_whatsapp_business_api" name="hbl_whatsapp_business_api" value="<?php echo get_option('hbl_whatsapp_business_api'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="hbl_custom_permalinks">Custom Permalinks</label></th>
                    <td><input type="text" id="hbl_custom_permalinks" name="hbl_custom_permalinks" value="<?php echo get_option('hbl_custom_permalinks'); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
?>
