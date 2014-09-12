<?php
/**
 * Define the PHPUnit test environment.
 *
 */

// Read package configuration.

$root = dirname(dirname(__FILE__));
require_once implode(DIRECTORY_SEPARATOR, array($root, 'config.php'));


// Load the package under test. This can be either from a source tree or a phar
// archive. By default, the header file in the development source tree is used.
// Set the PHPUNIT_TEST_SOURCE environment variable to specify another file.  

if (!($path = getenv('PHPUNIT_TEST_SOURCE'))) {
    $path = implode(DIRECTORY_SEPARATOR, array($root, $CONFIG['lib_path'], $CONFIG['lib_init']));
}
require $path;


// Load tests.

require $CONFIG['test_init'];


// Additional initialization.

date_default_timezone_set('UTC');  // stop DateTime from complaining
