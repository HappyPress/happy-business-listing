
# Happy Business Listing Plugin

## Description
The Happy Business Listing Plugin allows you to create a custom business listing directory with various custom post types, Gutenberg blocks, ACF integration, sub-site creation, and WhatsApp integration.

## Version
1.2

## Author
HappyPress, patilswapnilv

## File Structure

```
.
├── happy-business-listing.php
├── includes
│ ├── custom-post-types.php
│ ├── user-and-site.php
│ ├── whatsapp-integration.php
│ ├── form-shortcode.php
│ ├── settings.php
│ ├── templates.php
│ └── gutenberg-blocks.php
├── assets
│ └── js
│ ├── block.js
│ └── block-editor.js
└── templates
├── business-listing-template.php
└── archive-business-listing-template.php
```


## Installation
1. Upload the plugin files to the `/wp-content/plugins/happy-business-listing` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

## How to Use

### 1. Custom Post Types
- **Business Listings**: Add business details such as name, company type, GST no., TAN/PAN, location, website, social media handles, and WhatsApp number.
- **Services and Products**: Business users and administrators can add services and products listings.
- **Leads**: Businesses can add inquiries for services and products.

### 2. Sub-site Creation
- A sub-site is created for each business user that signs up, with a home page displaying business introduction, services, and contact details.

### 3. WhatsApp Integration
- The plugin integrates with WhatsApp Business API and Twilio API to add leads to a WhatsApp group.

### 4. Shortcodes
- `[business_signup_form]`: Displays the business registration form.

### 5. Settings
- Navigate to the plugin settings page under **Settings > Business Listing** to configure:
  - Custom Gutenberg templates for post types and archives.
  - Activate search and filters with a shortcode.
  - Activation and deactivation of custom blocks for each post type and archives.
  - WhatsApp integration settings for Twilio API and WhatsApp Business API.
  - Custom permalinks for post types and archives.
