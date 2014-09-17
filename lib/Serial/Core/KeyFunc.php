<?php
/**
 * Default key function for an AggregateReader/Writer.
 *
 * The key value for each record will be the subset of values specified by one
 * or more key fields. This is used internally and is not part of the public 
 * library API.
 */
class Serial_Core_KeyFunc
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
        return array_intersect_key($record, $this->keys);
    }
}
