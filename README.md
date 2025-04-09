
# Happy Business Listing Plugin

The Happy Business Listing Plugin allows you to create a custom business listing directory with various custom post types, Gutenberg blocks, ACF integration, sub-site creation, and WhatsApp integration.

## Features

- **Custom Post Types**
  - Business Listings: Store business details such as name, company type, GST no., TAN/PAN, location, website, social media handles, and WhatsApp number
  - Services and Products: Allow businesses to showcase their offerings
  - Leads: Manage inquiries for services and products

- **User Management**
  - Automatic user creation for business listings
  - Custom business user role with specific capabilities
  - User-business relationship management

- **Sub-site Creation** (for WordPress Multisite)
  - Automatic sub-site creation for each business
  - Custom pages (Home, About, Services, Contact)
  - Pre-configured navigation menu

- **WhatsApp Integration**
  - Support for Twilio API and WhatsApp Business API
  - Lead notifications via WhatsApp
  - Welcome messages for new users

- **Search and Filters**
  - Advanced search functionality
  - Filter by company type, location, etc.
  - AJAX-powered live search

- **Gutenberg Blocks**
  - Business Listings block: Display a grid of businesses
  - Business Search block: Add search and filter functionality
  - Business Details block: Show detailed information about a business

- **Shortcodes**
  - `[business_signup_form]`: Display the business registration form
  - `[business_search]`: Display the search and filter interface

- **Templates**
  - Custom templates for single business listings
  - Custom templates for business listing archives

## Requirements

- WordPress 5.6 or higher
- PHP 7.2 or higher
- Advanced Custom Fields plugin (recommended but not required)
- WordPress Multisite (for sub-site creation feature)

## Installation

1. Upload the `happy-business-listing` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the plugin settings page to configure options

## Usage

### Adding a Business Listing

1. Go to **Business Listings > Add New**
2. Enter the business name and details
3. Set featured image (logo)
4. Publish the listing

When a business listing is published:
- A user account is automatically created
- If multisite is enabled, a sub-site is created
- The business owner receives login credentials via email

### Managing Services and Products

1. Go to **Services & Products > Add New**
2. Enter the service/product details
3. Select the associated business
4. Publish the service/product

### Managing Leads

1. Go to **Leads > Add New**
2. Enter the lead details
3. Select the associated business
4. Publish the lead

### Using Shortcodes

#### Business Signup Form
```
[business_signup_form]
```

#### Business Search
```
[business_search show_filters="true" per_page="10" orderby="date" order="DESC"]
```

### Using Gutenberg Blocks

1. Add a new page or post
2. Click the "+" button to add a block
3. Search for "Business" in the block inserter
4. Choose one of the available blocks:
   - Business Listings
   - Business Search
   - Business Details
5. Configure the block settings in the sidebar

### Plugin Settings

Navigate to **Business Listings > Settings** to configure:

1. **General Settings**
   - Enable/disable search and filters
   - Enable/disable custom blocks
   - Enable/disable error logging

2. **WhatsApp Integration**
   - Select integration type (Twilio or WhatsApp Business)
   - Configure API credentials
   - Set welcome message

3. **Permalinks**
   - Customize permalinks for business listings
   - Customize permalinks for archives

## Developer Documentation

### Hooks and Filters

#### Actions

- `hbl_after_business_registration`: Fires after a business is registered
- `hbl_after_subsite_creation`: Fires after a sub-site is created
- `hbl_after_lead_creation`: Fires after a lead is created

#### Filters

- `hbl_business_fields`: Filter the business listing fields
- `hbl_service_fields`: Filter the service/product fields
- `hbl_lead_fields`: Filter the lead fields
- `hbl_whatsapp_message`: Filter the WhatsApp message before sending
- `hbl_registration_email`: Filter the registration email content

### Helper Functions

- `hbl_get_field($field_name, $post_id)`: Get a field value (works with or without ACF)
- `hbl_update_field($field_name, $value, $post_id)`: Update a field value
- `hbl_send_whatsapp_message($message, $to, $attachments)`: Send a WhatsApp message
- `hbl_get_businesses($args)`: Get business listings with custom arguments

### Custom Templates

To override the default templates, copy the template files from the plugin's `templates` directory to your theme's directory with the same file structure:

- `single-business_listing.php`: Template for single business listings
- `archive-business_listing.php`: Template for business listing archives

## Frequently Asked Questions

### Does this plugin require ACF?

No, but it's recommended. The plugin will work without ACF by using custom meta boxes as a fallback.

### Can I use this plugin without WordPress Multisite?

Yes, the sub-site creation feature will be disabled, but all other features will work normally.

### How do I customize the business registration form?

You can use the `hbl_business_fields` filter to add, remove, or modify fields in the registration form.

### Can I customize the WhatsApp messages?

Yes, use the `hbl_whatsapp_message` filter to customize the message content before it's sent.

## Changelog

### 1.3.0
- Added ACF dependency check and fallback
- Improved sub-site creation with custom pages and menus
- Added WhatsApp integration with Twilio and WhatsApp Business API
- Added search and filters functionality
- Added Gutenberg blocks for business listings, search, and details
- Added user role management for business owners
- Added error logging and debugging tools

### 1.2.0
- Added custom post types for services/products and leads
- Added basic WhatsApp integration
- Added business registration form shortcode
- Added settings page

### 1.1.0
- Added custom post type for business listings
- Added basic templates for single and archive views

### 1.0.0
- Initial release

## Credits

- Developed by HappyPress
- Contributors: patilswapnilv

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please visit [HappyPress Support](https://happypress.com/support) or email support@happypress.com.
