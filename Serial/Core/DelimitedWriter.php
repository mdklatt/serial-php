<?php
/**
 * A writer for tabular data with delimited fields.
 *
 */
class Serial_Core_DelimitedWriter extends Serial_Core_TabularWriter
{
    private $delim;
    
    public function __construct($stream, $fields, $delim, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, $endl);
        $this->delim = $delim;
        return;
    }
    
    protected function join($tokens)
    {
        return implode($this->delim, $tokens);
    }
}
