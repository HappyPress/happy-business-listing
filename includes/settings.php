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
    add_submenu_page(
        'edit.php?post_type=business_listing',
        'Happy Business Listing Settings',
        'Settings',
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
        <h1>Happy Business Listing Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('hbl_options_group'); ?>
            <div class="hbl-settings-grid">
                <div class="hbl-settings-section">
                    <h2>General Settings</h2>
                    <div class="hbl-setting-item">
                        <label for="hbl_activate_search">Search and Filters</label>
                        <label class="switch">
                            <input type="checkbox" id="hbl_activate_search" name="hbl_activate_search" value="1" <?php checked(1, get_option('hbl_activate_search'), true); ?>>
                            <span class="slider round"></span>
                        </label>
                        <?php
                        if (get_option('hbl_activate_search') == 1 && !is_plugin_active('happy-search-and-filter/happy-search-and-filter.php')) {
                            echo '<p class="description error">Please install and activate the Happy Search and Filter plugin.</p>';
                        }
                        ?>
                    </div>
                    <div class="hbl-setting-item">
                        <label for="hbl_activate_blocks">Activate Custom Blocks</label>
                        <label class="switch">
                            <input type="checkbox" id="hbl_activate_blocks" name="hbl_activate_blocks" value="1" <?php checked(1, get_option('hbl_activate_blocks'), true); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="hbl-settings-section">
                    <h2>WhatsApp Integration</h2>
                    <div class="hbl-setting-item">
                        <label>Integration Type</label>
                        <div class="hbl-radio-group">
                            <label>
                                <input type="radio" name="hbl_whatsapp_integration" value="twilio" <?php checked('twilio', get_option('hbl_whatsapp_integration')); ?>>
                                Twilio API
                            </label>
                            <label>
                                <input type="radio" name="hbl_whatsapp_integration" value="whatsapp_business" <?php checked('whatsapp_business', get_option('hbl_whatsapp_integration')); ?>>
                                WhatsApp Business API
                            </label>
                        </div>
                    </div>
                    <div class="hbl-setting-item">
                        <label for="hbl_twilio_api">Twilio API Key</label>
                        <input type="text" id="hbl_twilio_api" name="hbl_twilio_api" value="<?php echo esc_attr(get_option('hbl_twilio_api')); ?>" class="regular-text">
                    </div>
                    <div class="hbl-setting-item">
                        <label for="hbl_whatsapp_business_api">WhatsApp Business API Key</label>
                        <input type="text" id="hbl_whatsapp_business_api" name="hbl_whatsapp_business_api" value="<?php echo esc_attr(get_option('hbl_whatsapp_business_api')); ?>" class="regular-text">
                    </div>
                </div>
            </div>
            <?php submit_button('Save Settings', 'primary', 'submit', false, ['class' => 'hbl-submit-button']); ?>
        </form>
    </div>
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
function hbl_add_permalink_structure_fields() {
    add_settings_field(
        'hbl_single_permalink_structure',
        'Business Listing Single Permalink Structure',
        'hbl_single_permalink_structure_callback',
        'permalink',
        'optional'
    );
    add_settings_field(
        'hbl_archive_permalink_structure',
        'Business Listing Archive Permalink Structure',
        'hbl_archive_permalink_structure_callback',
        'permalink',
        'optional'
    );

    register_setting('permalink', 'hbl_single_permalink_structure');
    register_setting('permalink', 'hbl_archive_permalink_structure');
}
add_action('admin_init', 'hbl_add_permalink_structure_fields');

function hbl_single_permalink_structure_callback() {
    $value = get_option('hbl_single_permalink_structure', '/business/%postname%/');
    echo '<input type="text" class="regular-text code" value="' . esc_attr($value) . '" name="hbl_single_permalink_structure">';
    echo '<p class="description">Enter the permalink structure for single business listings. Default: /business/%postname%/</p>';
}

function hbl_archive_permalink_structure_callback() {
    $value = get_option('hbl_archive_permalink_structure', '/businesses/');
    echo '<input type="text" class="regular-text code" value="' . esc_attr($value) . '" name="hbl_archive_permalink_structure">';
    echo '<p class="description">Enter the permalink structure for the business listings archive page. Default: /businesses/</p>';
}
?>

<style>
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
</style>