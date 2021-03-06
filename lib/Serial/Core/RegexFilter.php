<?php
namespace Serial\Core;

/**
 * Filter lines using a regular expression.
 *
 * This is intended for use with StreamFilterManager.
 */
class RegexFilter
{
    private $regex;
    private $blacklist;
    
    /**
     * Initialize this object.
     *
     * By default, lines that match the regular expression are passed through
     * and all other lines are rejected (whitelisting). If $blacklist is true 
     * this behavior is inverted (blacklisting).
     */
    public function __construct($regex, $blacklist=false)
    {
        $this->regex = $regex;
        $this->blacklist = $blacklist;
        return;
    }
    
    /**
     * Execute the filter.
     */ 
    public function __invoke($line)
    {
        $match = preg_match($this->regex, $line);
        return $match != $this->blacklist ? $line : null;
    }
}
