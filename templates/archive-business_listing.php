<?php
/**
 * The template for displaying business listing archives
 *
 * @package Happy_Business_Listing
 */

// For block themes, we need to output content that can be inserted into the theme's structure
// This template should focus on content only, not page structure

// Start output buffering to ensure clean output
ob_start();

// Check if this is a block theme
$is_block_theme = wp_is_block_theme();

// If it's a block theme, we'll wrap our content properly
if ($is_block_theme) {
    // Get the theme's header template part
    block_template_part('header');
    
    echo '<main class="wp-block-group alignfull" style="margin-top:var(--wp--preset--spacing--60,4rem)">';
    echo '<div class="wp-block-group alignwide" style="padding-left:var(--wp--preset--spacing--40,2rem);padding-right:var(--wp--preset--spacing--40,2rem)">';
}

// Archive header
?>
<div class="hbl-archive-header alignwide" style="margin-bottom:var(--wp--preset--spacing--50,3rem)">
    <h1 class="wp-block-heading has-text-align-center has-x-large-font-size"><?php esc_html_e('Business Listings', 'happy-business-listing'); ?></h1>
</div>

<?php
// Check if HSF is enabled
if (get_option('hbl_use_hsf_filters') == '1' && function_exists('hsf_save_filter')) {
    // Check if there's already an HSF block on the page
    global $post;
    $has_hsf_block = false;
    
    if ($post && has_blocks($post->post_content)) {
        $blocks = parse_blocks($post->post_content);
        foreach ($blocks as $block) {
            if (isset($block['blockName']) && $block['blockName'] === 'happy-search-and-filter/advanced-search') {
                $has_hsf_block = true;
                break;
            }
        }
    }
    
    // If no HSF block found, display one automatically
    if (!$has_hsf_block) {
        ?>
        <div class="wp-block-group has-global-padding is-layout-constrained" style="margin-bottom:var(--wp--preset--spacing--40,2rem)">
            <div class="hbl-admin-notice has-accent-background-color has-background" style="padding:var(--wp--preset--spacing--30,1.5rem);border-radius:var(--wp--custom--border-radius--small,0.25rem)">
                <p class="has-small-font-size"><strong><?php _e('Advanced Search Filters Active', 'happy-business-listing'); ?></strong></p>
                <p class="has-small-font-size"><?php _e('The Advanced Search & Filter system is enabled. Add the "Advanced Search" block to this page for the best experience.', 'happy-business-listing'); ?></p>
            </div>
        </div>
        <?php
        
        // Display the HSF filters
        echo '<div class="wp-block-group alignwide hbl-filter-wrapper" style="margin-bottom:var(--wp--preset--spacing--50,3rem)">';
        echo do_shortcode('[hsf_advanced_search title="' . __('Find Businesses', 'happy-business-listing') . '" showKeywordSearch="true" showLocationFilter="true" showCompanyTypeFilter="true" showCategoryFilter="true" showSorting="true"]');
        echo '</div>';
    }
}

// Display the business listings
echo '<div class="wp-block-group alignwide hbl-listings-wrapper">';
echo do_shortcode('[business_listing_archive show_filters="true" posts_per_page="12" columns="3"]');
echo '</div>';

// If it's a block theme, close the wrapper and add footer
if ($is_block_theme) {
    echo '</div>'; // Close inner wrapper
    echo '</main>'; // Close main
    
    // Get the theme's footer template part
    block_template_part('footer');
} else {
    // For classic themes, use traditional functions
    get_footer();
}

// Get the buffered content
$content = ob_get_clean();

// Output the content
echo $content;
?>