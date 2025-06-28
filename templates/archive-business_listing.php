<?php
/**
 * The template for displaying business listing archives
 *
 * @package Happy_Business_Listing
 */

// Simple approach - just output the content without header/footer
// The theme will handle the page structure
?>

<div class="hbl-archive-container">
    <header class="hbl-archive-header">
        <h1 class="hbl-archive-title"><?php esc_html_e('Business Listings', 'happy-business-listing'); ?></h1>
    </header>

    <?php
    // Check if HSF is enabled
    if (get_option('hbl_use_hsf_filters') == '1' && function_exists('hsf_save_filter')) {
        // Display HSF filters if available
        echo '<div class="hbl-hsf-filter-notice">';
        echo '<p>' . __('Advanced Search & Filter system is enabled.', 'happy-business-listing') . '</p>';
        echo '</div>';
        
        // Try to display HSF shortcode
        if (shortcode_exists('hsf_advanced_search')) {
            echo do_shortcode('[hsf_advanced_search]');
        }
    }
    ?>

    <?php
    // Display the business listings using the existing shortcode
    echo do_shortcode('[business_listing_archive show_filters="true" posts_per_page="12" columns="3"]');
    ?>
</div>

<style>
.hbl-archive-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.hbl-archive-header {
    text-align: center;
    margin-bottom: 40px;
}

.hbl-archive-title {
    font-size: 2.5em;
    margin: 0;
    color: #333;
}

.hbl-hsf-filter-notice {
    background: #f0f8ff;
    border: 1px solid #0073aa;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.hbl-hsf-filter-notice p {
    margin: 0;
}
</style>