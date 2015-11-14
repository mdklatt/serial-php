<?php
namespace Serial\Core;

/**
 * A writer for tabular data consisting of fixed-width fields.
 *
 * The position of each field is given as a (begin, length) substring 
 * expression where the length is null for a variable-length array.
 */
class FixedWidthWriter extends TabularWriter
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
    
    /**
     * Join an array of string tokens into a line of text.
     *
     */       
    protected function join($tokens)
    {
        // In this implementation the positions in $this->fields don't matter;
        // tokens must be in he correct order, and each token must be the
        // correct width for that field. The DataType format for a fixed-width
        // field *MUST* have a field width, e.g. '6.2f'. 
        return implode('', $tokens);
    }  
}
