# Testing Documentation

The Happy Business Listing plugin includes a comprehensive testing framework to ensure code quality and prevent regressions. This document provides detailed information about the testing framework, how to run tests, and how to write new tests.

## Overview

The testing framework includes:

1. **Unit Tests**: Tests for individual functions and classes
2. **Integration Tests**: Tests for interactions between different parts of the plugin
3. **End-to-End Tests**: Tests for complete user journeys through the plugin
4. **Test Helpers**: Helper functions and classes for testing

## Requirements

To run the tests, you need:

- PHP 7.4 or higher
- PHPUnit 9.0 or higher
- WordPress test suite
- Composer

## Installation

1. Install Composer dependencies:

```bash
composer install
```

2. Install the WordPress test suite:

```bash
./bin/install-wp-tests.sh wordpress_test root root localhost latest
```

Replace `root` and `root` with your MySQL username and password, and `localhost` with your MySQL host if different.

## Running Tests

### Running All Tests

To run all tests:

```bash
composer test
```

### Running Unit Tests

To run only unit tests:

```bash
composer test:unit
```

### Running Integration Tests

To run only integration tests:

```bash
composer test:integration
```

### Running Specific Tests

To run a specific test file:

```bash
./vendor/bin/phpunit tests/unit/test-security.php
```

To run a specific test method:

```bash
./vendor/bin/phpunit --filter test_sanitize_input_text
```

## Test Structure

The tests are organized in the following directory structure:

```
tests/
├── bootstrap.php           # Test bootstrap file
├── test-helpers.php        # Test helper functions
├── unit/                   # Unit tests
│   ├── test-security.php   # Security functions tests
│   ├── test-templates.php  # Templates functions tests
│   └── test-helpers.php    # Helper functions tests
├── integration/            # Integration tests
│   ├── test-form-submission.php     # Form submission tests
│   └── test-user-registration.php   # User registration tests
└── e2e/                    # End-to-end tests
    └── test-e2e.php        # End-to-end tests
```

## Writing Tests

### Unit Tests

Unit tests should test individual functions and classes in isolation. They should not depend on external resources or other parts of the plugin.

Example:

```php
/**
 * Test sanitize input function with text type
 */
public function test_sanitize_input_text() {
    $input = '<script>alert("XSS");</script>Hello World';
    $sanitized = hbl_sanitize_input($input, 'text');
    
    $this->assertEquals('Hello World', $sanitized);
}
```

### Integration Tests

Integration tests should test interactions between different parts of the plugin. They can depend on external resources and other parts of the plugin.

Example:

```php
/**
 * Test business registration form submission
 */
public function test_business_registration_form_submission() {
    // Set up POST data
    $_POST = array(
        'action' => 'hbl_register_business',
        'hbl_nonce' => wp_create_nonce('hbl_register_business'),
        'business_name' => 'Test Business',
        // ...
    );
    
    // Call the handler function
    hbl_handle_business_registration();
    
    // Check if a business listing was created
    $posts = get_posts(array(
        'post_type' => 'business_listing',
        'meta_key' => 'business_name',
        'meta_value' => 'Test Business'
    ));
    
    $this->assertCount(1, $posts);
}
```

### End-to-End Tests

End-to-end tests should test complete user journeys through the plugin. They should simulate real user interactions with the plugin.

Example:

```php
/**
 * Test complete user journey
 */
public function test_complete_user_journey() {
    // Step 1: Submit business registration form
    $business_id = $this->submit_business_registration_form();
    
    // Step 2: Verify business listing was created
    $this->verify_business_listing($business_id);
    
    // Step 3: Submit contact form to create a lead
    $lead_id = $this->submit_contact_form($business_id);
    
    // Step 4: Verify lead was created
    $this->verify_lead($lead_id, $business_id);
}
```

## Test Helpers

The `test-helpers.php` file contains helper functions and classes for testing. These include:

- `HBL_Test_Helpers::create_test_business()`: Creates a test business listing
- `HBL_Test_Helpers::create_test_user()`: Creates a test user
- `HBL_Test_Helpers::create_test_service()`: Creates a test service/product
- `HBL_Test_Helpers::create_test_lead()`: Creates a test lead
- `HBL_Test_Helpers::mock_wp_functions()`: Mocks WordPress functions for unit testing
- `HBL_Test_Helpers::reset_test_environment()`: Resets the test environment

Example:

```php
// Create a test business
$business_id = HBL_Test_Helpers::create_test_business(array(
    'post_title' => 'Custom Business Name'
));

// Create a test user
$user_id = HBL_Test_Helpers::create_test_user(array(
    'user_login' => 'customuser'
));

// Create a test service
$service_id = HBL_Test_Helpers::create_test_service($business_id, array(
    'post_title' => 'Custom Service Name'
));

// Create a test lead
$lead_id = HBL_Test_Helpers::create_test_lead($business_id, array(
    'post_title' => 'Custom Lead Name'
));
```

## Mocking

For unit tests, you may need to mock WordPress functions and classes. The `Brain\Monkey` library is included for this purpose.

Example:

```php
use Brain\Monkey\Functions;

// Mock WordPress functions
Functions\when('get_option')->justReturn('test_value');
Functions\when('update_option')->justReturn(true);
Functions\when('wp_verify_nonce')->justReturn(true);

// Test function that uses these WordPress functions
$result = my_function_that_uses_wp_functions();

// Assert the result
$this->assertEquals('expected_value', $result);
```

## Continuous Integration

The tests are automatically run on GitHub Actions for every push and pull request. The configuration is in the `.github/workflows/phpunit.yml` file.

## Best Practices

1. **Test Coverage**: Aim for high test coverage, especially for critical functions
2. **Isolation**: Unit tests should be isolated and not depend on external resources
3. **Mocking**: Use mocking for external dependencies in unit tests
4. **Assertions**: Use specific assertions (e.g., `assertEquals` instead of `assertTrue`)
5. **Setup and Teardown**: Use `setUp` and `tearDown` methods to set up and clean up test environments
6. **Documentation**: Document your tests with clear comments
7. **Test Edge Cases**: Test edge cases and error conditions
8. **Test Performance**: Keep tests fast to run

## Troubleshooting

### Tests Fail with Database Errors

If tests fail with database errors, make sure the WordPress test database exists and is accessible:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS wordpress_test"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON wordpress_test.* TO 'root'@'localhost'"
```

### Tests Fail with "Call to undefined function" Errors

If tests fail with "Call to undefined function" errors, make sure the function is defined in the bootstrap file or included in the test file:

```php
require_once HBL_PLUGIN_DIR . 'includes/file-with-function.php';
```

### Tests Fail with "Cannot redeclare function" Errors

If tests fail with "Cannot redeclare function" errors, make sure the function is only defined once:

```php
if (!function_exists('my_function')) {
    function my_function() {
        // ...
    }
}
```

### Tests Fail with "Headers already sent" Errors

If tests fail with "Headers already sent" errors, make sure there is no output before header functions:

```php
ob_start();
// Code that might output something
$output = ob_get_clean();
```