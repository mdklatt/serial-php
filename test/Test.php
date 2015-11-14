<?php
namespace Serial\Core\Test;

/**
 * The Serial\Core library test suite.
 *
 */
function run($name=null)
{
    $path = dirname(__FILE__).DIRECTORY_SEPARATOR;
    if ($name) {
        $path.= "{$name}.php";
    }
    system('phpunit --bootstrap test/bootstrap.php '.$path, $status);
    return $status;
}


spl_autoload_register(function($name) {
    $path = explode('\\', $name);
    if (array_slice($path, 0, 3) != array('Serial', 'Core', 'Test')) {
        return;
    }
    $path = array_slice($path, 2);
    array_unshift($path, dirname(__FILE__));
    $path = implode(DIRECTORY_SEPARATOR, $path).'.php';
    if (is_readable($path)) {
        include $path;
    }
    return;
});
