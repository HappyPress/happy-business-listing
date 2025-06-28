<?php
/**
 * The template for displaying business listing archives
 *
 * @package Happy_Business_Listing
 */

echo '<section class="hbl-archive-hero"><h1>'.esc_html__('Business Listings','happy-business-listing').'</h1></section>';
echo do_shortcode('[business_listing_archive show_filters="true" posts_per_page="12" columns="3"]');

// simple inline hero styles (can be moved to css)
echo '<style>.hbl-archive-hero{max-width:1200px;margin:60px auto 30px;padding:0 20px;text-align:center}.hbl-archive-hero h1{font-size:2.8em;margin:0;color:#333}</style>';

get_footer();
?>