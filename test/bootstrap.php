<?php
/**
 * PHPUnit bootstrap file.
 *
 *     phpunit --bootstrap bootstrap.php
 *
 * By default, the development source tree will be tested. Specify a different
 * test source by setting the PHPUNIT_TEST_SOURCE environment variable; this
 * is useful for verifying a phar file.
 */
require 'Test/autoload.php';
$root = implode(DIRECTORY_SEPARATOR, array(dirname(dirname(__FILE__)), 'lib'));
if (!($path = getenv('PHPUNIT_TEST_SOURCE'))) {
    $path = array(dirname(dirname(__FILE__)), 'lib', 'Serial', 'Core', 'autoload.php');
    $path = implode(DIRECTORY_SEPARATOR, $path);
}
require $path;
date_default_timezone_set('UTC');  // stop DateTime from complaining
