<?php
/*
Plugin Name: Happy Business Listing
Description: Custom plugin for business listings with Gutenberg blocks, ACF integration, sub-site creation, and WhatsApp integration.
Version: 1.2
Author: HappyPress, patilswapnilv
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HBL_VERSION', '1.2');
define('HBL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HBL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once HBL_PLUGIN_DIR . 'includes/custom-post-types.php';
require_once HBL_PLUGIN_DIR . 'includes/acf-fields.php';
require_once HBL_PLUGIN_DIR . 'includes/user-registration.php';
require_once HBL_PLUGIN_DIR . 'includes/gutenberg-blocks.php';
require_once HBL_PLUGIN_DIR . 'includes/site-creation.php';
require_once HBL_PLUGIN_DIR . 'includes/whatsapp-integration.php';
require_once HBL_PLUGIN_DIR . 'includes/form-shortcode.php';
require_once HBL_PLUGIN_DIR . 'includes/settings.php';
require_once HBL_PLUGIN_DIR . 'includes/templates.php';
require_once HBL_PLUGIN_DIR . 'includes/search-and-filters.php';
require_once HBL_PLUGIN_DIR . 'includes/permalinks.php';

// Activation hook
register_activation_hook(__FILE__, 'hbl_activate_plugin');

function hbl_activate_plugin() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Create default options
    add_option('hbl_activate_search', '1');
    add_option('hbl_activate_blocks', '1');
    add_option('hbl_whatsapp_integration', 'twilio');
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'hbl_deactivate_plugin');

function hbl_deactivate_plugin() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Enqueue scripts and styles
function hbl_enqueue_scripts() {
    wp_enqueue_style('hbl-style', HBL_PLUGIN_URL . 'assets/css/style.css', array(), HBL_VERSION);
    wp_enqueue_script('hbl-script', HBL_PLUGIN_URL . 'assets/js/script.js', array('jquery'), HBL_VERSION, true);
}
add_action('wp_enqueue_scripts', 'hbl_enqueue_scripts');

// Enqueue admin scripts and styles
function hbl_enqueue_admin_scripts($hook) {
    if ('toplevel_page_hbl_settings' !== $hook) {
        return;
    }
    wp_enqueue_style('hbl-admin-style', HBL_PLUGIN_URL . 'assets/css/admin-style.css', array(), HBL_VERSION);
    wp_enqueue_script('hbl-admin-script', HBL_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), HBL_VERSION, true);
}
add_action('admin_enqueue_scripts', 'hbl_enqueue_admin_scripts');

// Add settings link on plugin page
function hbl_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=hbl_settings">' . __('Settings', 'happy-business-listing') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'hbl_add_settings_link');

// Load text domain for internationalization
function hbl_load_textdomain() {
    load_plugin_textdomain('happy-business-listing', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'hbl_load_textdomain');