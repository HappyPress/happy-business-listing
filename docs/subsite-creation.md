# Sub-site Creation Documentation

The Happy Business Listing plugin includes a powerful feature for automatically creating sub-sites for each business listing in a multisite WordPress environment. This document provides detailed information about this feature, its configuration, and customization options.

## Overview

The sub-site creation feature allows you to:

1. Automatically create a sub-site for each business listing
2. Customize the content of the sub-site with templates
3. Copy content from a template site
4. Apply a specific theme to the sub-site
5. Set up navigation menus and widgets

## Requirements

To use the sub-site creation feature, you need:

- WordPress in multisite mode
- The Happy Business Listing plugin activated network-wide
- Appropriate permissions to create sites

## Configuration

You can configure the sub-site creation feature in the plugin settings under the 'Sub-sites' tab:

### Enabling Sub-site Creation

1. Go to **Business Listings > Settings**
2. Navigate to the **Sub-sites** tab
3. Check the **Automatically create a sub-site for each business listing** option
4. Save changes

### Template Site

You can select an existing site to use as a template for new business sub-sites:

1. Create and set up a model site with the desired pages, content, and structure
2. In the plugin settings, select this site from the **Template Site** dropdown
3. When new sub-sites are created, they will copy the structure and content from this template

### Default Theme

You can specify a default theme for new business sub-sites:

1. In the plugin settings, select a theme from the **Default Theme** dropdown
2. All new sub-sites will use this theme automatically

### Page Templates

You can customize the content templates for the standard pages:

1. In the plugin settings, find the **Page Templates** section
2. Edit the templates for Home, About, Services, and Contact pages
3. Use placeholders like `{business_name}`, `{company_type}`, `{location}`, etc.

## Manual Sub-site Creation

For existing business listings without a sub-site, you can manually create a sub-site:

1. Go to the business listing edit screen
2. Find the **Sub-site Management** meta box in the sidebar
3. Click the **Create Sub-site** button

## Recreating Pages

If you need to reset or recreate the pages on an existing business sub-site:

1. Go to the business listing edit screen
2. Find the **Sub-site Management** meta box in the sidebar
3. Click the **Recreate Pages** button

## Developer Information

### Functions

#### `hbl_create_business_subsite($post_id, $user_id, $business_name, $username)`

Creates a sub-site for a business listing.

**Parameters:**
- `$post_id` (int): The business listing post ID
- `$user_id` (int): The user ID of the business owner
- `$business_name` (string): The name of the business
- `$username` (string): The username for the site URL

**Returns:**
- (int|WP_Error): Site ID on success, WP_Error on failure

#### `hbl_setup_business_site($post_id, $business_name)`

Sets up a business sub-site with pages and content.

**Parameters:**
- `$post_id` (int): The business listing post ID
- `$business_name` (string): The name of the business

#### `hbl_copy_template_content($template_id, $site_id, $post_id)`

Copies content from a template site to a new business site.

**Parameters:**
- `$template_id` (int): The template site ID
- `$site_id` (int): The new site ID
- `$post_id` (int): The business listing post ID

### Hooks

#### Actions

- `hbl_after_subsite_creation`: Fires after a sub-site is created
  - Parameters: `$site_id`, `$post_id`, `$user_id`

- `hbl_after_subsite_setup`: Fires after a sub-site is set up with pages and content
  - Parameters: `$site_id`, `$post_id`

#### Filters

- `hbl_subsite_title`: Filter the title of the new sub-site
  - Parameters: `$site_title`, `$post_id`, `$user_id`

### AJAX Endpoints

- `hbl_create_site_manually`: Creates a sub-site manually from the admin interface
- `hbl_recreate_pages`: Recreates pages on an existing sub-site

## Customization Examples

### Customizing the Sub-site Title

```php
function my_custom_subsite_title($site_title, $post_id, $user_id) {
    // Get additional information
    $location = hbl_get_field('location', $post_id);
    
    // Add location to site title if available
    if (!empty($location)) {
        $site_title .= ' - ' . $location;
    }
    
    return $site_title;
}
add_filter('hbl_subsite_title', 'my_custom_subsite_title', 10, 3);
```

### Adding Custom Actions After Sub-site Creation

```php
function my_after_subsite_creation($site_id, $post_id, $user_id) {
    // Switch to the new site
    switch_to_blog($site_id);
    
    // Install and activate a plugin
    $plugin = 'contact-form-7/wp-contact-form-7.php';
    if (!is_plugin_active($plugin)) {
        activate_plugin($plugin);
    }
    
    // Create a contact form
    // ...
    
    // Switch back to the main site
    restore_current_blog();
    
    // Send notification email
    $admin_email = get_option('admin_email');
    $subject = 'New business sub-site created';
    $message = 'A new business sub-site has been created: ' . get_site_url($site_id);
    wp_mail($admin_email, $subject, $message);
}
add_action('hbl_after_subsite_creation', 'my_after_subsite_creation', 10, 3);
```

## Troubleshooting

### Sub-site Creation Fails

1. Verify that WordPress Multisite is properly configured
2. Check that the user has sufficient permissions
3. Ensure the domain and path are valid
4. Check the error logs if logging is enabled

### Pages Not Created Correctly

1. Verify that the page templates are properly formatted
2. Check for missing placeholders or syntax errors
3. Use the "Recreate Pages" button to reset the pages

### Template Site Content Not Copied

1. Verify that the template site exists and is accessible
2. Check that the template site has published pages
3. Ensure the template site has a valid menu structure

## Best Practices

1. Create a comprehensive template site before enabling automatic sub-site creation
2. Use placeholders consistently in page templates
3. Test the sub-site creation process in a staging environment first
4. Regularly back up your database before making changes to sub-site settings
5. Use hooks to extend functionality rather than modifying core files