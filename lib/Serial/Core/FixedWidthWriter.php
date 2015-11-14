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
     * Create a FixedWidthWriter with automatic stream handling.
     *
     * The first argument is either an open stream or a path to open as a text
     * file. In either case, the output stream will automatically be closed 
     * when the writer's destructor is called. Any additional arguments are 
     * passed along to the FixedWidthWriter constructor.
     *
     * If the object contains a circular reference, e.g. a class method used
     * as a filter callback, unsetting the variable is not enough to trigger
     * the destructor. It will be called when the process ends, or it can be 
     * called explicitly, i.e. $writer->__destruct().
     */
    public static function open(/* $args */)
    {
        // Every derived class must implement its own open() method that calls
        // openWriter() with the correct class name. This is a workaround for
        // PHP 5.2's lack of late static binding.
        $args = func_get_args();
        array_unshift($args, __CLASS__);
        return parent::openWriter($args);
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
