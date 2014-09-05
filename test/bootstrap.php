<?php
/**
 * Define the PHPUnit test environment.
 *
 */

// Load the package under test. This can be either from a source tree or a phar
// archive. By default, the header file in the development source tree is used.
// Set the PHPUNIT_TEST_SOURCE environment variable to specify another file.  

$root = dirname(dirname(__FILE__));
if (!($path = getenv('PHPUNIT_TEST_SOURCE'))) {
    $path = array($root, 'lib', 'Serial', 'Core.php');
    $path = implode(DIRECTORY_SEPARATOR, $path);
}
require $path;


// Load tests.

require 'Test.php';


// Additional initialization.

date_default_timezone_set('UTC');  // stop DateTime from complaining
