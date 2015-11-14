<?php
namespace Serial\Core;

/**
 * Default key function.
 *
 * The key value for each record will be the subset of values specified by one
 * or more key fields. This is used internally and is not part of the public 
 * library API.
 */
class KeyFunc
{
    /**
     * Initialize this object.
     *
     * The $keys argument is one or more names to use as key fields.
     */
    public function __construct($keys)
    {
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        $this->keys = array_flip($keys);
        return;
    }
    
    /**
     * Return key fields for the given record.
     */
    public function __invoke($record)
    {
        $keys = $this->keys;
        foreach ($keys as $key => $pos) {
            $keys[$key] = $record[$key];
        }
        return $keys;
    }
}
