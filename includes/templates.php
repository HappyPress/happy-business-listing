<?php
// Function to get custom template
function hbl_load_plugin_templates($template) {
    if (is_singular('business_listing')) {
        $new_template = plugin_dir_path(__FILE__) . '../templates/single-business_listing.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    } // Load custom template for custom post types
    if (is_post_type_archive('business_listing')) {
        $new_template = plugin_dir_path(__FILE__) . '../templates/archive-business_listing.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'hbl_load_plugin_templates');

?>
