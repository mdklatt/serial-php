<?php
/**
 * Autoloading and encapsulation for Core global constants and functions.
 * 
 */
class Serial_Core
{
    // Namespace workaround for PHP 5.2.

    const VERSION = '0.1.1dev';  // Semantic Versioning: <http://semver.org>
    
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
