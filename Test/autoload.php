<?php
/**
 * Test suite class autoloader.
 *
 * The test suite uses the PSR-0 standard for organizing and loading classes,
 * except that there is currently no Vendor prefix. For PHP 5.2 compatibility
 * namespaces are not used.
 *
 *    <http://www.php-fig.org/psr/psr-0/>
 */
function Test_autoload($name)
{
    // If no class is given, load the Test namespace class.
    list($lib, $cls) = array_pad(explode('_', $name, 2), 2, 'Test');
    if ($lib != 'Test') {
        return;
    }
    $root = dirname(__FILE__).DIRECTORY_SEPARATOR;
    $path = $root.str_replace('_', DIRECTORY_SEPARATOR, $cls).'.php';
    if (is_readable($path)) {
        include $path;                
    }
    return;    
}

spl_autoload_register('Test_autoload');
