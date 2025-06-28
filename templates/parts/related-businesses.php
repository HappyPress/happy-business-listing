<?php
/**
 * Template part for displaying related businesses
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get post ID and args
$post_id = isset($args['post_id']) ? $args['post_id'] : get_the_ID();
$relationship = isset($args['relationship']) ? $args['relationship'] : 'category';
$posts_per_page = isset($args['posts_per_page']) ? $args['posts_per_page'] : 4;
$title = isset($args['title']) ? $args['title'] : __('Related Businesses', 'happy-business-listing');
$layout = isset($args['layout']) ? $args['layout'] : 'grid'; // grid or carousel

// Get related businesses
$related_businesses = hbl_get_related_businesses($post_id, array(
    'posts_per_page' => $posts_per_page,
    'relationship' => $relationship,
));

// If no related businesses, exit
if (empty($related_businesses)) {
    return;
}
?>

<div class="hbl-related-businesses">
    <h3 class="hbl-related-businesses-title"><?php echo esc_html($title); ?></h3>
    
    <div class="hbl-related-businesses-<?php echo esc_attr($layout); ?>">
        <?php foreach ($related_businesses as $business) : ?>
            <div class="hbl-related-business-card">
                <a href="<?php echo esc_url($business['permalink']); ?>" class="hbl-related-business-link">
                    <div class="hbl-related-business-image">
                        <?php if (!empty($business['thumbnail'])) : ?>
                            <img src="<?php echo esc_url($business['thumbnail']); ?>" alt="<?php echo esc_attr($business['title']); ?>" class="hbl-related-business-thumbnail">
                        <?php else : ?>
                            <div class="hbl-related-business-thumbnail-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="hbl-related-business-title"><?php echo esc_html($business['title']); ?></h4>
                    
                    <div class="hbl-related-business-meta">
                        <?php if (!empty($business['company_type'])) : ?>
                            <span class="hbl-related-business-type"><?php echo esc_html($business['company_type']); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($business['location'])) : ?>
                            <span class="hbl-related-business-location"><?php echo esc_html($business['location']); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($business['rating'])) : ?>
                            <div class="hbl-related-business-rating">
                                <span class="hbl-rating-stars" data-rating="<?php echo esc_attr($business['rating']); ?>">
                                    <?php echo str_repeat('★', round($business['rating'])) . str_repeat('☆', 5 - round($business['rating'])); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .hbl-related-businesses {
        margin-top: 40px;
        margin-bottom: 40px;
    }
    
    .hbl-related-businesses-title {
        margin-bottom: 20px;
        font-size: 1.5em;
        font-weight: bold;
    }
    
    .hbl-related-businesses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .hbl-related-business-card {
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hbl-related-business-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .hbl-related-business-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    
    .hbl-related-business-image {
        height: 150px;
        overflow: hidden;
    }
    
    .hbl-related-business-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .hbl-related-business-thumbnail-placeholder {
        width: 100%;
        height: 100%;
        background-color: #f5f5f5;
    }
    
    .hbl-related-business-title {
        padding: 10px 15px;
        margin: 0;
        font-size: 1.1em;
    }
    
    .hbl-related-business-meta {
        padding: 0 15px 15px;
        font-size: 0.9em;
        color: #666;
    }
    
    .hbl-related-business-type,
    .hbl-related-business-location {
        display: block;
        margin-bottom: 5px;
    }
    
    .hbl-related-business-rating {
        margin-top: 8px;
    }
    
    .hbl-rating-stars {
        color: #f8d448;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .hbl-related-businesses-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }
    
    @media (max-width: 480px) {
        .hbl-related-businesses-grid {
            grid-template-columns: 1fr;
        }
    }
</style> 