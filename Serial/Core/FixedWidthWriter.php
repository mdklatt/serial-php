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
        // Every derived class must implement its own open() method that
        // returns the correct type of object.
        $args = func_get_args();
        if (count($args) < 2) {
            $message = 'call to open() is missing required arguments';
            throw new BadMethodCallException($message);
        }
        if (!is_resource($args[0])) {
            // Assume this is a string to use as a file path.
            if (!($args[0] = @fopen($args[0], 'w'))) {
                $message = "invalid input stream or path: {$args[0]}";
                throw new RuntimeException($message);
            }
        }
        $class = new ReflectionClass('Serial_Core_FixedWidthWriter');
        $writer = $class->newInstanceArgs($args);
        $writer->closing = true;  // take responsiblity for closing stream
        return $writer;
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
