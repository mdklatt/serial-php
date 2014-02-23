<?php
/**
 * A writer for tabular data consisting of fixed-width fields.
 *
 * The position of each field is given as a (begin, length) substring 
 * expression where the length is null for a variable-length array.
 */
class Serial_Core_FixedWidthWriter extends Serial_Core_TabularWriter
{
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
