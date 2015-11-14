<?php
namespace Serial\Core;

/**
 * A writer for tabular data consisting of character-delimited fields.
 *
 * The position of each scalar field is be given as an integer index, and the
 * position of an array field is a (begin, length) pair where the length is
 * null for a variable-length array. 
 */
class DelimitedWriter extends TabularWriter
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
     */
    public function __construct($stream, $fields, $delim, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, $endl);
        $this->delim = $delim;
        return;
    }
 
    /**
     * Join an array of string tokens into a line of text.
     *
     */   
    protected function join($tokens)
    {
        // TODO: Add delimiter escaping.
        return implode($this->delim, $tokens);
    }
}
