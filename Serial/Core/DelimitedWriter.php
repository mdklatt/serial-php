<?php
/**
 * A writer for tabular data consisting of character-delimited fields.
 *
 * The position of each scalar field is be given as an integer index, and the
 * position of an array field is a (begin, length) pair where the length is
 * null for a variable-length array. 
 */
class Serial_Core_DelimitedWriter extends Serial_Core_TabularWriter
{
    // TODO: Add delimiter escaping.
    
    /**
     * Create a DelimitedWriter with automatic stream handling.
     *
     * The first argument is either an open stream or a path to open as a text
     * file. In either case, the output stream will automatically be closed 
     * when the writer's destructor is called. Any additional arguments are 
     * passed along to the DelimitedWriter constructor.
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
        if (count($args) < 3) {
            $message = 'call to open() is missing required arguments';
            throw new BadMethodCallException($message);
        }
        if (!is_resource($args[0])) {
            // Assume this is a string to use as a file path.
            if (!($args[0] = @fopen($args[0], 'w'))) {
                $message = "invalid output stream or path: {$args[0]}";
                throw new RuntimeException($message);
            }
        }
        $class = new ReflectionClass('Serial_Core_DelimitedWriter');
        $writer = $class->newInstanceArgs($args);
        $writer->closing = true;  // take responsiblity for closing stream
        return $writer;
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
        return implode($this->delim, $tokens);
    }
}
