<?php
/**
 * Template part for displaying business card
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
?>
<div class="business-card">
    <a href="<?php the_permalink(); ?>" class="business-link">
        <?php echo hbl_get_business_image($post_id, 'thumbnail'); ?>
        
        <h2 class="business-title"><?php the_title(); ?></h2>
        
        <?php if ($location = hbl_get_business_field('location', $post_id)) : ?>
            <div class="business-location">
                <span class="dashicons dashicons-location"></span>
                <?php echo esc_html($location); ?>
            </div>
        <?php endif; ?>
        
        <div class="business-excerpt">
            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
        </div>
    </a>
</div>