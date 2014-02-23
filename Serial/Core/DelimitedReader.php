<?php
/**
 * A reader for tabular data consisting of character-delimited fields.
 *
 * The position of each scalar field is be given as an integer index, and the
 * position of an array field is a (begin, length) pair where the length is
 * null for a variable-length array.
 *
 */
class Serial_Core_DelimitedReader extends Serial_Core_TabularReader
{
    // TODO: Add default delimiter to split on all whitespace.
    // TODO: Add delimiter escaping.
    
    private $delim;
    
    /**
     * Iniialize this object.
     *
     */
    public function __construct($stream, $fields, $delim, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, $endl);
        $this->delim = $delim;
        return;
    }
    
    /**
     * Split a line of text into an array of string tokens.
     *
     * Lines are split at each occurrence of the delimiter; the delimiter is
     * discarded.
     */
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
