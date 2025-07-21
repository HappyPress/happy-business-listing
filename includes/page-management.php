<?php
/**
 * Business Directory Page Management
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create business directory page on plugin activation
 */
function hbl_create_business_directory_page() {
    // Check if page already exists
    $existing_page_id = get_option('hbl_directory_page_id');
    
    if ($existing_page_id && get_post($existing_page_id) && get_post_status($existing_page_id) === 'publish') {
        return $existing_page_id; // Page already exists and is published
    }
    
    // Create the page
    $page_data = array(
        'post_title'   => __('Business Directory', 'happy-business-listing'),
        'post_content' => hbl_get_default_page_content(),
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => get_current_user_id(),
        'meta_input'   => array(
            '_hbl_is_directory_page' => '1'
        )
    );
    
    $page_id = wp_insert_post($page_data);
    
    if (!is_wp_error($page_id)) {
        // Save page ID in options
        update_option('hbl_directory_page_id', $page_id);
        
        // Set as front page if no front page is set
        if (get_option('show_on_front') === 'posts') {
            update_option('hbl_suggest_front_page', '1');
        }
        
        return $page_id;
    }
    
    return false;
}

/**
 * Get default page content with blocks
 */
function hbl_get_default_page_content() {
    $content = '';
    
    // Add a welcome paragraph
    $content .= '<!-- wp:paragraph -->';
    $content .= '<p>' . __('Welcome to our business directory. Find and connect with local businesses in your area.', 'happy-business-listing') . '</p>';
    $content .= '<!-- /wp:paragraph -->';
    
    $content .= "\n\n";
    
    // Add Advanced Search Block (if HSF is available)
    if (function_exists('hsf_save_filter')) {
        $content .= '<!-- wp:happy-search-and-filter/business-search -->';
        $content .= '<!-- /wp:happy-search-and-filter/business-search -->';
        $content .= "\n\n";
    }
    
    // Add Business Grid Block
    $content .= '<!-- wp:happy-business-listing/business-grid {"columns":3,"postsPerPage":12,"showFilters":true} -->';
    $content .= '<!-- /wp:happy-business-listing/business-grid -->';
    
    return $content;
}

/**
 * Check if current page is the business directory page
 */
function hbl_is_directory_page() {
    global $post;
    
    if (!$post) {
        return false;
    }
    
    $directory_page_id = get_option('hbl_directory_page_id');
    
    return ($post->ID == $directory_page_id) || get_post_meta($post->ID, '_hbl_is_directory_page', true);
}

/**
 * Get the business directory page URL
 */
function hbl_get_directory_page_url() {
    $page_id = get_option('hbl_directory_page_id');
    
    if ($page_id && get_post($page_id)) {
        return get_permalink($page_id);
    }
    
    // Fallback to manually constructed archive URL to avoid infinite loop
    return home_url('/businesses/');
}

/**
 * Recreate the business directory page
 */
function hbl_recreate_directory_page() {
    // Delete existing page if it exists
    $existing_page_id = get_option('hbl_directory_page_id');
    
    if ($existing_page_id) {
        wp_delete_post($existing_page_id, true);
    }
    
    // Create new page
    return hbl_create_business_directory_page();
}

/**
 * Handle page deletion cleanup
 */
function hbl_handle_page_deletion($post_id) {
    $directory_page_id = get_option('hbl_directory_page_id');
    
    if ($post_id == $directory_page_id) {
        // Directory page was deleted, clear the option
        delete_option('hbl_directory_page_id');
        
        // Set a flag to show admin notice
        update_option('hbl_directory_page_deleted', '1');
    }
}
add_action('before_delete_post', 'hbl_handle_page_deletion');

/**
 * Show admin notice if directory page was deleted
 */
function hbl_directory_page_deleted_notice() {
    if (get_option('hbl_directory_page_deleted')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('Happy Business Listing:', 'happy-business-listing'); ?></strong>
                <?php _e('The business directory page was deleted. You can recreate it in the plugin settings.', 'happy-business-listing'); ?>
                <a href="<?php echo admin_url('admin.php?page=hbl-settings'); ?>" class="button button-small">
                    <?php _e('Go to Settings', 'happy-business-listing'); ?>
                </a>
            </p>
        </div>
        <?php
        
        // Clear the flag after showing notice
        delete_option('hbl_directory_page_deleted');
    }
}
add_action('admin_notices', 'hbl_directory_page_deleted_notice');

/**
 * AJAX handler for recreating directory page
 */
function hbl_ajax_recreate_directory_page() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Insufficient permissions.', 'happy-business-listing'));
    }
    
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hbl_recreate_page')) {
        wp_send_json_error(__('Security check failed.', 'happy-business-listing'));
    }
    
    $page_id = hbl_recreate_directory_page();
    
    if ($page_id) {
        wp_send_json_success(array(
            'message' => __('Business directory page recreated successfully.', 'happy-business-listing'),
            'page_url' => get_permalink($page_id),
            'edit_url' => get_edit_post_link($page_id)
        ));
    } else {
        wp_send_json_error(__('Failed to create directory page.', 'happy-business-listing'));
    }
}
add_action('wp_ajax_hbl_recreate_directory_page', 'hbl_ajax_recreate_directory_page');

/**
 * Get directory page status for settings display
 */
function hbl_get_directory_page_status() {
    $page_id = get_option('hbl_directory_page_id');
    
    if (!$page_id) {
        return array(
            'status' => 'missing',
            'message' => __('No directory page set.', 'happy-business-listing'),
            'class' => 'error'
        );
    }
    
    $page = get_post($page_id);
    
    if (!$page) {
        return array(
            'status' => 'deleted',
            'message' => __('Directory page was deleted.', 'happy-business-listing'),
            'class' => 'error'
        );
    }
    
    if ($page->post_status !== 'publish') {
        return array(
            'status' => 'draft',
            'message' => sprintf(__('Directory page exists but is not published. Status: %s', 'happy-business-listing'), $page->post_status),
            'class' => 'warning',
            'page_id' => $page_id
        );
    }
    
    return array(
        'status' => 'active',
        'message' => sprintf(__('Directory page is active: %s', 'happy-business-listing'), get_the_title($page_id)),
        'class' => 'success',
        'page_id' => $page_id,
        'page_url' => get_permalink($page_id),
        'edit_url' => get_edit_post_link($page_id)
    );
}
?> 