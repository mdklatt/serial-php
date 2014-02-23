<?php
/**
 * Filter lines using a regular expression.
 *
 * This is intended for use with Serial_Core_StreamFilterManager.
 */
class Serial_Core_RegexFilter
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
