<?php
/**
 * Set up the test environment.
 * 
 */
define('LIB_NAME', 'serial');
define('LIB_HEADER', LIB_NAME.DIRECTORY_SEPARATOR.LIB_NAME.'.php');
define('TEST_PATH', dirname(__FILE__));

require dirname(TEST_PATH).DIRECTORY_SEPARATOR.LIB_HEADER;
chdir(TEST_PATH);
date_default_timezone_set('UTC');  // stop DateTime from complaining