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
     * Open a FixedWidthWriter with automatic stream handling.
     *
     * The first argument is a either an open stream or a path to use to open
     * a text file. In either case, the output stream will automatically be
     * closed when the writer object is destroyed. Any additional arguments are
     * passed along to the FixedWidthWriter constructor.
     */
    public static function open(/* $args */)
    {
        // Every derived class *MUST* implement its own open() method that
        // returns the correct type of object.
        $args = func_get_args();
        if (count($args) < 2) {
            $message = 'call to open() is missing required arguments';
            throw new BadMethodCallException($message);
        }
        if (!is_resource($args[0])) {
            // Assume this is a string to use as a file path.
            $args[0] = fopen($path, 'w');
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
