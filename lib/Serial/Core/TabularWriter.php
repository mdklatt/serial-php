<?php
/**
 * Base class for tabular data writers.
 *
 * Tabular data is organized into named fields such that each field occupies
 * the same position in each output record. One line of text corresponds to one
 * complete record.
 */
abstract class Serial_Core_TabularWriter extends Serial_Core_Writer
{
    /**
     * Create a writer with automatic stream handling.
     *
     * The first argument is a Writer class name, and the next argument is
     * either an open stream or a path to open as a text file. In both cases,
     * the input stream will automatically be closed when the the Reader's
     * destructor is called. Any additional arguments are passed along to the
     * Writer's constructor. Derived classes should implement their own static
     * open() method that calls this method with the appropriate class name.
     *
     * If the object contains a circular reference, e.g. a class method used
     * as a filter callback, unsetting the variable is not enough to trigger
     * the destructor. It will be called when the process ends, or it can be
     * called explicitly, i.e. $writer->__destruct().
     */
    public static function openWriter($args)
    {
        // This is a workaround for PHP 5.2's lack of late static binding. If
        // 5.2 compatibility is no longer necessary, this should be renamed to
        // 'open' and made public, and derived classes can override it as
        // necessary.
        assert($args);
        $className = array_shift($args);
        if (!is_resource($args[0])) {
            // Assume this is a string to use as a file path.
            if (!($args[0] = @fopen($args[0], 'r'))) {
                $message = "invalid input stream or path: {$args[0]}";
                throw new RuntimeException($message);
            }
        }
        $class = new ReflectionClass($className);
        $writer = $class->newInstanceArgs($args);
        $writer->closing = true;  // take responsibility for closing stream
        return $writer;
    }
    
    protected $closing = false;
    protected $stream;
    protected $fields;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($stream, $fields, $endl=PHP_EOL)
    {
        parent::__construct();
        $this->stream = $stream;
        $this->fields = $fields;
        $this->endl = $endl;
        return;
    }
    
    /**
     * Object clean-up.
     *
     * If the $closing attribute is true, the object's stream is automatically
     * closed; see the open() method.
     */
    public function __destruct()
    {
        if ($this->closing && is_resource($this->stream)) {
            Serial_Core::close($this->stream);
        }
        return;
    }    

    /**
     * Put a formatted record into the output stream.
     * 
     * This is called after the record has been passed through all filters.
     */
    protected function put($record)
    {
        $tokens = array();
        foreach ($this->fields as &$field) {
            // Convert each field to a string token.
            $token = $field->encode(@$record[$field->name]);
            if (is_array($token)) {
                // An array of tokens; expand inline.
                $tokens = array_merge($tokens, $token);
            }
            else {
                $tokens[] = $token;
            } 
        }
        fwrite($this->stream, $this->join($tokens).$this->endl);
        return;
    }
    
    /**
     * Join an array of string tokens into a line of text.
     *
     */
    abstract protected function join($tokens);
}
