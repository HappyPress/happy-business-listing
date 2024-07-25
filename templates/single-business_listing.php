<?php
/*
Template Name: Business Listing Template
Template Post Type: business_listing
*/
get_header();
?>

<div class="business-listing-container">
    <?php while (have_posts()) : the_post(); ?>
        <article class="business-listing">
            <header class="business-header">
                <h1 class="business-title"><?php the_title(); ?></h1>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="business-logo">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="business-details">
                <?php
                $fields = [
                    'type_of_company' => 'Type of Company',
                    'gst_no' => 'GST No',
                    'tan_pan' => 'TAN/PAN',
                    'location' => 'Location',
                    'website' => 'Website',
                    'social_media_handles' => 'Social Media Handles',
                    'whatsapp_number' => 'WhatsApp Number'
                ];

                foreach ($fields as $field_key => $field_label) :
                    $field_value = get_field($field_key);
                    if ($field_value) :
                ?>
                    <div class="business-detail">
                        <strong class="detail-label"><?php echo esc_html($field_label); ?>:</strong>
                        <span class="detail-value">
                            <?php
                            if ($field_key === 'website') {
                                echo '<a href="' . esc_url($field_value) . '" target="_blank">' . esc_html($field_value) . '</a>';
                            } elseif ($field_key === 'whatsapp_number') {
                                echo '<a href="https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $field_value)) . '" target="_blank">' . esc_html($field_value) . '</a>';
                            } else {
                                echo esc_html($field_value);
                            }
                            ?>
                        </span>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>

            <div class="business-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<style>
    .business-listing-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .business-listing {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .business-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .business-title {
        flex: 1;
        margin: 0;
    }
    .business-logo {
        margin-left: 20px;
    }
    .business-logo img {
        max-width: 100px;
        height: auto;
        border-radius: 4px;
    }
    .business-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    .business-detail {
        background-color: #ffffff;
        padding: 10px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .detail-label {
        display: block;
        margin-bottom: 5px;
        color: #666;
    }
    .detail-value {
        font-size: 1.1em;
    }
    .business-content {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
</style>

<?php get_footer(); ?>