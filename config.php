<?php
/**
 * Package configuration.
 *
 */
$NAMESPACE = 'Serial\Core';
$CONFIG = array(
    'lib_path' => 'lib',
    'lib_name' => str_replace('\\', '-', strtolower($NAMESPACE)),
    'lib_init' => str_replace('\\', DIRECTORY_SEPARATOR, $NAMESPACE).'.php',
    'test_path' => 'test',
    'test_init' => 'Test.php'
);
