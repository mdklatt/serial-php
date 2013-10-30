<?php
/**
 * Load the serial-core library.
 *
 * This library uses the PSR-0 standard for organizing and loading classes,
 * except that there is currently no Vendor prefix. For PHP 5.2 compatibility
 * namespaces are not used.
 *
 *    <http://www.php-fig.org/psr/psr-0/>
 */


// Load constants and functions and register the class autoloader.

include 'version.php';
spl_autoload_register('Serial_Core_autoload');


/**
 * Class autoloader for the serial-core library.
 *
 */
function Serial_Core_autoload($name)
{
    $root = strstr(__DIR__, 'Serial', true);  // remove Serial/Core
    $path = $root.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';
    if (is_readable($path)) {
        include $path;                
    }
    return;    
}
