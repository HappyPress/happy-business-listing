<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Happy_Business_Listing
 */

// Define constants for testing
define('HBL_TESTING', true);
define('HBL_PLUGIN_DIR', dirname(__DIR__) . '/');
define('HBL_PLUGIN_URL', 'http://example.org/wp-content/plugins/happy-business-listing/');
define('HBL_VERSION', '1.0.0');

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WP testing environment
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit(1);
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested
 */
function _manually_load_plugin() {
    require dirname(__DIR__) . '/happy-business-listing.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Include test helpers
require_once dirname(__DIR__) . '/tests/test-helpers.php';