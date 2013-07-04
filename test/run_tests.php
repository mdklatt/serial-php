<?php
/**
 * Run all tests for this library.
 *
 * Tests are run using phpunit. A configuration file can be specified on the
 * command line or else the default file (phpunit.xml) is used. The return 
 * value is nonzero if any test is unsuccessful.
 */
require_once 'env.php';

$config = $argc > 1 ? $argv[1] : 'phpunit.xml';
$cmd = "phpunit --configuration {$config}";
system($cmd, $status);
exit($status);