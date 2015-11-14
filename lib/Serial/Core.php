<?php
/**
 * The Serial\Core library.
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
namespace Serial\Core;

const VERSION = '0.4.0dev';

/**
 * Robustly close a stream.
 */
function close($stream) {
    # TODO: Put this in a private namespace.
    while (is_resource($stream) && fclose($stream)) {
        // Need a loop here because sometimes fclose() doesn't actually close
        // the stream on the first try, even if it returns true.        
        continue;
    }
}


spl_autoload_register(function($name) {
    $path = explode('\\', $name);
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
});
