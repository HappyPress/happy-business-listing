<?php
/**
 * Template System for Happy Business Listing
 * 
 * Handles template loading, overrides, and template-related functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load plugin templates
 *
 * @param string $template The template path
 * @return string The template path
 */
function hbl_load_plugin_templates($template) {
    global $post;
    
    // Check if we're on a business listing page
    if (is_singular('business_listing')) {
        $template_path = 'templates/single-business-listing.php';
        $template_file = locate_template($template_path);
        
        if (!$template_file) {
            $template_file = HBL_PLUGIN_DIR . $template_path;
        }
        
        if (file_exists($template_file)) {
            return $template_file;
        }
    }
    
    // Check if we're on a business listing archive page
    if (is_post_type_archive('business_listing')) {
        $template_path = 'templates/archive-business-listing.php';
        $template_file = locate_template($template_path);
        
        if (!$template_file) {
            $template_file = HBL_PLUGIN_DIR . $template_path;
        }
        
        if (file_exists($template_file)) {
            return $template_file;
        }
    }
    
    return $template;
}
add_filter('template_include', 'hbl_load_plugin_templates');

/**
 * Get template part with fallback to plugin templates
 *
 * @param string $slug The slug name for the generic template
 * @param string $name The name of the specialized template
 * @param array $args Additional arguments passed to the template
 */
function hbl_get_template_part($slug, $name = null, $args = array()) {
    // Extract args to make them available in the template
    if (!empty($args) && is_array($args)) {
        extract($args);
    }
    
    // Look for template in theme
    $template = '';
    
    // Format template name
    $template_name = $name ? "{$slug}-{$name}.php" : "{$slug}.php";
    
    // Check theme directory
    $template = locate_template(array(
        "hbl/{$template_name}",
        $template_name
    ));
    
    // Fallback to plugin templates
    if (!$template) {
        $template = HBL_PLUGIN_DIR . "templates/parts/{$template_name}";
        
        if (!file_exists($template)) {
            // Final fallback to default template
            $template = HBL_PLUGIN_DIR . "templates/parts/{$slug}.php";
        }
    }
    
    // Allow filtering of the template path
    $template = apply_filters('hbl_get_template_part', $template, $slug, $name, $args);
    
    // Include the template if it exists
    if (file_exists($template)) {
        include $template;
    }
}

/**
 * Get business social media links as formatted HTML
 *
 * @param int $post_id The post ID (optional)
 * @param array $args Additional arguments
 * @return string Formatted HTML for social media links
 */
function hbl_get_social_media_links($post_id = null, $args = array()) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Default arguments
    $defaults = array(
        'wrapper_class' => 'business-social-links',
        'link_class' => 'social-link',
        'show_labels' => false,
        'target' => '_blank',
        'rel' => 'noopener noreferrer'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Get social media handles
    $social_media = hbl_get_business_field('social_media_handles', $post_id);
    $social_media_array = array();
    
    // If it's a string, try to parse it
    if (is_string($social_media) && !empty($social_media)) {
        // Check if it's JSON
        $json_decoded = json_decode($social_media, true);
        if (is_array($json_decoded)) {
            $social_media_array = $json_decoded;
        } else {
            // Try to parse from string format (e.g., "facebook: url, twitter: url")
            $pairs = explode(',', $social_media);
            foreach ($pairs as $pair) {
                $parts = explode(':', $pair, 2);
                if (count($parts) === 2) {
                    $platform = trim($parts[0]);
                    $url = trim($parts[1]);
                    $social_media_array[$platform] = $url;
                }
            }
        }
    } elseif (is_array($social_media)) {
        $social_media_array = $social_media;
    }
    
    // If no social media links, return empty string
    if (empty($social_media_array)) {
        return '';
    }
    
    // Start building HTML
    $html = '<div class="' . esc_attr($args['wrapper_class']) . '">';
    
    foreach ($social_media_array as $platform => $url) {
        // Skip if URL is empty
        if (empty($url)) {
            continue;
        }
        
        // Format platform name for display
        $platform_display = ucfirst($platform);
        
        // Build link HTML
        $html .= '<a href="' . esc_url($url) . '" class="' . esc_attr($args['link_class']) . ' ' . esc_attr($platform) . '" target="' . esc_attr($args['target']) . '" rel="' . esc_attr($args['rel']) . '">';
        
        // Add platform icon if available
        $icon_class = 'hbl-icon-' . $platform;
        $html .= '<span class="' . esc_attr($icon_class) . '"></span>';
        
        // Add platform name if show_labels is true
        if ($args['show_labels']) {
            $html .= '<span class="platform-name">' . esc_html($platform_display) . '</span>';
        }
        
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get business contact information as formatted HTML
 *
 * @param int $post_id The post ID (optional)
 * @param array $args Additional arguments
 * @return string Formatted HTML for contact information
 */
function hbl_get_contact_info($post_id = null, $args = array()) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Default arguments
    $defaults = array(
        'wrapper_class' => 'business-contact-info',
        'show_labels' => false,
        'show_phone' => true,
        'show_email' => true,
        'show_website' => true,
        'show_whatsapp' => true,
        'show_location' => true
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Get contact information
    $phone = hbl_get_business_field('phone', $post_id);
    $email = hbl_get_business_field('business_email', $post_id);
    $website = hbl_get_business_field('website', $post_id);
    $whatsapp = hbl_get_business_field('whatsapp_number', $post_id);
    $location = hbl_get_business_field('location', $post_id);
    
    // If all fields are empty, return empty string
    if (empty($phone) && empty($email) && empty($website) && empty($whatsapp) && empty($location)) {
        return '';
    }
    
    // Start building HTML
    $html = '<div class="' . esc_attr($args['wrapper_class']) . '">';
    
    // Phone
    if ($args['show_phone'] && !empty($phone)) {
        $html .= '<div class="contact-item phone">';
        if ($args['show_labels']) {
            $html .= '<span class="contact-label">' . __('Phone:', 'happy-business-listing') . '</span> ';
        }
        $html .= '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $phone)) . '">' . esc_html($phone) . '</a>';
        $html .= '</div>';
    }
    
    // Email
    if ($args['show_email'] && !empty($email)) {
        $html .= '<div class="contact-item email">';
        if ($args['show_labels']) {
            $html .= '<span class="contact-label">' . __('Email:', 'happy-business-listing') . '</span> ';
        }
        $html .= '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
        $html .= '</div>';
    }
    
    // Website
    if ($args['show_website'] && !empty($website)) {
        $html .= '<div class="contact-item website">';
        if ($args['show_labels']) {
            $html .= '<span class="contact-label">' . __('Website:', 'happy-business-listing') . '</span> ';
        }
        $html .= '<a href="' . esc_url($website) . '" target="_blank" rel="noopener noreferrer">' . esc_html($website) . '</a>';
        $html .= '</div>';
    }
    
    // WhatsApp
    if ($args['show_whatsapp'] && !empty($whatsapp)) {
        $html .= '<div class="contact-item whatsapp">';
        if ($args['show_labels']) {
            $html .= '<span class="contact-label">' . __('WhatsApp:', 'happy-business-listing') . '</span> ';
        }
        $html .= '<a href="https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $whatsapp)) . '" target="_blank" rel="noopener noreferrer">' . esc_html($whatsapp) . '</a>';
        $html .= '</div>';
    }
    
    // Location
    if ($args['show_location'] && !empty($location)) {
        $html .= '<div class="contact-item location">';
        if ($args['show_labels']) {
            $html .= '<span class="contact-label">' . __('Location:', 'happy-business-listing') . '</span> ';
        }
        $html .= '<span class="location-text">' . esc_html($location) . '</span>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get business details
 *
 * @param int $post_id The post ID
 * @return string The business details HTML
 */
function hbl_get_business_details($post_id) {
    $company_type = hbl_get_business_field($post_id, 'company_type');
    $gst_no = hbl_get_business_field($post_id, 'gst_no');
    $tan_pan = hbl_get_business_field($post_id, 'tan_pan');
    $verification_status = hbl_get_business_field($post_id, 'verification_status');
    $established_date = hbl_get_business_field($post_id, 'established_date');
    
    $html = '<div class="business-details">';
    
    if ($company_type) {
        $html .= sprintf(
            '<div class="business-detail company-type"><strong class="detail-label">%s</strong><span class="detail-value">%s</span></div>',
            esc_html__('Type of Company:', 'happy-business-listing'),
            esc_html($company_type)
        );
    }
    
    if ($gst_no) {
        $html .= sprintf(
            '<div class="business-detail gst-no"><strong class="detail-label">%s</strong><span class="detail-value">%s</span></div>',
            esc_html__('GST No:', 'happy-business-listing'),
            esc_html($gst_no)
        );
    }
    
    if ($tan_pan) {
        $html .= sprintf(
            '<div class="business-detail tan-pan"><strong class="detail-label">%s</strong><span class="detail-value">%s</span></div>',
            esc_html__('TAN/PAN:', 'happy-business-listing'),
            esc_html($tan_pan)
        );
    }
    
    if ($verification_status) {
        $html .= sprintf(
            '<div class="business-detail verification-status"><strong class="detail-label">%s</strong><span class="detail-value status-%s">%s</span></div>',
            esc_html__('Verification Status:', 'happy-business-listing'),
            esc_attr(strtolower($verification_status)),
            esc_html($verification_status)
        );
    }
    
    if ($established_date) {
        $html .= sprintf(
            '<div class="business-detail established-date"><strong class="detail-label">%s</strong><span class="detail-value">%s</span></div>',
            esc_html__('Established:', 'happy-business-listing'),
            esc_html($established_date)
        );
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get business featured image with fallback
 *
 * @param int $post_id The post ID (optional)
 * @param string $size The image size
 * @param array $args Additional arguments
 * @return string HTML for the featured image
 */
function hbl_get_business_image($post_id = null, $size = 'medium', $args = array()) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Default arguments
    $defaults = array(
        'wrapper_class' => 'business-image',
        'image_class' => 'business-logo',
        'default_image' => HBL_PLUGIN_URL . 'assets/img/default-logo.png',
        'alt' => get_the_title($post_id)
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Build HTML
    $html = '<div class="' . esc_attr($args['wrapper_class']) . '">';
    
    if (has_post_thumbnail($post_id)) {
        $html .= get_the_post_thumbnail($post_id, $size, array(
            'class' => $args['image_class'],
            'alt' => $args['alt']
        ));
    } else {
        $html .= '<img src="' . esc_url($args['default_image']) . '" alt="' . esc_attr($args['alt']) . '" class="' . esc_attr($args['image_class']) . '">';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Register and enqueue template styles
 */
function hbl_enqueue_template_styles() {
    // Only enqueue on our post types
    if (!is_singular('business_listing') && !is_post_type_archive('business_listing') && !is_tax('business_category') && !is_tax('business_location')) {
        return;
    }
    
    // Register and enqueue main stylesheet
    wp_register_style(
        'hbl-templates',
        HBL_PLUGIN_URL . 'assets/css/templates.css',
        array(),
        HBL_VERSION
    );
    
    wp_enqueue_style('hbl-templates');
    
    // Enqueue dashicons for social media icons
    wp_enqueue_style('dashicons');
    
    // Add responsive styles
    wp_add_inline_style('hbl-templates', hbl_get_responsive_styles());
}
add_action('wp_enqueue_scripts', 'hbl_enqueue_template_styles');

/**
 * Get responsive styles for templates
 *
 * @return string CSS styles
 */
function hbl_get_responsive_styles() {
    $css = '
        @media screen and (max-width: 768px) {
            .business-listing-container {
                padding: 10px;
            }
            .business-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .business-logo {
                margin-left: 0;
                margin-top: 15px;
            }
            .business-details {
                grid-template-columns: 1fr;
            }
            .business-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        
        @media screen and (max-width: 480px) {
            .business-grid {
                grid-template-columns: 1fr;
            }
        }
    ';
    
    return $css;
}

/**
 * Add filter options to archive page
 */
function hbl_add_archive_filters() {
    // Only add filters on archive page
    if (!is_post_type_archive('business_listing')) {
        return;
    }
    
    // Get filter values from query string
    $company_type = isset($_GET['company_type']) ? sanitize_text_field($_GET['company_type']) : '';
    $location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
    $verification = isset($_GET['verification']) ? sanitize_text_field($_GET['verification']) : '';
    
    // Get unique values for filters
    $company_types = hbl_get_unique_field_values('company_type');
    $locations = hbl_get_unique_field_values('location');
    
    // Build filter form
    ?>
    <div class="business-filters">
        <form method="get" action="<?php echo esc_url(get_post_type_archive_link('business_listing')); ?>">
            <div class="filter-group">
                <label for="company-type-filter"><?php _e('Company Type:', 'happy-business-listing'); ?></label>
                <select name="company_type" id="company-type-filter">
                    <option value=""><?php _e('All Types', 'happy-business-listing'); ?></option>
                    <?php foreach ($company_types as $type) : ?>
                        <option value="<?php echo esc_attr($type); ?>" <?php selected($company_type, $type); ?>><?php echo esc_html($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="location-filter"><?php _e('Location:', 'happy-business-listing'); ?></label>
                <select name="location" id="location-filter">
                    <option value=""><?php _e('All Locations', 'happy-business-listing'); ?></option>
                    <?php foreach ($locations as $loc) : ?>
                        <option value="<?php echo esc_attr($loc); ?>" <?php selected($location, $loc); ?>><?php echo esc_html($loc); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="verification-filter"><?php _e('Verification:', 'happy-business-listing'); ?></label>
                <select name="verification" id="verification-filter">
                    <option value=""><?php _e('All', 'happy-business-listing'); ?></option>
                    <option value="verified" <?php selected($verification, 'verified'); ?>><?php _e('Verified', 'happy-business-listing'); ?></option>
                    <option value="pending" <?php selected($verification, 'pending'); ?>><?php _e('Pending', 'happy-business-listing'); ?></option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="filter-button"><?php _e('Filter', 'happy-business-listing'); ?></button>
                <a href="<?php echo esc_url(get_post_type_archive_link('business_listing')); ?>" class="reset-button"><?php _e('Reset', 'happy-business-listing'); ?></a>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Get unique values for a field from business listings
 *
 * @param string $field_key The field key
 * @return array Array of unique values
 */
function hbl_get_unique_field_values($field_key) {
    $values = array();
    
    // Query all business listings
    $query = new WP_Query(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));
    
    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            $value = hbl_get_business_field($field_key, $post_id);
            
            if (!empty($value) && !in_array($value, $values)) {
                $values[] = $value;
            }
        }
    }
    
    // Sort values
    sort($values);
    
    return $values;
}

/**
 * Modify query for business listings archive
 *
 * @param WP_Query $query The WP_Query instance
 */
function hbl_modify_business_listings_query($query) {
    // Only modify main query on frontend for business listings
    if (is_admin() || !$query->is_main_query() || !is_post_type_archive('business_listing')) {
        return;
    }
    
    // Get filter values from query string
    $company_type = isset($_GET['company_type']) ? sanitize_text_field($_GET['company_type']) : '';
    $location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
    $verification = isset($_GET['verification']) ? sanitize_text_field($_GET['verification']) : '';
    
    // Set up meta query
    $meta_query = array();
    
    // Add company type filter
    if (!empty($company_type)) {
        $meta_query[] = array(
            'key' => 'company_type',
            'value' => $company_type,
            'compare' => '='
        );
    }
    
    // Add location filter
    if (!empty($location)) {
        $meta_query[] = array(
            'key' => 'location',
            'value' => $location,
            'compare' => 'LIKE'
        );
    }
    
    // Add verification filter
    if (!empty($verification)) {
        $meta_query[] = array(
            'key' => 'verification_status',
            'value' => $verification,
            'compare' => '='
        );
    }
    
    // Apply meta query if we have filters
    if (!empty($meta_query)) {
        $query->set('meta_query', $meta_query);
    }
    
    // Set posts per page
    $posts_per_page = get_option('hbl_archive_posts_per_page', 12);
    $query->set('posts_per_page', $posts_per_page);
    
    // Set orderby and order
    $orderby = get_option('hbl_archive_orderby', 'date');
    $order = get_option('hbl_archive_order', 'DESC');
    
    $query->set('orderby', $orderby);
    $query->set('order', $order);
}
add_action('pre_get_posts', 'hbl_modify_business_listings_query');

/**
 * Create template parts directory and default templates
 */
function hbl_create_template_parts() {
    $template_parts_dir = HBL_PLUGIN_DIR . 'templates/parts';
    
    // Create directory if it doesn't exist
    if (!file_exists($template_parts_dir)) {
        wp_mkdir_p($template_parts_dir);
    }
    
    // Create default template parts if they don't exist
    $template_parts = array(
        'business-card.php' => '<?php
/**
 * Template part for displaying business card
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined(\'ABSPATH\')) {
    exit;
}

$post_id = get_the_ID();
?>
<div class="business-card">
    <a href="<?php the_permalink(); ?>" class="business-link">
        <?php echo hbl_get_business_image($post_id, \'thumbnail\'); ?>
        
        <h2 class="business-title"><?php the_title(); ?></h2>
        
        <?php if ($location = hbl_get_business_field(\'location\', $post_id)) : ?>
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
',
        'business-details.php' => '<?php
/**
 * Template part for displaying business details
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined(\'ABSPATH\')) {
    exit;
}

$post_id = get_the_ID();
?>
<div class="business-details-section">
    <?php echo hbl_get_business_details($post_id); ?>
</div>
',
        'business-contact.php' => '<?php
/**
 * Template part for displaying business contact information
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined(\'ABSPATH\')) {
    exit;
}

$post_id = get_the_ID();
?>
<div class="business-contact-section">
    <h3><?php _e(\'Contact Information\', \'happy-business-listing\'); ?></h3>
    <?php echo hbl_get_contact_info($post_id); ?>
    <?php echo hbl_get_social_media_links($post_id, array(\'show_labels\' => true)); ?>
</div>
',
        'business-filters.php' => '<?php
/**
 * Template part for displaying business filters
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined(\'ABSPATH\')) {
    exit;
}

// Display filters
hbl_add_archive_filters();
'
    );
    
    foreach ($template_parts as $filename => $content) {
        $file_path = $template_parts_dir . '/' . $filename;
        
        if (!file_exists($file_path)) {
            file_put_contents($file_path, $content);
        }
    }
}
add_action('init', 'hbl_create_template_parts');

/**
 * Create taxonomy template if it doesn't exist
 */
function hbl_create_taxonomy_template() {
    $taxonomy_template = HBL_PLUGIN_DIR . 'templates/taxonomy.php';
    
    if (!file_exists($taxonomy_template)) {
        $content = '<?php
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
    
    <?php hbl_get_template_part(\'business-filters\'); ?>
    
    <?php if (have_posts()) : ?>
        <div class="business-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php hbl_get_template_part(\'business-card\'); ?>
            <?php endwhile; ?>
        </div>
        
        <?php the_posts_pagination(array(
            \'prev_text\' => \'&larr; \' . __(\'Previous\', \'happy-business-listing\'),
            \'next_text\' => __(\'Next\', \'happy-business-listing\') . \' &rarr;\',
        )); ?>
    <?php else : ?>
        <p class="no-results"><?php _e(\'No businesses found.\', \'happy-business-listing\'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>';
        
        file_put_contents($taxonomy_template, $content);
    }
}
add_action('init', 'hbl_create_taxonomy_template');

/**
 * Register template settings
 */
function hbl_register_template_settings() {
    // Register settings
    register_setting('hbl_options_group', 'hbl_archive_posts_per_page', 'absint');
    register_setting('hbl_options_group', 'hbl_archive_orderby', 'sanitize_text_field');
    register_setting('hbl_options_group', 'hbl_archive_order', 'sanitize_text_field');
    
    // Add settings section
    add_settings_section(
        'hbl_template_section',
        __('Template Settings', 'happy-business-listing'),
        'hbl_template_section_callback',
        'hbl_options_group'
    );
    
    // Add settings fields
    add_settings_field(
        'hbl_archive_posts_per_page',
        __('Businesses Per Page', 'happy-business-listing'),
        'hbl_archive_posts_per_page_callback',
        'hbl_options_group',
        'hbl_template_section'
    );
    
    add_settings_field(
        'hbl_archive_orderby',
        __('Order By', 'happy-business-listing'),
        'hbl_archive_orderby_callback',
        'hbl_options_group',
        'hbl_template_section'
    );
    
    add_settings_field(
        'hbl_archive_order',
        __('Order', 'happy-business-listing'),
        'hbl_archive_order_callback',
        'hbl_options_group',
        'hbl_template_section'
    );
}
add_action('admin_init', 'hbl_register_template_settings');

/**
 * Template settings section callback
 */
function hbl_template_section_callback() {
    echo '<p>' . __('Configure settings for business listing templates.', 'happy-business-listing') . '</p>';
}

/**
 * Archive posts per page field callback
 */
function hbl_archive_posts_per_page_callback() {
    $value = get_option('hbl_archive_posts_per_page', 12);
    
    echo '<input type="number" name="hbl_archive_posts_per_page" value="' . esc_attr($value) . '" min="1" max="100" step="1">';
    echo '<p class="description">' . __('Number of businesses to display per page on archive pages.', 'happy-business-listing') . '</p>';
}

/**
 * Archive orderby field callback
 */
function hbl_archive_orderby_callback() {
    $value = get_option('hbl_archive_orderby', 'date');
    
    $options = array(
        'date' => __('Date', 'happy-business-listing'),
        'title' => __('Title', 'happy-business-listing'),
        'rand' => __('Random', 'happy-business-listing'),
        'menu_order' => __('Menu Order', 'happy-business-listing')
    );
    
    echo '<select name="hbl_archive_orderby">';
    foreach ($options as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . __('Field to order businesses by on archive pages.', 'happy-business-listing') . '</p>';
}

/**
 * Archive order field callback
 */
function hbl_archive_order_callback() {
    $value = get_option('hbl_archive_order', 'DESC');
    
    echo '<select name="hbl_archive_order">';
    echo '<option value="DESC" ' . selected($value, 'DESC', false) . '>' . __('Descending', 'happy-business-listing') . '</option>';
    echo '<option value="ASC" ' . selected($value, 'ASC', false) . '>' . __('Ascending', 'happy-business-listing') . '</option>';
    echo '</select>';
    echo '<p class="description">' . __('Order direction for businesses on archive pages.', 'happy-business-listing') . '</p>';
}