<?php
/**
 * Filter lines by a specific substring.
 *
 * This is intended for use with Serial_Core_StreamFilterManager.
 */
class Serial_Core_SubstrFilter
{
    /**
     * Initialize this object.
     *
     * By default, records that match one of the given field values are passed
     * through and all other records are dropped (whitelisting). If $whitelist
     * is false this behavior is inverted (blacklisting).
     */
    public function __construct($pos, $values, $whitelist=true)
    {
        list($this->beg, $this->end) = $pos;
        $this->values = array_flip($values);  // store as keys for fast search
        $this->whitelist = $whitelist;
        return;
    }
    
    /**
     * Execute the filter.
     *
     */
    public function __invoke($line)
    {   
        $value = substr($line, $this->beg, $this->end);
        $valid = isset($this->values[$value]) == $this->whitelist;
        return $valid ? $line : null;
    }
}
