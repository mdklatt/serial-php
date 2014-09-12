<?php
/**
 * Load the serial-core library test suite.
 *
 * As a PHP 5.2 workaround for namespaces, all test classes have a name of the
 * form `Test_ClassNameTest`. Global functions and constants are defined in the
 * in Test class.
 */
class Test
{
    /**
     * Run phpunit for the entire test suite or an individual class.
     *
     */
    public static function run($name=null)
    {
        $path = dirname(__FILE__).DIRECTORY_SEPARATOR;
        if ($name) {
            $path.= "{$name}.php";
        }
        system('phpunit --bootstrap test/bootstrap.php '.$path, $status);
        return $status;
    }

    // The following functions are intended for internal use only.
    
    /**
     * Test class autoloader.
     *
     */
    public static function autoload($name)
    {
        $path = explode('_', $name);
        if ($path[0] != 'Test') {
            return;
        }
        array_unshift($path, dirname(__FILE__));
        $path = implode(DIRECTORY_SEPARATOR, $path).'.php';
        if (is_readable($path)) {
            include $path;                
        }
        return;    
    }
}


spl_autoload_register('Test::autoload');
