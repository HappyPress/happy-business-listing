<?php

// Register settings and add options page
function hbl_register_settings() {
    register_setting('hbl_options_group', 'hbl_activate_search', 'hbl_sanitize_checkbox');
    register_setting('hbl_options_group', 'hbl_activate_blocks', 'hbl_sanitize_checkbox');
    register_setting('hbl_options_group', 'hbl_whatsapp_integration', 'hbl_sanitize_whatsapp_integration');
    register_setting('hbl_options_group', 'hbl_twilio_api', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_whatsapp_business_api', 'hbl_sanitize_api_key');
    register_setting('permalink', 'hbl_single_permalink_structure', 'hbl_sanitize_permalink');
    register_setting('permalink', 'hbl_archive_permalink_structure', 'hbl_sanitize_permalink');
}

function hbl_sanitize_checkbox($input) {
    return isset($input) ? '1' : '0';
}

function hbl_sanitize_whatsapp_integration($input) {
    $valid_options = array('twilio', 'whatsapp_business');
    return in_array($input, $valid_options) ? $input : 'twilio';
}

function hbl_sanitize_api_key($input) {
    return sanitize_text_field($input);
}

function hbl_sanitize_permalink($input) {
    return sanitize_text_field(trim($input, '/'));
}

function hbl_register_options_page() {
    add_submenu_page(
        'edit.php?post_type=business_listing',
        __('Happy Business Listing Settings', 'happy-business-listing'),
        __('Settings', 'happy-business-listing'),
        'manage_options',
        'hbl_settings',
        'hbl_options_page'
    );
}
add_action('admin_menu', 'hbl_register_options_page');
add_action('admin_init', 'hbl_register_settings');

function hbl_options_page() {
    ?>
    <div class="wrap hbl-settings-page">
        <h1><?php _e('Happy Business Listing Settings', 'happy-business-listing'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('hbl_options_group'); ?>
            <div class="hbl-settings-grid">
                <div class="hbl-settings-section">
                    <h2><?php _e('General Settings', 'happy-business-listing'); ?></h2>
                    <div class="hbl-setting-item">
                        <label for="hbl_activate_search"><?php _e('Search and Filters', 'happy-business-listing'); ?></label>
                        <label class="switch">
                            <input type="checkbox" id="hbl_activate_search" name="hbl_activate_search" value="1" <?php checked(1, get_option('hbl_activate_search'), true); ?>>
                            <span class="slider round"></span>
                        </label>
                        <?php
                        if (get_option('hbl_activate_search') == 1 && !is_plugin_active('happy-search-and-filter/happy-search-and-filter.php')) {
                            echo '<p class="description error">' . __('Please install and activate the Happy Search and Filter plugin.', 'happy-business-listing') . '</p>';
                        }
                        ?>
                    </div>
                    <div class="hbl-setting-item">
                        <label for="hbl_activate_blocks"><?php _e('Activate Custom Blocks', 'happy-business-listing'); ?></label>
                        <label class="switch">
                            <input type="checkbox" id="hbl_activate_blocks" name="hbl_activate_blocks" value="1" <?php checked(1, get_option('hbl_activate_blocks'), true); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="hbl-settings-section">
                    <h2><?php _e('WhatsApp Integration', 'happy-business-listing'); ?></h2>
                    <div class="hbl-setting-item">
                        <label><?php _e('Integration Type', 'happy-business-listing'); ?></label>
                        <div class="hbl-radio-group">
                            <label>
                                <input type="radio" name="hbl_whatsapp_integration" value="twilio" <?php checked('twilio', get_option('hbl_whatsapp_integration')); ?>>
                                <?php _e('Twilio API', 'happy-business-listing'); ?>
                            </label>
                            <label>
                                <input type="radio" name="hbl_whatsapp_integration" value="whatsapp_business" <?php checked('whatsapp_business', get_option('hbl_whatsapp_integration')); ?>>
                                <?php _e('WhatsApp Business API', 'happy-business-listing'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="hbl-setting-item">
                        <label for="hbl_twilio_api"><?php _e('Twilio API Key', 'happy-business-listing'); ?></label>
                        <input type="text" id="hbl_twilio_api" name="hbl_twilio_api" value="<?php echo esc_attr(get_option('hbl_twilio_api')); ?>" class="regular-text">
                    </div>
                    <div class="hbl-setting-item">
                        <label for="hbl_whatsapp_business_api"><?php _e('WhatsApp Business API Key', 'happy-business-listing'); ?></label>
                        <input type="text" id="hbl_whatsapp_business_api" name="hbl_whatsapp_business_api" value="<?php echo esc_attr(get_option('hbl_whatsapp_business_api')); ?>" class="regular-text">
                    </div>
                </div>
            </div>
            <?php submit_button(__('Save Settings', 'happy-business-listing'), 'primary', 'submit', false, ['class' => 'hbl-submit-button']); ?>
        </form>
    </div>
    <?php
    hbl_settings_styles();
}

function hbl_add_permalink_structure_fields() {
    add_settings_field(
        'hbl_single_permalink_structure',
        __('Business Listing Single Permalink Structure', 'happy-business-listing'),
        'hbl_single_permalink_structure_callback',
        'permalink',
        'optional'
    );
    add_settings_field(
        'hbl_archive_permalink_structure',
        __('Business Listing Archive Permalink Structure', 'happy-business-listing'),
        'hbl_archive_permalink_structure_callback',
        'permalink',
        'optional'
    );
}
add_action('admin_init', 'hbl_add_permalink_structure_fields');

function hbl_single_permalink_structure_callback() {
    $value = get_option('hbl_single_permalink_structure', 'business/%postname%');
    echo '<input type="text" class="regular-text code" value="' . esc_attr($value) . '" name="hbl_single_permalink_structure">';
    echo '<p class="description">' . __('Enter the permalink structure for single business listings. Default: business/%postname%', 'happy-business-listing') . '</p>';
}

function hbl_archive_permalink_structure_callback() {
    $value = get_option('hbl_archive_permalink_structure', 'businesses');
    echo '<input type="text" class="regular-text code" value="' . esc_attr($value) . '" name="hbl_archive_permalink_structure">';
    echo '<p class="description">' . __('Enter the permalink structure for the business listings archive page. Default: businesses', 'happy-business-listing') . '</p>';
}

function hbl_settings_styles() {
    ?>
    <style>
        .hbl-settings-page {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .hbl-settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .hbl-settings-section {
            background: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
        }
        .hbl-setting-item {
            margin-bottom: 20px;
        }
        .hbl-setting-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .hbl-radio-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: normal;
        }
        .hbl-submit-button {
            margin-top: 20px !important;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .slider.round {
            border-radius: 34px;
        }
        .slider.round:before {
            border-radius: 50%;
        }
        .description.error {
            color: #d63638;
        }
    </style>
    <?php
}