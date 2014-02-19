<?php
/**
 * PHPUnit bootstrap file.
 *
 * Use this to define the PHPUnit test environment.
 */

// Load the Test classes.

require 'autoload.php';


// Load the package under test. This can be either from a source tree or a phar
// archive. By default, the 'autoload.php' file in the development source tree
// is used. Set the PHPUNIT_TEST_SOURCE environment variable to specify another
// file.  

if (!($path = getenv('PHPUNIT_TEST_SOURCE'))) {
    $root = dirname(dirname(__FILE__));  // absolute path to ../
    $path = array($root, 'Serial', 'Core', 'autoload.php');
    $path = implode(DIRECTORY_SEPARATOR, $path);
}
require $path;


// Additional initialization.

date_default_timezone_set('UTC');  // stop DateTime from complaining
