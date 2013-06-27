<?php
/**
 * Predefined filters.
 *
 */


class BlacklistFilter
{
    private $_field;
    private $_reject;
    
    public function __construct($field, $reject)
    {
        $this->_field = $field;
        $this->_reject = $reject;
        return;
    }
    
    public function __invoke($record)
    {
        if (array_search($record[$this->_field], $this->_reject) !== false) {
            return null;
        }
        return $record;
    }
}


class WhitelistFilter
{
    private $_field;
    private $_accept;
    
    public function __construct($field, $accept)
    {
        $this->_field = $field;
        $this->_accept = $accept;
        return;
    }
    
    public function __invoke($record)
    {
        if (array_search($record[$this->_field], $this->_accept) === false) {
            return null;
        }
        return $record;
    }
}
