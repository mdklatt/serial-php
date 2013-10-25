<?php
/**
 * Predefined filters.
 *
 */

/**
 * Filter records by a specific field.
 *
 * This is intended for use with a Reader or Writer via their filter() method.
 *
 */
class Serial_Core_FieldFilter
{
    /**
     * Initialize this object.
     *
     * By default, records that match one of the given field values are passed
     * through and all other records are dropped (whitelisting). If $whitelist
     * is false this behavior is inverted (blacklisting).
     */
    public function __construct($field, $values, $whitelist=true)
    {
        $this->field = $field;
        $this->values = array_flip($values);  // store as keys for fast search
        $this->whitelist = $whitelist;
        return;
    }
    
    /**
     * Execute the filter.
     *
     */
    public function __invoke($record)
    {   
        if (!isset($record[$this->field])) {
            // The record fails for whitelisting because it doesn't have a 
            // required field value; it's valid for blacklisting because it
            // doesn't have a probhibited value.
            $valid = !$this->whitelist;
        }
        else {
            $value = $record[$this->field];
            $valid = isset($this->values[$value]) == $this->whitelist;
        }
        return $valid ? $record : null;
    }
}
