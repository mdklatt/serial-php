<?php
/**
 * Filter lines by a specific substring.
 *
 * This is intended for use with Serial_Core_StreamFilterManager.
 */
class Serial_Core_SubstrFilter
{
    private $beg;
    private $end;
    private $values;
    private $blacklist;
  
    /**
     * Initialize this object.
     *
     * By default, records that match one of the given field values are passed
     * through and all other records are dropped (whitelisting). If $blacklist
     * is true this behavior is inverted (blacklisting).
     */
    public function __construct($pos, $values, $blacklist=false)
    {
        list($this->beg, $this->end) = $pos;
        $this->values = array_flip($values);  // store as keys for fast search
        $this->blacklist = $blacklist;
        return;
    }
    
    /**
     * Execute the filter.
     *
     */
    public function __invoke($line)
    {   
        $value = substr($line, $this->beg, $this->end);
        $match = isset($this->values[$value]);
        return $match != $this->blacklist ? $line : null;
    }
}
