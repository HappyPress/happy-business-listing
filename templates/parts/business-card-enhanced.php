<?php
/**
 * Template part for displaying an enhanced business card
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get post ID and args
$post_id = isset($args['post_id']) ? $args['post_id'] : get_the_ID();
$show_excerpt = isset($args['show_excerpt']) ? $args['show_excerpt'] : true;
$show_rating = isset($args['show_rating']) ? $args['show_rating'] : true;
$show_contact = isset($args['show_contact']) ? $args['show_contact'] : false;
$show_services = isset($args['show_services']) ? $args['show_services'] : false;
$show_cta = isset($args['show_cta']) ? $args['show_cta'] : true;
$cta_text = isset($args['cta_text']) ? $args['cta_text'] : __('View Details', 'happy-business-listing');
$size = isset($args['size']) ? $args['size'] : 'medium'; // small, medium, large

// Get business data
$business_name = get_the_title($post_id);
$company_type = get_post_meta($post_id, 'company_type', true);
$location = get_post_meta($post_id, 'location', true);
$city = get_post_meta($post_id, 'city', true);
$state = get_post_meta($post_id, 'state', true);
$rating = get_post_meta($post_id, 'rating', true);
$verified = get_post_meta($post_id, 'verified', true);
$featured = get_post_meta($post_id, 'featured', true);
$website = get_post_meta($post_id, 'website', true);
$phone = get_post_meta($post_id, 'phone', true);
$whatsapp_number = get_post_meta($post_id, 'whatsapp_number', true);
$email = get_post_meta($post_id, 'email', true);

// Get services
$services = array();
if ($show_services) {
    $services_query = new WP_Query(array(
        'post_type' => 'service_product',
        'posts_per_page' => 3,
        'meta_query' => array(
            array(
                'key' => 'business_id',
                'value' => $post_id,
                'compare' => '='
            )
        )
    ));
    
    if ($services_query->have_posts()) {
        while ($services_query->have_posts()) {
            $services_query->the_post();
            $services[] = array(
                'title' => get_the_title(),
                'permalink' => get_permalink(),
            );
        }
        wp_reset_postdata();
    }
}

// CSS classes
$card_classes = array(
    'hbl-business-card-enhanced',
    'hbl-business-card-' . $size
);

if ($featured) {
    $card_classes[] = 'hbl-business-card-featured';
}

$card_class = implode(' ', $card_classes);
?>

<div class="<?php echo esc_attr($card_class); ?>">
    <div class="hbl-business-card-header">
        <?php if ($featured) : ?>
            <span class="hbl-business-featured-badge"><?php _e('Featured', 'happy-business-listing'); ?></span>
        <?php endif; ?>
        
        <div class="hbl-business-card-image">
            <?php if (has_post_thumbnail($post_id)) : ?>
                <?php echo get_the_post_thumbnail($post_id, 'medium', array('class' => 'hbl-business-card-thumbnail')); ?>
            <?php else : ?>
                <div class="hbl-business-card-thumbnail-placeholder"></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="hbl-business-card-content">
        <h3 class="hbl-business-card-title">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                <?php echo esc_html($business_name); ?>
                <?php if ($verified) : ?>
                    <span class="hbl-business-verified-badge" title="<?php esc_attr_e('Verified Business', 'happy-business-listing'); ?>">✓</span>
                <?php endif; ?>
            </a>
        </h3>
        
        <div class="hbl-business-card-meta">
            <?php if ($company_type) : ?>
                <div class="hbl-business-card-company-type">
                    <span class="hbl-business-card-label"><?php _e('Type:', 'happy-business-listing'); ?></span>
                    <span class="hbl-business-card-value"><?php echo esc_html($company_type); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($location || $city || $state) : ?>
                <div class="hbl-business-card-location">
                    <span class="hbl-business-card-label"><?php _e('Location:', 'happy-business-listing'); ?></span>
                    <span class="hbl-business-card-value">
                        <?php 
                        $location_parts = array_filter(array($location, $city, $state));
                        echo esc_html(implode(', ', $location_parts)); 
                        ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ($show_rating && $rating) : ?>
                <div class="hbl-business-card-rating">
                    <span class="hbl-business-card-label"><?php _e('Rating:', 'happy-business-listing'); ?></span>
                    <span class="hbl-business-card-stars" data-rating="<?php echo esc_attr($rating); ?>">
                        <?php echo str_repeat('★', round($rating)) . str_repeat('☆', 5 - round($rating)); ?>
                        <span class="hbl-business-card-rating-value">(<?php echo esc_html($rating); ?>)</span>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($show_excerpt) : ?>
            <div class="hbl-business-card-excerpt">
                <?php echo wp_trim_words(get_the_excerpt($post_id), 20); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($show_services && !empty($services)) : ?>
            <div class="hbl-business-card-services">
                <span class="hbl-business-card-label"><?php _e('Services:', 'happy-business-listing'); ?></span>
                <ul class="hbl-business-card-services-list">
                    <?php foreach ($services as $service) : ?>
                        <li>
                            <a href="<?php echo esc_url($service['permalink']); ?>">
                                <?php echo esc_html($service['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($show_contact) : ?>
            <div class="hbl-business-card-contact">
                <?php if ($phone) : ?>
                    <div class="hbl-business-card-phone">
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>">
                            <span class="hbl-business-card-contact-icon">📞</span>
                            <?php echo esc_html($phone); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($email) : ?>
                    <div class="hbl-business-card-email">
                        <a href="mailto:<?php echo esc_attr($email); ?>">
                            <span class="hbl-business-card-contact-icon">✉️</span>
                            <?php echo esc_html($email); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($whatsapp_number) : ?>
                    <div class="hbl-business-card-whatsapp">
                        <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $whatsapp_number)); ?>" target="_blank">
                            <span class="hbl-business-card-contact-icon">💬</span>
                            <?php _e('WhatsApp', 'happy-business-listing'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($website) : ?>
                    <div class="hbl-business-card-website">
                        <a href="<?php echo esc_url($website); ?>" target="_blank">
                            <span class="hbl-business-card-contact-icon">🌐</span>
                            <?php _e('Website', 'happy-business-listing'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($show_cta) : ?>
        <div class="hbl-business-card-footer">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="hbl-business-card-cta">
                <?php echo esc_html($cta_text); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .hbl-business-card-enhanced {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .hbl-business-card-enhanced:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .hbl-business-card-featured {
        border-color: #f8d448;
        box-shadow: 0 3px 10px rgba(248, 212, 72, 0.2);
    }
    
    .hbl-business-card-header {
        position: relative;
    }
    
    .hbl-business-featured-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #f8d448;
        color: #333;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: bold;
        z-index: 1;
    }
    
    .hbl-business-card-image {
        height: 180px;
        overflow: hidden;
    }
    
    .hbl-business-card-thumbnail,
    .hbl-business-card-thumbnail-placeholder {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .hbl-business-card-thumbnail-placeholder {
        background-color: #f5f5f5;
    }
    
    .hbl-business-card-content {
        padding: 15px;
        flex-grow: 1;
    }
    
    .hbl-business-card-title {
        margin: 0 0 10px;
        font-size: 1.2em;
        line-height: 1.3;
    }
    
    .hbl-business-card-title a {
        color: #333;
        text-decoration: none;
    }
    
    .hbl-business-verified-badge {
        display: inline-block;
        margin-left: 5px;
        color: #fff;
        background-color: #28a745;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        line-height: 16px;
        text-align: center;
        font-size: 10px;
    }
    
    .hbl-business-card-meta {
        margin-bottom: 10px;
    }
    
    .hbl-business-card-company-type,
    .hbl-business-card-location,
    .hbl-business-card-rating {
        margin-bottom: 5px;
        font-size: 0.9em;
    }
    
    .hbl-business-card-label {
        font-weight: bold;
        color: #555;
        margin-right: 5px;
    }
    
    .hbl-business-card-stars {
        color: #f8d448;
    }
    
    .hbl-business-card-rating-value {
        color: #666;
        margin-left: 5px;
    }
    
    .hbl-business-card-excerpt {
        margin-bottom: 15px;
        font-size: 0.9em;
        color: #666;
        line-height: 1.5;
    }
    
    .hbl-business-card-services {
        margin-bottom: 15px;
    }
    
    .hbl-business-card-services-list {
        margin: 5px 0 0;
        padding-left: 20px;
        font-size: 0.9em;
    }
    
    .hbl-business-card-services-list a {
        color: #0066cc;
        text-decoration: none;
    }
    
    .hbl-business-card-contact {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 15px;
    }
    
    .hbl-business-card-phone,
    .hbl-business-card-email,
    .hbl-business-card-whatsapp,
    .hbl-business-card-website {
        font-size: 0.85em;
    }
    
    .hbl-business-card-contact a {
        color: #333;
        text-decoration: none;
        display: flex;
        align-items: center;
    }
    
    .hbl-business-card-contact-icon {
        margin-right: 5px;
    }
    
    .hbl-business-card-footer {
        padding: 15px;
        border-top: 1px solid #eee;
        text-align: center;
    }
    
    .hbl-business-card-cta {
        display: inline-block;
        padding: 8px 20px;
        background-color: #0066cc;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        transition: background-color 0.2s ease;
    }
    
    .hbl-business-card-cta:hover {
        background-color: #0052a3;
    }
    
    /* Size variants */
    .hbl-business-card-small .hbl-business-card-image {
        height: 120px;
    }
    
    .hbl-business-card-small .hbl-business-card-title {
        font-size: 1em;
    }
    
    .hbl-business-card-large .hbl-business-card-image {
        height: 220px;
    }
    
    .hbl-business-card-large .hbl-business-card-title {
        font-size: 1.4em;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .hbl-business-card-contact {
            grid-template-columns: 1fr;
        }
    }
</style> 