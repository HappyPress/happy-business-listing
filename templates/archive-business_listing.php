<?php
get_header();
?>

<div class="business-listing-archive">
    <h1 class="archive-title">Business Listings</h1>
    <?php if (have_posts()) : ?>
        <div class="business-grid">
            <?php while (have_posts()) : the_post(); ?>
                <div class="business-card">
                <a href="<?php the_permalink(); ?>" class="business-link">
                    <div class="business-image">
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('thumbnail', array('class' => 'business-logo'));
                        } else {
                            echo '<img src="' . plugin_dir_url(__FILE__) . '../assets/default-logo.png" alt="Default Logo" class="business-logo">';
                        }
                        ?>
                    </div>
                    <h2 class="business-title"><?php the_title(); ?></h2>
                    <div class="business-excerpt">
                        <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                    </div>
                </a>
                </div>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p class="no-results">No businesses found.</p>
    <?php endif; ?>
</div>

<style>
    .business-listing-archive {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .archive-title {
        text-align: center;
        margin-bottom: 30px;
    }
    .business-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    .business-card {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .business-card:hover {
        transform: translateY(-5px);
    }
    .business-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    .business-image {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffffff;
    }
    .business-logo {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    }