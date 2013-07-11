<?php
/**
 * Predefined filters.
 *
 */


class Serial_BlacklistFilter
{
    private $field;
    private $reject;
    
    public function __construct($field, $reject)
    {
        $this->field = $field;
        $this->reject = $reject;
        return;
    }
    
    public function __invoke($record)
    {
        if (array_search($record[$this->field], $this->reject) !== false) {
            return null;
        }
        return $record;
    }
}


class Serial_WhitelistFilter
{
    private $field;
    private $accept;
    
    public function __construct($field, $accept)
    {
        $this->field = $field;
        $this->accept = $accept;
        return;
    }
    
    public function __invoke($record)
    {
        if (array_search($record[$this->field], $this->accept) === false) {
            return null;
        }
        return $record;
    }
}
