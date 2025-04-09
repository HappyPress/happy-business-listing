<?php
/**
 * The template for displaying single business listings
 *
 * @package Happy_Business_Listing
 */

get_header();
?>

<div class="business-listing-container">
    <?php while (have_posts()) : the_post(); ?>
        <article id="business-<?php the_ID(); ?>" <?php post_class('business-listing'); ?>>
            <header class="business-header">
                <h1 class="business-title"><?php the_title(); ?></h1>
                
                <?php 
                // Display business logo/image
                echo hbl_get_business_image(get_the_ID(), 'medium'); 
                ?>
            </header>

            <?php if ($verification_status = hbl_get_business_field('verification_status')) : ?>
                <div class="verification-badge status-<?php echo sanitize_html_class($verification_status); ?>">
                    <?php 
                    $status_text = '';
                    switch ($verification_status) {
                        case 'verified':
                            $status_text = __('Verified Business', 'happy-business-listing');
                            break;
                        case 'pending':
                            $status_text = __('Verification Pending', 'happy-business-listing');
                            break;
                        case 'rejected':
                            $status_text = __('Verification Failed', 'happy-business-listing');
                            break;
                        default:
                            $status_text = ucfirst($verification_status);
                    }
                    echo esc_html($status_text); 
                    ?>
                </div>
            <?php endif; ?>

            <?php 
            // Display business details
            echo hbl_get_business_details(get_the_ID(), array(
                'show_verification' => false, // Already shown above
                'custom_fields' => array(
                    'established_year' => __('Established', 'happy-business-listing'),
                    'business_hours' => __('Business Hours', 'happy-business-listing'),
                    'employee_count' => __('Number of Employees', 'happy-business-listing')
                )
            )); 
            ?>

            <div class="business-content">
                <?php the_content(); ?>
            </div>

            <?php 
            // Display contact information
            hbl_get_template_part('business-contact'); 
            ?>

            <?php
            // Display services/products if available
            $services = get_posts(array(
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

            if (!empty($services)) : 
            ?>
                <div class="business-services">
                    <h2><?php _e('Services & Products', 'happy-business-listing'); ?></h2>
                    
                    <div class="services-grid">
                        <?php foreach ($services as $service) : ?>
                            <div class="service-item">
                                <h3 class="service-title"><?php echo esc_html($service->post_title); ?></h3>
                                
                                <?php if ($price = hbl_get_business_field('price', $service->ID)) : ?>
                                    <div class="service-price">
                                        <?php echo esc_html(hbl_format_price($price)); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="service-description">
                                    <?php echo wp_kses_post(wpautop($service->post_content)); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php
            // Display related businesses if available
            $related_args = array(
                'post_type' => 'business_listing',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
                'orderby' => 'rand'
            );
            
            // If we have a company type, use it to find related businesses
            if ($company_type = hbl_get_business_field('company_type')) {
                $related_args['meta_query'] = array(
                    array(
                        'key' => 'company_type',
                        'value' => $company_type,
                        'compare' => '='
                    )
                );
            }
            
            $related_businesses = get_posts($related_args);
            
            if (!empty($related_businesses)) : 
            ?>
                <div class="related-businesses">
                    <h2><?php _e('Similar Businesses', 'happy-business-listing'); ?></h2>
                    
                    <div class="business-grid">
                        <?php foreach ($related_businesses as $related) : ?>
                            <div class="business-card">
                                <a href="<?php echo esc_url(get_permalink($related->ID)); ?>" class="business-link">
                                    <?php echo hbl_get_business_image($related->ID, 'thumbnail'); ?>
                                    
                                    <h3 class="business-title"><?php echo esc_html($related->post_title); ?></h3>
                                    
                                    <?php if ($location = hbl_get_business_field('location', $related->ID)) : ?>
                                        <div class="business-location">
                                            <span class="dashicons dashicons-location"></span>
                                            <?php echo esc_html($location); ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>