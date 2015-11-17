<?php
namespace Serial\Core;

/**
 * Robustly close a stream.
 * 
 */
function close($stream) {
    # TODO: Put this in a private namespace.
    while (is_resource($stream) && fclose($stream)) {
        // Need a loop here because sometimes fclose() doesn't actually close
        // the stream on the first try, even if it returns true.        
        continue;
    }
}
