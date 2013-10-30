<?php
/**
 * A reader for tabular data with delimited fields.
 *
 */
class Serial_Core_DelimitedReader extends Serial_Core_TabularReader
{
    private $delim;
    
    public function __construct($stream, $fields, $delim, $endl="\n")
    {
        parent::__construct($stream, $fields, $endl);
        $this->delim = $delim;
        return;
    }
    
    protected function split($line)
    {
        $line = explode($this->delim, $line);
        $tokens = array();
        foreach ($this->fields as $field) {
            if (is_array($field->pos)) {
                // Token is an array.
                list($beg, $len) = $field->pos;
                $tokens[] = array_slice($line, $beg, $len);
            }
            else {
                $tokens[] = $line[$field->pos];                
            }
        }
        return $tokens;
    }
}
