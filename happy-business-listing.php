<?php
/**
 * Plugin Name: Happy Business Listing
 * Description: Custom plugin for business listings with Gutenberg blocks, ACF integration, sub-site creation, and WhatsApp integration.
 * Version: 1.3.0
 * Author: HappyPress, patilswapnilv
 * Author URI: https://happypress.com
 * Text Domain: happy-business-listing
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.6
 * Requires PHP: 7.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HBL_VERSION', '1.3.0');
define('HBL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HBL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HBL_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('HBL_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class Happy_Business_Listing {
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . HBL_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('happy-business-listing', false, dirname(HBL_PLUGIN_BASENAME) . '/languages');
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.6', '<')) {
            add_action('admin_notices', array($this, 'wordpress_version_notice'));
            return;
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }
        
        // Include required files
        $this->include_files();
        
        // Add admin notices for missing dependencies
        add_action('admin_notices', array($this, 'check_dependencies'));
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        // Helper functions (must be loaded first)
        require_once HBL_PLUGIN_DIR . 'includes/helpers.php';
        
        // Security and logging (must be loaded early)
        require_once HBL_PLUGIN_DIR . 'includes/security.php';
        
        // Core functionality
        require_once HBL_PLUGIN_DIR . 'includes/custom-post-types.php';
        require_once HBL_PLUGIN_DIR . 'includes/acf-fields.php';
        require_once HBL_PLUGIN_DIR . 'includes/user-registration.php';
        require_once HBL_PLUGIN_DIR . 'includes/site-creation.php';
        require_once HBL_PLUGIN_DIR . 'includes/whatsapp-integration.php';
        require_once HBL_PLUGIN_DIR . 'includes/form-shortcode.php';
        require_once HBL_PLUGIN_DIR . 'includes/settings.php';
        require_once HBL_PLUGIN_DIR . 'includes/templates.php';
        require_once HBL_PLUGIN_DIR . 'includes/search-and-filters.php';
        require_once HBL_PLUGIN_DIR . 'includes/permalinks.php';
        
        // New improvements (load after core functionality)
        require_once HBL_PLUGIN_DIR . 'includes/rest-api.php';
        require_once HBL_PLUGIN_DIR . 'includes/caching.php';
        require_once HBL_PLUGIN_DIR . 'includes/accessibility.php';
        
        // Gutenberg blocks (only if WordPress version supports it)
        if (function_exists('register_block_type')) {
            require_once HBL_PLUGIN_DIR . 'includes/gutenberg-blocks.php';
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options first (these are safe to call)
        $default_options = array(
            'hbl_activate_blocks' => '1',
            'hbl_activate_search' => '1',
            'hbl_enable_logging' => '0',
            'hbl_whatsapp_integration' => 'twilio',
            'hbl_single_permalink_structure' => 'business/%postname%',
            'hbl_archive_permalink_structure' => 'businesses',
            'hbl_welcome_message' => 'Welcome to our business listing service!',
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
        
        // Add activation timestamp
        add_option('hbl_activation_time', time());
        
        // Create essential pages
        $this->create_essential_pages();
        
        // Schedule post type registration for next init
        add_action('init', array($this, 'delayed_activation'), 1);
    }
    
    /**
     * Create essential pages during activation
     */
    private function create_essential_pages() {
        $pages = array(
            'business_signup' => array(
                'title' => 'Business Signup',
                'content' => '[business_signup_form]',
                'slug' => 'business-signup'
            ),
            'business_list' => array(
                'title' => 'Business Directory',
                'content' => '[business_listing_archive]',
                'slug' => 'business-directory'
            ),
            'thank_you' => array(
                'title' => 'Thank You',
                'content' => '<h2>Thank you for your submission!</h2><p>Your business listing has been submitted successfully. We will review it and get back to you soon.</p><p><a href="' . home_url('/business-directory') . '">Browse Business Directory</a></p>',
                'slug' => 'thank-you'
            )
        );
        
        foreach ($pages as $page_key => $page_data) {
            // Check if page already exists
            $existing_page = get_page_by_path($page_data['slug']);
            
            if (!$existing_page) {
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_name' => $page_data['slug'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => 1
                ));
                
                if ($page_id && !is_wp_error($page_id)) {
                    // Store page ID in options
                    add_option('hbl_' . $page_key . '_page_id', $page_id);
            }
            } else {
                // Store existing page ID
                add_option('hbl_' . $page_key . '_page_id', $existing_page->ID);
            }
        }
    }
    
    /**
     * Delayed activation tasks
     */
    public function delayed_activation() {
        // Create custom post types
        if (function_exists('hbl_register_post_types')) {
            hbl_register_post_types();
        }
        
        // Create business user role
        if (function_exists('hbl_create_business_user_role')) {
            hbl_create_business_user_role();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Remove the action to prevent running again
        remove_action('init', array($this, 'delayed_activation'), 1);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Add plugin action links
     */
    public function add_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('edit.php?post_type=business_listing&page=hbl_settings') . '">' . __('Settings', 'happy-business-listing') . '</a>',
        );
        return array_merge($plugin_links, $links);
    }
    
    /**
     * WordPress version notice
     */
    public function wordpress_version_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Happy Business Listing requires WordPress version 5.6 or higher. Please upgrade WordPress to use this plugin.', 'happy-business-listing'); ?></p>
        </div>
        <?php
    }
    
    /**
     * PHP version notice
     */
    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Happy Business Listing requires PHP version 7.2 or higher. Please upgrade PHP to use this plugin.', 'happy-business-listing'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Check dependencies
     */
    public function check_dependencies() {
        // Check for ACF
        if (!class_exists('ACF') && !function_exists('acf_add_local_field_group')) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e('Happy Business Listing works best with Advanced Custom Fields (ACF) plugin. While the plugin will function without ACF, we recommend installing it for the best experience.', 'happy-business-listing'); ?></p>
                <p><a href="<?php echo admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term'); ?>" class="button button-primary"><?php _e('Install ACF', 'happy-business-listing'); ?></a></p>
            </div>
            <?php
        }
        
        // Check for multisite if sub-site creation is enabled
        if (!is_multisite() && get_option('hbl_enable_subsite_creation') == '1') {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e('Sub-site creation is enabled but WordPress is not in multisite mode. Sub-site creation will be skipped.', 'happy-business-listing'); ?></p>
            </div>
            <?php
        }
    }
}

// Initialize the plugin
Happy_Business_Listing::get_instance();
