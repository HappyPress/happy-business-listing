# Happy Business Listing Plugin Architecture

This document provides an overview of the Happy Business Listing plugin's architecture and explains how to avoid common issues when extending the plugin.

## File Structure

The plugin is organized into the following directories:

- `includes/`: Contains the core PHP files that implement the plugin's functionality
- `templates/`: Contains the template files for displaying business listings
- `assets/`: Contains CSS, JavaScript, and image files
- `docs/`: Contains documentation files
- `tests/`: Contains test files for unit, integration, and end-to-end testing

## Core Files

The main plugin file `happy-business-listing.php` initializes the plugin and includes all the necessary files. The order of inclusion is important to avoid function name conflicts.

### Key Files in the `includes/` Directory

1. **helpers.php**: Contains helper functions used throughout the plugin
2. **custom-post-types.php**: Defines the custom post types and taxonomies
3. **acf-fields.php**: Integrates with Advanced Custom Fields (ACF) for additional fields
4. **user-registration.php**: Handles user registration and authentication
5. **site-creation.php**: Manages sub-site creation for businesses
6. **whatsapp-integration.php**: Implements WhatsApp integration features
7. **form-shortcode.php**: Provides shortcodes for forms
8. **security.php**: Implements security features
9. **templates.php**: Manages template loading and customization
10. **settings.php**: Handles plugin settings

## Function Naming Conventions

To avoid function name conflicts, we follow these naming conventions:

1. All functions should be prefixed with `hbl_` (Happy Business Listing)
2. Functions in specific files should include a descriptive prefix after `hbl_`:
   - Functions in `custom-post-types.php`: `hbl_cpt_*`
   - Functions in `acf-fields.php`: `hbl_acf_*`
   - Functions in `user-registration.php`: `hbl_user_*`
   - And so on...

## Avoiding Function Name Conflicts

A critical issue that was fixed in the plugin was a function name conflict between `custom-post-types.php` and `acf-fields.php`. Both files defined a function called `hbl_business_details_callback()`, which caused a fatal error when the plugin was activated.

To avoid similar issues in the future:

1. **Always use unique function names**: Follow the naming conventions described above
2. **Check for existing functions**: Before adding a new function, search the entire codebase to ensure the name is not already in use
3. **Use namespaces or classes**: Consider organizing related functionality into classes or namespaces to avoid global function name conflicts
4. **Document function relationships**: If a function in one file is related to or extends a function in another file, document this relationship in the function's DocBlock

## File Loading Order

The order in which files are loaded is important to avoid conflicts. The current loading order is:

1. `helpers.php` (loaded first because it contains functions used by other files)
2. `custom-post-types.php` (defines the basic post types)
3. `acf-fields.php` (extends the post types with additional fields)
4. Other files...

If you need to modify this order, be careful to avoid introducing new conflicts.

## Best Practices for Extending the Plugin

When extending the plugin:

1. **Create a new file**: Instead of modifying existing files, create a new file in the `includes/` directory
2. **Use hooks and filters**: Use WordPress hooks and filters to modify the plugin's behavior
3. **Follow naming conventions**: Use the naming conventions described above
4. **Document your changes**: Add comments to explain what your code does and why
5. **Write tests**: Add tests to ensure your changes work as expected

By following these guidelines, you can help ensure the plugin remains stable and maintainable.