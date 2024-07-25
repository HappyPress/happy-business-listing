<?php
/*
Plugin Name: Happy Business Listing
Description: Custom plugin for business listings with Gutenberg blocks, ACF integration, sub-site creation, and WhatsApp integration.
Version: 1.2
Author: HappyPress, patilswapnilv
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include required files
require_once plugin_dir_path( __FILE__ ) . 'includes/custom-post-types.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/acf-fields.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/user-registration.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/gutenberg-blocks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/site-creation.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/whatsapp-integration.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/form-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/templates.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/search-and-filters.php';
require_once plugin_dir_path(__FILE__) . 'includes/permalinks.php';