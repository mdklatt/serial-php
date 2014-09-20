<?php
/**
 * Load the serial-core library.
 *
 * As a PHP 5.2 workaround for namespaces, all library classes have a name of
 * the form `Serial_Core_ClassName`. Global functions and constants are defined
 * in the Serial_Core class.
 * 
 * This library uses Semantic Versioning:
 *    <http://semver.org>
 *
 * Major versions introduce significant changes to the API, and backwards
 * compatibility is not guaranteed. Minor versions are new features and other
 * backwards-compatible changes to the API. Patch versions are for bug fixes
 * internal code changes that do not effect the API.
 *
 * Version 0.x should be considered a development version with an unstable API,
 * and backwards compatibility is not guaranteed for minor versions.
 */
class Serial_Core
{
    const VERSION = '0.3.0';
    
    // The following functions are intended for internal use only.
    
    /**
     * Library class autoloader.
     *
     * This library uses the PSR-0 standard for organizing and loading classes,
     * adpated for the lack of namespaces in PHP 5.2.
     *
     *    <http://www.php-fig.org/psr/psr-0/>
     */
    public static function autoload($name)
    {
        $path = explode('_', $name);
        list($lib, $pkg) = array_pad($path, 2, null);
        if ($lib != 'Serial' || $pkg != 'Core') {
            return;
        }
        array_unshift($path, dirname(dirname(__FILE__)));
        $path = implode(DIRECTORY_SEPARATOR, $path).'.php';
        if (is_readable($path)) {
            include $path;                
        }
        return;    
    }
 
    /**
     * Close a stream.
     *
     */
    public static function close($stream) {
        while (is_resource($stream) && fclose($stream)) {
            // Need a loop here because sometimes fclose() doesn't actually
            // close the stream on the first try even if it returns true.
            continue;
        }
    }
}


spl_autoload_register('Serial_Core::autoload');
