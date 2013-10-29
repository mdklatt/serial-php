<?php
/**
 * Predefined filters for reader/writers and streams.
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


/**
 * Filter lines using a regular expression.
 *
 */
class Serial_Core_TextFilter
{
    private $regex;
    private $whitelist;
    
    /**
     * Initialize this object.
     *
     * By default, lines that match the regular expression are passed through
     * and all other lines are rejected (whitelisting). If $whitelist is false
     * this is reversed (blacklisting).
     */
    public function __construct($regex, $whitelist=true)
    {
        $this->regex = $regex;
        $this->whitelist = $whitelist;
        return;
    }
    
    /**
     * Execute the filter.
     * 
     */ 
    public function __invoke($line)
    {
        $valid = preg_match($this->regex, $line) == $this->whitelist;
        return $valid ? $line : null;
    }
}
