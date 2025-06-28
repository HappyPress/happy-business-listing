<?php
/**
 * The template for displaying single business listings
 *
 * @package Happy_Business_Listing
 */

get_header();
?>

<div class="hbl-business-listing-container">
    <div class="hbl-business-listing">
        <div class="hbl-business-header">
            <h1 class="hbl-business-title"><?php the_title(); ?></h1>
            
            <?php
            // Get business meta
            $company_type = get_post_meta(get_the_ID(), 'company_type', true);
            $location = get_post_meta(get_the_ID(), 'location', true);
            $rating = get_post_meta(get_the_ID(), 'rating', true);
            $verified = get_post_meta(get_the_ID(), 'verified', true);
            $featured = get_post_meta(get_the_ID(), 'featured', true);
            ?>
            
            <?php if ($company_type || $location) : ?>
                <div class="hbl-business-meta">
                    <?php if ($company_type) : ?>
                        <span class="hbl-business-type"><?php echo esc_html($company_type); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($location) : ?>
                        <span class="hbl-business-location"><?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($rating) : ?>
                        <div class="hbl-business-rating">
                            <span class="hbl-rating-stars">
                                <?php echo str_repeat('★', round($rating)) . str_repeat('☆', 5 - round($rating)); ?>
                            </span>
                            <span class="hbl-rating-value"><?php echo esc_html($rating); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($verified) : ?>
                        <span class="hbl-business-verified-badge" title="<?php esc_attr_e('Verified Business', 'happy-business-listing'); ?>">
                            <?php _e('Verified', 'happy-business-listing'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="hbl-business-image">
                <?php 
                if (has_post_thumbnail()) {
                    the_post_thumbnail('large', array('class' => 'hbl-business-thumbnail'));
                } else {
                    echo '<div class="hbl-business-thumbnail-placeholder"></div>';
                }
                ?>
            </div>
        </div>
        
        <div class="hbl-business-content-wrapper">
            <div class="hbl-business-main-content">
                <div class="hbl-business-content">
                    <?php the_content(); ?>
                </div>
                
                <?php
                // Get services/products
                $services_query = new WP_Query(array(
                    'post_type' => 'service_product',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'business_id',
                            'value' => get_the_ID(),
                            'compare' => '='
                        )
                    )
                ));
                
                if ($services_query->have_posts()) :
                ?>
                    <div class="hbl-business-services">
                        <h2 class="hbl-business-services-title"><?php _e('Products & Services', 'happy-business-listing'); ?></h2>
                        
                        <div class="hbl-business-services-grid">
                            <?php while ($services_query->have_posts()) : $services_query->the_post(); ?>
                                <div class="hbl-business-service">
                                    <h3 class="hbl-business-service-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="hbl-business-service-image">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="hbl-business-service-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    
                                    <a href="<?php the_permalink(); ?>" class="hbl-business-service-link">
                                        <?php _e('Learn More', 'happy-business-listing'); ?>
                                    </a>
                                    
                                    <a href="#" class="hbl-business-service-enquiry" data-product-id="<?php the_ID(); ?>" data-product-name="<?php the_title(); ?>">
                                        <?php _e('Send Enquiry', 'happy-business-listing'); ?>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php 
                wp_reset_postdata();
                endif; 
                ?>
                
                <?php
                // Display related businesses
                hbl_get_template_part('related-businesses', null, array(
                    'post_id' => get_the_ID(),
                    'relationship' => 'all',
                    'posts_per_page' => 4,
                    'title' => __('Similar Businesses', 'happy-business-listing')
                ));
                ?>
            </div>
            
            <div class="hbl-business-sidebar">
                <?php
                // Display enhanced business card
                hbl_get_template_part('business-card-enhanced', null, array(
                    'post_id' => get_the_ID(),
                    'show_excerpt' => false,
                    'show_rating' => true,
                    'show_contact' => true,
                    'show_cta' => false
                ));
                
                // Display enquiry form
                hbl_get_template_part('enquiry-form', null, array(
                    'post_id' => get_the_ID(),
                    'title' => __('Contact Business', 'happy-business-listing'),
                    'button_text' => __('Send Enquiry', 'happy-business-listing')
                ));
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    .hbl-business-listing-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .hbl-business-header {
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
    }
    
    .hbl-business-title {
        margin-bottom: 10px;
        font-size: 2.4em;
    }
    
    .hbl-business-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 20px;
        gap: 15px;
    }
    
    .hbl-business-type,
    .hbl-business-location {
        padding: 5px 10px;
        background-color: #f5f5f5;
        border-radius: 4px;
        font-size: 0.9em;
    }
    
    .hbl-business-rating {
        display: flex;
        align-items: center;
    }
    
    .hbl-rating-stars {
        color: #f8d448;
        margin-right: 5px;
    }
    
    .hbl-rating-value {
        font-weight: bold;
    }
    
    .hbl-business-verified-badge {
        background-color: #28a745;
        color: #fff;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8em;
    }
    
    .hbl-business-image {
        flex: 0 0 320px;
        max-width: 100%;
    }
    
    .hbl-business-thumbnail,
    .hbl-business-thumbnail-placeholder {
        width: 100%;
        height: auto;
        border-radius: 8px;
        object-fit: cover;
    }
    
    .hbl-business-thumbnail-placeholder {
        background: #f1f1f1;
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 14px;
    }
    
    .hbl-business-content-wrapper {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }
    
    .hbl-business-content {
        margin-bottom: 30px;
    }
    
    .hbl-business-services {
        margin-bottom: 30px;
    }
    
    .hbl-business-services-title {
        margin-bottom: 20px;
        font-size: 1.5em;
    }
    
    .hbl-business-services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .hbl-business-service {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 15px;
    }
    
    .hbl-business-service-title {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 1.2em;
    }
    
    .hbl-business-service-title a {
        color: #333;
        text-decoration: none;
    }
    
    .hbl-business-service-image {
        margin-bottom: 10px;
    }
    
    .hbl-business-service-image img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .hbl-business-service-excerpt {
        margin-bottom: 15px;
        font-size: 0.9em;
        color: #666;
    }
    
    .hbl-business-service-link,
    .hbl-business-service-enquiry {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9em;
        margin-right: 10px;
    }
    
    .hbl-business-service-link {
        background-color: #f5f5f5;
        color: #333;
    }
    
    .hbl-business-service-enquiry {
        background-color: #0066cc;
        color: #fff;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .hbl-business-content-wrapper {
            grid-template-columns: 1fr;
        }
        
        .hbl-business-services-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }
    
    @media (max-width: 480px) {
        .hbl-business-services-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .hbl-business-sidebar {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Handle service enquiry links
    $('.hbl-business-service-enquiry').on('click', function(e) {
        e.preventDefault();
        
        var productId = $(this).data('product-id');
        var productName = $(this).data('product-name');
        
        // Set product in the enquiry form
        if ($('#hbl-enquiry-form-product').length) {
            $('#hbl-enquiry-form-product').val(productId);
        }
        
        // Update message with product name
        if ($('#hbl-enquiry-form-message').length) {
            $('#hbl-enquiry-form-message').val('I am interested in ' + productName + '. Please provide more information.');
        }
        
        // Scroll to enquiry form
        $('html, body').animate({
            scrollTop: $('.hbl-enquiry-form-container').offset().top - 100
        }, 500);
    });
});
</script>

<?php get_footer(); ?>