<?php
/**
 * The template for displaying business taxonomy archives
 *
 * @package Happy_Business_Listing
 */

get_header();

$term = get_queried_object();
?>

<div class="business-listing-archive taxonomy-archive">
    <h1 class="archive-title"><?php echo esc_html($term->name); ?></h1>
    
    <?php if (!empty($term->description)) : ?>
        <div class="term-description">
            <?php echo wp_kses_post($term->description); ?>
        </div>
    <?php endif; ?>
    
    <?php hbl_get_template_part('business-filters'); ?>
    
    <?php if (have_posts()) : ?>
        <div class="business-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php hbl_get_template_part('business-card'); ?>
            <?php endwhile; ?>
        </div>
        
        <?php the_posts_pagination(array(
            'prev_text' => '&larr; ' . __('Previous', 'happy-business-listing'),
            'next_text' => __('Next', 'happy-business-listing') . ' &rarr;',
        )); ?>
    <?php else : ?>
        <p class="no-results"><?php _e('No businesses found.', 'happy-business-listing'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>