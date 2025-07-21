<?php
/**
 * Settings for Happy Business Listing
 * 
 * Handles the plugin settings and options page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register settings and add options page
 */
function hbl_register_settings() {
    register_setting('hbl_options_group', 'hbl_activate_search', 'hbl_sanitize_filter_options');
    register_setting('hbl_options_group', 'hbl_activate_blocks', 'hbl_sanitize_checkbox');
    register_setting('hbl_options_group', 'hbl_use_hsf_filters', 'hbl_sanitize_advanced_filter_options');
    register_setting('hbl_options_group', 'hbl_whatsapp_integration', 'hbl_sanitize_whatsapp_integration');
    register_setting('hbl_options_group', 'hbl_twilio_api', 'hbl_sanitize_api_key');
    register_setting('hbl_options_group', 'hbl_whatsapp_business_api', 'hbl_sanitize_api_key');
    register_setting('permalink', 'hbl_single_permalink_structure', 'hbl_sanitize_permalink');
    register_setting('permalink', 'hbl_archive_permalink_structure', 'hbl_sanitize_permalink');
    register_setting('hbl_options_group', 'hbl_enable_logging', 'hbl_sanitize_checkbox');
}
add_action('admin_init', 'hbl_register_settings');

/**
 * Sanitize checkbox
 *
 * @param mixed $input The input to sanitize
 * @return string '1' if checked, '0' if not
 */
function hbl_sanitize_checkbox($input) {
    return isset($input) ? '1' : '0';
}

/**
 * Sanitize filter options to ensure mutual exclusivity
 *
 * @param mixed $input The input to sanitize
 * @return string '1' if checked, '0' if not
 */
function hbl_sanitize_filter_options($input) {
    // If this is the basic filters option being enabled
    if (isset($input) && $input == '1') {
        // Disable advanced filters
        update_option('hbl_use_hsf_filters', '0');
        return '1';
    }
    return '0';
}

/**
 * Sanitize advanced filter options to ensure mutual exclusivity
 *
 * @param mixed $input The input to sanitize
 * @return string '1' if checked, '0' if not
 */
function hbl_sanitize_advanced_filter_options($input) {
    // If this is the advanced filters option being enabled
    if (isset($input) && $input == '1') {
        // Disable basic filters
        update_option('hbl_activate_search', '0');
        return '1';
    }
    return '0';
}

/**
 * Sanitize WhatsApp integration
 *
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function hbl_sanitize_whatsapp_integration($input) {
    $valid_options = array('twilio', 'whatsapp_business');
    return in_array($input, $valid_options) ? $input : 'twilio';
}

/**
 * Sanitize API key
 *
 * @param string $input The input to sanitize
 * @return string The sanitized input
/**
 * Sanitize permalink
 *
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function hbl_sanitize_permalink($input) {
    return sanitize_text_field(trim($input, '/'));
}

/**
 * Register options page
 */
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

/**
 * Display options page
 */
function hbl_options_page() {
    // Get active tab
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    
    // Get available tabs
    $tabs = apply_filters('hbl_settings_tabs', array(
        'general'  => __('General', 'happy-business-listing'),
        'page'     => __('Page Management', 'happy-business-listing'),
        'whatsapp' => __('WhatsApp', 'happy-business-listing'),
        'logs'     => __('Logs', 'happy-business-listing')
    ));
    ?>
    <div class="wrap hbl-settings-page">
        <h1><?php _e('Happy Business Listing Settings', 'happy-business-listing'); ?></h1>
        
        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_id => $tab_name) : ?>
                <a href="?post_type=business_listing&page=hbl_settings&tab=<?php echo esc_attr($tab_id); ?>" class="nav-tab <?php echo $active_tab == $tab_id ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($tab_name); ?></a>
            <?php endforeach; ?>
        </h2>
        
        <div class="hbl-tab-content-wrapper">
            <?php
            // Display tab content
            if ($active_tab == 'general') {
                hbl_display_general_tab();
            } elseif ($active_tab == 'page') {
                hbl_display_page_management_tab();
            } elseif ($active_tab == 'whatsapp') {
                hbl_display_whatsapp_tab();
            } elseif ($active_tab == 'logs') {
                hbl_display_logs_tab();
            } else {
                // Allow other tabs to be added
                do_action('hbl_settings_tab_' . $active_tab);
            }
            ?>
        </div>
    </div>
    <?php
    hbl_settings_styles();
}

/**
 * Display general tab content
 */
function hbl_display_general_tab() {
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('hbl_options_group'); ?>
        <div class="hbl-settings-grid">
            <div class="hbl-settings-section">
                <h2><?php _e('General Settings', 'happy-business-listing'); ?></h2>
                <div class="hbl-setting-item">
                    <label for="hbl_activate_search"><?php _e('Basic Filters (HBL)', 'happy-business-listing'); ?></label>
                    <label class="switch">
                        <input type="checkbox" id="hbl_activate_search" name="hbl_activate_search" value="1" <?php checked(1, get_option('hbl_activate_search'), true); ?>>
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php _e('Enable basic search and filter functionality built into HBL.', 'happy-business-listing'); ?></p>
                    <?php
                    if (get_option('hbl_activate_search') == 1 && !is_plugin_active('happy-search-and-filter/happy-search-and-filter.php')) {
                        echo '<p class="description error">' . __('Please install and activate the Happy Search and Filter plugin.', 'happy-business-listing') . '</p>';
                    }
                    ?>
                </div>
                <div class="hbl-setting-item">
                    <label for="hbl_use_hsf_filters"><strong><?php _e('Advanced Search Filters (HSF)', 'happy-business-listing'); ?></strong></label>
                    <label class="switch">
                        <input type="checkbox" id="hbl_use_hsf_filters" name="hbl_use_hsf_filters" value="1" <?php checked(1, get_option('hbl_use_hsf_filters'), true); ?>>
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php _e('Enable advanced search and filter functionality with Gutenberg blocks and saved filters.', 'happy-business-listing'); ?></p>
                    <?php
                    if ( get_option( 'hbl_use_hsf_filters' ) == '1' && ! function_exists( 'hsf_save_filter' ) ) {
                        echo '<p class="description error">' . __( 'Happy Search & Filter plugin is required for Advanced Filters. Please install and activate it.', 'happy-business-listing' ) . '</p>';
                    }
                    ?>
                </div>
                <div class="hbl-setting-item">
                    <label for="hbl_activate_blocks"><?php _e('Activate Custom Blocks', 'happy-business-listing'); ?></label>
                    <label class="switch">
                        <input type="checkbox" id="hbl_activate_blocks" name="hbl_activate_blocks" value="1" <?php checked(1, get_option('hbl_activate_blocks'), true); ?>>
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php _e('Enable custom Gutenberg blocks for business listings.', 'happy-business-listing'); ?></p>
                </div>
                <div class="hbl-setting-item">
                    <label for="hbl_enable_logging"><?php _e('Enable Error Logging', 'happy-business-listing'); ?></label>
                    <label class="switch">
                        <input type="checkbox" id="hbl_enable_logging" name="hbl_enable_logging" value="1" <?php checked(1, get_option('hbl_enable_logging'), true); ?>>
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php _e('Log errors and debugging information to help troubleshoot issues.', 'happy-business-listing'); ?></p>
                </div>
            </div>
            <div class="hbl-settings-section">
            </div>
        </div>
        <?php submit_button(__('Save Settings', 'happy-business-listing'), 'primary', 'submit', false, ['class' => 'hbl-submit-button']); ?>
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Make filter options mutually exclusive
        $('#hbl_activate_search, #hbl_use_hsf_filters').on('change', function() {
            var $this = $(this);
            var $other = $this.attr('id') === 'hbl_activate_search' ? $('#hbl_use_hsf_filters') : $('#hbl_activate_search');
            
            if ($this.is(':checked')) {
                $other.prop('checked', false);
            }
        });
    });
    </script>
    <?php
}

/**
 * Display page management tab content
 */
function hbl_display_page_management_tab() {
    $page_status = hbl_get_directory_page_status();
    ?>
    <div class="hbl-settings-grid">
        <div class="hbl-settings-section">
            <h2><?php _e('Business Directory Page', 'happy-business-listing'); ?></h2>
            
            <div class="hbl-setting-item">
                <h3><?php _e('Page Status', 'happy-business-listing'); ?></h3>
                <div class="hbl-status-indicator <?php echo esc_attr($page_status['class']); ?>">
                    <span class="dashicons <?php echo $page_status['class'] === 'success' ? 'dashicons-yes-alt' : ($page_status['class'] === 'warning' ? 'dashicons-warning' : 'dashicons-dismiss'); ?>"></span>
                    <?php echo esc_html($page_status['message']); ?>
                </div>
                
                <?php if (isset($page_status['page_url'])) : ?>
                    <p>
                        <a href="<?php echo esc_url($page_status['page_url']); ?>" class="button button-secondary" target="_blank">
                            <?php _e('View Page', 'happy-business-listing'); ?>
                        </a>
                        <a href="<?php echo esc_url($page_status['edit_url']); ?>" class="button button-secondary">
                            <?php _e('Edit Page', 'happy-business-listing'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="hbl-setting-item">
                <h3><?php _e('Page Management', 'happy-business-listing'); ?></h3>
                <p class="description">
                    <?php _e('The business directory page is automatically created when the plugin is activated. This page uses Gutenberg blocks to display your business listings with filters.', 'happy-business-listing'); ?>
                </p>
                
                <div class="hbl-page-actions">
                    <button type="button" class="button button-primary" id="hbl-recreate-page">
                        <?php _e('Recreate Directory Page', 'happy-business-listing'); ?>
                    </button>
                    <p class="description">
                        <?php _e('This will create a new business directory page with default content. The old page will be deleted if it exists.', 'happy-business-listing'); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="hbl-settings-section">
            <h2><?php _e('Block-Based Directory', 'happy-business-listing'); ?></h2>
            
            <div class="hbl-setting-item">
                <h3><?php _e('How It Works', 'happy-business-listing'); ?></h3>
                <ul class="hbl-feature-list">
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Uses a dedicated page instead of archive templates', 'happy-business-listing'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Built with Gutenberg blocks for flexibility', 'happy-business-listing'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Compatible with all themes, including block themes', 'happy-business-listing'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Easy to customize through the page editor', 'happy-business-listing'); ?>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('SEO-friendly with proper page structure', 'happy-business-listing'); ?>
                    </li>
                </ul>
            </div>
            
            <div class="hbl-setting-item">
                <h3><?php _e('Available Blocks', 'happy-business-listing'); ?></h3>
                <div class="hbl-blocks-grid">
                    <div class="hbl-block-card">
                        <span class="dashicons dashicons-store"></span>
                        <h4><?php _e('Business Grid', 'happy-business-listing'); ?></h4>
                        <p><?php _e('Display business listings in a customizable grid layout with filters and pagination.', 'happy-business-listing'); ?></p>
                    </div>
                    
                    <?php if (function_exists('hsf_save_filter')) : ?>
                    <div class="hbl-block-card">
                        <span class="dashicons dashicons-search"></span>
                        <h4><?php _e('Advanced Search', 'happy-business-listing'); ?></h4>
                        <p><?php _e('Powerful search and filter interface with saved filter presets.', 'happy-business-listing'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#hbl-recreate-page').on('click', function() {
            var $button = $(this);
            var originalText = $button.text();
            
            if (!confirm('<?php echo esc_js(__('Are you sure you want to recreate the directory page? This will delete the existing page if it exists.', 'happy-business-listing')); ?>')) {
                return;
            }
            
            $button.text('<?php echo esc_js(__('Creating...', 'happy-business-listing')); ?>').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hbl_recreate_directory_page',
                    nonce: '<?php echo wp_create_nonce('hbl_recreate_page'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        $button.text(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('<?php echo esc_js(__('An error occurred. Please try again.', 'happy-business-listing')); ?>');
                    $button.text(originalText).prop('disabled', false);
                }
            });
        });
    });
    </script>
    <?php
}

if (!function_exists('hbl_display_whatsapp_tab')) {
function hbl_display_whatsapp_tab() {
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('hbl_options_group'); ?>
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
            <div class="hbl-setting-item twilio-option">
                <label for="hbl_twilio_api"><?php _e('Twilio API Key', 'happy-business-listing'); ?></label>
                <input type="text" id="hbl_twilio_api" name="hbl_twilio_api" value="<?php echo esc_attr(get_option('hbl_twilio_api')); ?>" class="regular-text">
            </div>
            <div class="hbl-setting-item wa-business-option">
                <label for="hbl_whatsapp_business_api"><?php _e('WhatsApp Business API Key', 'happy-business-listing'); ?></label>
                <input type="text" id="hbl_whatsapp_business_api" name="hbl_whatsapp_business_api" value="<?php echo esc_attr(get_option('hbl_whatsapp_business_api')); ?>" class="regular-text">
            </div>
            <div class="hbl-setting-item twilio-option">
                <label for="hbl_twilio_auth"><?php _e('Twilio Auth Token', 'happy-business-listing'); ?></label>
                <input type="text" id="hbl_twilio_auth" name="hbl_twilio_auth" value="<?php echo esc_attr(get_option('hbl_twilio_auth')); ?>" class="regular-text">
            </div>
            <div class="hbl-setting-item twilio-option">
                <label for="hbl_twilio_from">
                    <?php _e('Twilio WhatsApp From Number', 'happy-business-listing'); ?>
                </label>
                <input type="text" id="hbl_twilio_from" name="hbl_twilio_from" value="<?php echo esc_attr(get_option('hbl_twilio_from')); ?>" class="regular-text">
            </div>
            <div class="hbl-setting-item wa-business-option">
                <label for="hbl_whatsapp_phone_id"><?php _e('WhatsApp Business Phone ID', 'happy-business-listing'); ?></label>
                <input type="text" id="hbl_whatsapp_phone_id" name="hbl_whatsapp_phone_id" value="<?php echo esc_attr(get_option('hbl_whatsapp_phone_id')); ?>" class="regular-text">
            </div>
        </div>
        <script>
        jQuery(function($){
            function toggleWaFields(){
                var type = $('input[name="hbl_whatsapp_integration"]:checked').val();
                $('.twilio-option').toggle(type === 'twilio');
                $('.wa-business-option').toggle(type === 'whatsapp_business');
            }
            toggleWaFields();
            $('input[name="hbl_whatsapp_integration"]').on('change', toggleWaFields);
        });
        </script>
        <?php submit_button(__('Save Settings', 'happy-business-listing'), 'primary', 'submit', false, ['class' => 'hbl-submit-button']); ?>
    </form>
    <?php
}
}

/**
 * Display logs tab content
 */
function hbl_display_logs_tab() {
    ?>
    <div class="hbl-logs-section">
        <h2><?php _e('Error Logs', 'happy-business-listing'); ?></h2>
        <?php hbl_display_logs(); ?>
    </div>
    <?php
}

/**
 * Add permalink structure fields
 */
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

/**
 * Single permalink structure field callback
 */
function hbl_single_permalink_structure_callback() {
    $value = get_option('hbl_single_permalink_structure', 'business/%postname%');
    echo '<input type="text" class="regular-text code" value="' . esc_attr($value) . '" name="hbl_single_permalink_structure">';
    echo '<p class="description">' . __('Enter the permalink structure for single business listings. Default: business/%postname%', 'happy-business-listing') . '</p>';
}

/**
 * Archive permalink structure field callback
 */
function hbl_archive_permalink_structure_callback() {
    $value = get_option('hbl_archive_permalink_structure', 'businesses');
    echo '<input type="text" class="regular-text code" value="' . esc_attr($value) . '" name="hbl_archive_permalink_structure">';
    echo '<p class="description">' . __('Enter the permalink structure for the business listings archive page. Default: businesses', 'happy-business-listing') . '</p>';
}

/**
 * Display logs
 */
function hbl_display_logs() {
    if (get_option('hbl_enable_logging') != 1) {
        echo '<p>' . __('Logging is currently disabled. Enable it in the General Settings tab to view logs.', 'happy-business-listing') . '</p>';
        return;
    }

    $log_file = WP_CONTENT_DIR . '/hbl-error.log';
    if (file_exists($log_file)) {
        $logs = file_get_contents($log_file);
        echo '<pre class="hbl-logs">' . esc_html($logs) . '</pre>';
        echo '<form method="post">';
        echo '<input type="hidden" name="hbl_clear_logs" value="1">';
        submit_button(__('Clear Logs', 'happy-business-listing'), 'secondary', 'submit', false);
        echo '</form>';
    } else {
        echo '<p>' . __('No errors found.', 'happy-business-listing') . '</p>';
    }
}

/**
 * Settings styles
 */
function hbl_settings_styles() {
    ?>
    <style>
        .hbl-settings-page {
            max-width: 1200px;
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
        .hbl-logs {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            max-height: 400px;
            overflow: auto;
        }
        .hbl-tab-content-wrapper {
            margin-top: 20px;
        }
        .hbl-subsite-management {
            margin-top: 30px;
        }
        
        /* Page Management Tab Styles */
        .hbl-status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .hbl-status-indicator.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .hbl-status-indicator.warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        .hbl-status-indicator.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .hbl-feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .hbl-feature-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .hbl-feature-list li:last-child {
            border-bottom: none;
        }
        
        .hbl-feature-list .dashicons {
            color: #16a085;
            font-size: 16px;
        }
        
        .hbl-blocks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .hbl-block-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .hbl-block-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #2271b1;
        }
        
        .hbl-block-card .dashicons {
            font-size: 32px;
            color: #2271b1;
            margin-bottom: 10px;
        }
        
        .hbl-block-card h4 {
            margin: 10px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .hbl-block-card p {
            margin: 0;
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }
        
        .hbl-page-actions {
            margin-top: 15px;
        }
        
        .hbl-page-actions .button {
            margin-right: 10px;
        }
        
        .hbl-setting-item h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .hbl-settings-grid {
                grid-template-columns: 1fr;
            }
            
            .hbl-blocks-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
}

/**
 * Handle clear logs action
 */
function hbl_handle_clear_logs() {
    if (isset($_POST['hbl_clear_logs']) && current_user_can('manage_options')) {
        $log_file = WP_CONTENT_DIR . '/hbl-error.log';
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
            
            // Add admin notice
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Logs cleared successfully.', 'happy-business-listing') . '</p></div>';
            });
        }
    }
}
add_action('admin_init', 'hbl_handle_clear_logs');