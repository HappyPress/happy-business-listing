# Template System Documentation

The Happy Business Listing plugin includes a comprehensive template system that allows you to customize how business listings are displayed on your website. This document provides detailed information about the template system, its features, and how to customize it.

## Overview

The template system includes:

1. **Template Files**: Core templates for displaying business listings
2. **Template Parts**: Reusable components for displaying specific parts of business listings
3. **Template Functions**: Helper functions for displaying business data
4. **Template Overrides**: A system for overriding templates in your theme
5. **CSS Styling**: Responsive styles for business listings

## Template Files

The plugin includes the following main template files:

- `single-business_listing.php`: Displays a single business listing
- `archive-business_listing.php`: Displays a list of business listings
- `taxonomy.php`: Displays business listings for a specific taxonomy term

## Template Parts

Template parts are reusable components that can be included in templates:

- `business-card.php`: Displays a business card in grid layouts
- `business-details.php`: Displays business details
- `business-contact.php`: Displays business contact information
- `business-filters.php`: Displays filters for business listings

## Template Functions

The plugin provides several helper functions for displaying business data:

### `hbl_get_template_part($slug, $name = null, $args = array())`

Loads a template part with fallback to plugin templates.

**Parameters:**
- `$slug` (string): The slug name for the generic template
- `$name` (string, optional): The name of the specialized template
- `$args` (array, optional): Additional arguments passed to the template

**Example:**
```php
// Load the business-card template part
hbl_get_template_part('business-card');

// Load the business-card-featured template part
hbl_get_template_part('business-card', 'featured');

// Load the business-card template part with arguments
hbl_get_template_part('business-card', null, array(
    'show_excerpt' => false,
    'show_location' => true
));
```

### `hbl_get_business_field($field_key, $post_id = null, $default = '')`

Gets a business field value with proper fallback.

**Parameters:**
- `$field_key` (string): The field key to retrieve
- `$post_id` (int, optional): The post ID
- `$default` (mixed, optional): Default value if field is empty

**Example:**
```php
// Get the business name
$business_name = hbl_get_business_field('business_name');

// Get the location with a default value
$location = hbl_get_business_field('location', null, 'Unknown Location');
```

### `hbl_get_social_media_links($post_id = null, $args = array())`

Gets business social media links as formatted HTML.

**Parameters:**
- `$post_id` (int, optional): The post ID
- `$args` (array, optional): Additional arguments

**Example:**
```php
// Display social media links
echo hbl_get_social_media_links();

// Display social media links with labels
echo hbl_get_social_media_links(null, array(
    'show_labels' => true
));
```

### `hbl_get_contact_info($post_id = null, $args = array())`

Gets business contact information as formatted HTML.

**Parameters:**
- `$post_id` (int, optional): The post ID
- `$args` (array, optional): Additional arguments

**Example:**
```php
// Display contact information
echo hbl_get_contact_info();

// Display only phone and email
echo hbl_get_contact_info(null, array(
    'show_website' => false,
    'show_whatsapp' => false,
    'show_location' => false
));
```

### `hbl_get_business_details($post_id = null, $args = array())`

Gets business details as formatted HTML.

**Parameters:**
- `$post_id` (int, optional): The post ID
- `$args` (array, optional): Additional arguments

**Example:**
```php
// Display business details
echo hbl_get_business_details();

// Display business details with custom fields
echo hbl_get_business_details(null, array(
    'custom_fields' => array(
        'established_year' => 'Established',
        'business_hours' => 'Business Hours'
    )
));
```

### `hbl_get_business_image($post_id = null, $size = 'medium', $args = array())`

Gets business featured image with fallback.

**Parameters:**
- `$post_id` (int, optional): The post ID
- `$size` (string, optional): The image size
- `$args` (array, optional): Additional arguments

**Example:**
```php
// Display business image
echo hbl_get_business_image();

// Display business image with custom size and class
echo hbl_get_business_image(null, 'thumbnail', array(
    'image_class' => 'custom-logo-class'
));
```

## Template Overrides

You can override the plugin templates in your theme by creating files with the same name in your theme directory. The plugin will look for templates in the following locations:

1. `{your-theme}/hbl/{template-name}.php`
2. `{your-theme}/{template-name}.php`
3. Plugin templates (fallback)

For example, to override the single business listing template, create a file at:
`{your-theme}/hbl/single-business_listing.php`

## CSS Styling

The plugin includes responsive styles for business listings. You can customize these styles by adding your own CSS in your theme's stylesheet.

The main CSS classes used by the plugin are:

- `.business-listing-container`: Container for single business listing
- `.business-listing`: Business listing wrapper
- `.business-header`: Business header section
- `.business-title`: Business title
- `.business-image`: Business image wrapper
- `.business-logo`: Business logo image
- `.business-details`: Business details wrapper
- `.business-detail`: Individual business detail
- `.business-content`: Business content
- `.business-contact-section`: Business contact section
- `.business-social-links`: Business social media links
- `.business-listing-archive`: Container for business listing archive
- `.business-grid`: Grid of business cards
- `.business-card`: Individual business card
- `.business-filters`: Business filters section

## Filters

The plugin provides several filters for customizing the template system:

### `hbl_get_template_part`

Filters the template part path.

**Parameters:**
- `$template` (string): The template path
- `$slug` (string): The slug name for the generic template
- `$name` (string): The name of the specialized template
- `$args` (array): Additional arguments

**Example:**
```php
add_filter('hbl_get_template_part', 'my_custom_template_part', 10, 4);
function my_custom_template_part($template, $slug, $name, $args) {
    // Modify template path
    if ($slug === 'business-card' && $name === 'featured') {
        return get_stylesheet_directory() . '/custom-templates/featured-card.php';
    }
    return $template;
}
```

### `hbl_formatted_price`

Filters the formatted price.

**Parameters:**
- `$formatted` (string): The formatted price
- `$price` (float): The original price
- `$currency` (string): The currency code

**Example:**
```php
add_filter('hbl_formatted_price', 'my_custom_price_format', 10, 3);
function my_custom_price_format($formatted, $price, $currency) {
    // Modify price format
    if ($currency === 'USD') {
        return 'USD ' . number_format($price, 2);
    }
    return $formatted;
}
```

## Template Settings

The plugin includes several settings for customizing the template system:

- **Businesses Per Page**: Number of businesses to display per page on archive pages
- **Order By**: Field to order businesses by on archive pages
- **Order**: Order direction for businesses on archive pages

These settings can be configured in the plugin settings page under the "Template Settings" section.

## Best Practices

1. **Use Template Parts**: Use template parts to keep your templates modular and reusable
2. **Override Templates in Theme**: Override templates in your theme rather than modifying the plugin files
3. **Use Helper Functions**: Use the provided helper functions to display business data
4. **Add Custom CSS in Theme**: Add custom CSS in your theme's stylesheet rather than modifying the plugin CSS
5. **Use Filters**: Use filters to customize the template system without modifying the plugin files

## Examples

### Customizing the Business Card Template

```php
// In your theme's functions.php
add_filter('hbl_get_template_part', 'my_custom_business_card', 10, 4);
function my_custom_business_card($template, $slug, $name, $args) {
    if ($slug === 'business-card') {
        return get_stylesheet_directory() . '/hbl/business-card.php';
    }
    return $template;
}
```

### Adding Custom Fields to Business Details

```php
// In your theme's functions.php
add_filter('hbl_business_details', 'my_custom_business_details', 10, 2);
function my_custom_business_details($html, $post_id) {
    // Get custom field value
    $established = hbl_get_business_field('established_year', $post_id);
    
    if (!empty($established)) {
        // Add custom field to HTML
        $custom_field = '<div class="business-detail established-year">';
        $custom_field .= '<strong class="detail-label">Established:</strong>';
        $custom_field .= '<span class="detail-value">' . esc_html($established) . '</span>';
        $custom_field .= '</div>';
        
        // Insert before closing div
        $html = str_replace('</div>', $custom_field . '</div>', $html);
    }
    
    return $html;
}
```

### Customizing the Archive Template

Create a file at `{your-theme}/hbl/archive-business_listing.php`:

```php
<?php
/**
 * Custom archive template for business listings
 */

get_header();
?>

<div class="custom-business-archive">
    <h1 class="archive-title"><?php _e('Our Business Directory', 'my-theme'); ?></h1>
    
    <?php hbl_get_template_part('business-filters'); ?>
    
    <?php if (have_posts()) : ?>
        <div class="custom-business-grid">
            <?php while (have_posts()) : the_post(); ?>
                <div class="custom-business-card">
                    <a href="<?php the_permalink(); ?>">
                        <?php echo hbl_get_business_image(); ?>
                        <h2><?php the_title(); ?></h2>
                        <p><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p>No businesses found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
```