<?php
namespace Serial\Core\Test;

/**
 * Register a PSR-4 autoloader for the library namespace.
 *
 */
spl_autoload_register(function($name) {
    $path = explode('\\', $name);
    if (array_slice($path, 0, 3) != array('Serial', 'Core', 'Test')) {
        return;
    }
    $path = array_slice($path, 2);
    array_unshift($path, dirname(dirname(__FILE__)));
    $path = implode(DIRECTORY_SEPARATOR, $path).'.php';
    if (is_readable($path)) {
        require $path;
    }
    return;
});
