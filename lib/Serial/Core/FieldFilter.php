<?php
namespace Serial\Core;

/**
 * Filter records by a specific field.
 *
 * This is intended for use with a Reader or Writer via their filter() method.
 */
class FieldFilter
{
    private $field;
    private $values;
    private $blacklist;
    
    /**
     * Initialize this object.
     *
     * By default, records that match one of the given field values are passed
     * through and all other records are dropped (whitelisting). If $blacklist
     * is true this behavior is inverted (blacklisting).
     */
    public function __construct($field, $values, $blacklist=false)
    {
        $this->field = $field;
        $this->values = array_flip($values);  // store as keys for fast search
        $this->blacklist = $blacklist;
        return;
    }
    
    /**
     * Execute the filter.
     *
     */
    public function __invoke($record)
    {   
        $match = false;
        if (isset($record[$this->field])) {
            $value = $record[$this->field];
            $match = isset($this->values[$value]);
        }
        return $match != $this->blacklist ? $record : null;
    }
}
