<?php
/**
 * Autoloading and encapsulation for Test global constants and functions.
 *
 */
class Test
{
    /**
     * Run phpunit for the entire test suite or an individual class.
     *
     */
    public static function run($name=null)
    {
        echo 'Test: '.$name.PHP_EOL;
        $path = dirname(__FILE__).DIRECTORY_SEPARATOR;
        if ($name) {
            $path.= "{$name}.php";
        }
        // system('phpunit --configuration Test/phpunit.xml '.$path, $status);
        system('phpunit --bootstrap Test/env.php '.$path, $status);
        return $status;
    }
}