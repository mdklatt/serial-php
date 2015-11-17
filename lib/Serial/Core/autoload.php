<?php
namespace Serial\Core;

/**
 * Register a PSR-0 autoloader for the library namespace.
 * 
 */
spl_autoload_register(function($name) {
    if (substr($name, 0, strlen(__NAMESPACE__)) != __NAMESPACE__) {
        // Defer to another autoloader.
        return;
    }
    $path = explode('\\', $name);
    array_unshift($path, dirname(dirname(dirname(__FILE__))));
    $path = implode(DIRECTORY_SEPARATOR, $path).'.php';
    if (is_readable($path)) {
        include $path;
    }
    return;
});
