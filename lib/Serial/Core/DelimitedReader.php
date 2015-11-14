<?php
namespace Serial\Core;

/**
 * A reader for tabular data consisting of character-delimited fields.
 *
 * The position of each scalar field is be given as an integer index, and the
 * position of an array field is a (begin, length) pair where the length is
 * null for a variable-length array.
 *
 */
class DelimitedReader extends TabularReader
{
    /**
     * Return the class name.
     *
     * This *MUST* be implemented by all derived classes. It is sufficient to
     * copy this function verbatim.
     */
    protected static function className()
    {
        return __CLASS__;
    }
    
    private $delim;

    /**
     * Initialize this object.
     *
     * The default delimiter will split on any whitespace.
     */
    public function __construct($stream, $fields, $delim=null, $endl=PHP_EOL)
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
        // TODO: Add delimiter escaping.
        if ($this->delim === null) {
            // Split on any whitespace.
            $line = preg_split('/\s+/', $line);
        }
        else {
            $line = explode($this->delim, $line);
        }
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
