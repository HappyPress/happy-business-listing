<?php
/**
 * The template for displaying business listing archives
 *
 * @package Happy_Business_Listing
 */
?>

<div class="hbl-archive-container">
    <header class="hbl-archive-header">
        <h1 class="hbl-archive-title"><?php esc_html_e('Business Listings', 'happy-business-listing'); ?></h1>
    </header>

    <?php
    // Check if HSF is enabled and available
    $hsf_enabled = (get_option('hbl_use_hsf_filters') == '1' && function_exists('hsf_save_filter'));
    
    if ($hsf_enabled) {
        // Display HSF notice and try to render HSF filters
        echo '<div class="hbl-hsf-filter-notice">';
        echo '<p><strong>' . __('Advanced Search & Filter system is active.', 'happy-business-listing') . '</strong></p>';
        echo '<p>' . __('Using enhanced search and filtering capabilities.', 'happy-business-listing') . '</p>';
        echo '</div>';
        
        // Try to display HSF shortcode
        if (shortcode_exists('hsf_advanced_search')) {
            echo '<div class="hbl-hsf-filter-wrapper">';
            echo do_shortcode('[hsf_advanced_search]');
            echo '</div>';
        }
    }
    ?>

    <?php
    // Display the business listings using the existing shortcode
    // The shortcode will handle showing/hiding its own filters based on HSF status
    echo do_shortcode('[business_listing_archive show_filters="true" posts_per_page="12" columns="3"]');
    ?>
</div>

<style>
.hbl-archive-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.hbl-archive-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.hbl-archive-title {
    font-size: 2.5em;
    margin: 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hbl-hsf-filter-notice {
    background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
    border: 1px solid #0073aa;
    border-left: 4px solid #0073aa;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,115,170,0.1);
}

.hbl-hsf-filter-notice p {
    margin: 0 0 8px 0;
    line-height: 1.6;
}

.hbl-hsf-filter-notice p:last-child {
    margin-bottom: 0;
}

.hbl-hsf-filter-wrapper {
    margin-bottom: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

/* Improve the default business listing archive styles */
.hbl-business-archive {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.hbl-business-archive .business-filters {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 0;
    border-radius: 0;
}

.hbl-business-archive .business-results {
    padding: 30px;
}

.hbl-business-archive .business-grid {
    gap: 30px;
}

.hbl-business-archive .business-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.hbl-business-archive .business-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.hbl-business-archive .results-info {
    background: #f8f9fa;
    padding: 15px 20px;
    margin: -30px -30px 30px -30px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.hbl-business-archive .no-results {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 60px 40px;
    text-align: center;
}

.hbl-business-archive .no-results h3 {
    color: #495057;
    margin-bottom: 15px;
}

.hbl-business-archive .no-results .button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.hbl-business-archive .no-results .button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .hbl-archive-container {
        padding: 15px;
    }
    
    .hbl-archive-title {
        font-size: 2em;
    }
    
    .hbl-archive-header {
        padding: 30px 20px;
        margin-bottom: 30px;
    }
    
    .hbl-hsf-filter-notice {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .hbl-business-archive .business-results {
        padding: 20px;
    }
    
    .hbl-business-archive .results-info {
        margin: -20px -20px 20px -20px;
    }
}

@media (max-width: 480px) {
    .hbl-archive-title {
        font-size: 1.8em;
    }
    
    .hbl-business-archive .business-grid {
        gap: 20px;
    }
}
</style>