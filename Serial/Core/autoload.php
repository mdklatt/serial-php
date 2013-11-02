<?php
/**
 * Class autoloader for the serial-core library.
 *
 * This library uses the PSR-0 standard for organizing and loading classes,
 * except that there is currently no Vendor prefix. For PHP 5.2 compatibility
 * namespaces are not used.
 *
 *    <http://www.php-fig.org/psr/psr-0/>
 */
function Serial_Core_autoload($name)
{
    // If no class is given, load the package namespace class.    
    list($lib, $pkg, $cls) = array_pad(explode('_', $name, 3), 3, 'Core');
    if ($lib != 'Serial' || $pkg != 'Core') {
        return;
    }
    $root = dirname(__FILE__).DIRECTORY_SEPARATOR;
    $path = $root.str_replace('_', DIRECTORY_SEPARATOR, $cls).'.php';
    if (is_readable($path)) {
        include $path;                
    }
    return;    
}

spl_autoload_register('Serial_Core_autoload');
