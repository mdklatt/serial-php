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
     * The first argument is either an open stream or a path to open as a text
     * file. In either case, the output stream will automatically be closed 
     * when the writer's destructor is called. Any additional arguments are 
     * passed along to the writer's constructor.
     *
     * If the object contains a circular reference, e.g. a class method used
     * as a filter callback, unsetting the variable is not enough to trigger
     * the destructor. It will be called when the process ends, or it can be 
     * called explicitly, i.e. $writer->__destruct().
     */
    public static function open(/* $args */)
    {
        // This is strictly for documention purposes. This should return a
        // dynamic type, so derived classes must implement their own open()
        // method if appropriate. Here is a sample implementation.
        //
        // if (!($args = func_get_args())) {
        //     $message = "call to open() is missing required arguments";
        //     throw new BadMethodCallException($message);
        // }
        // if (!is_resource($args[0])) {
        //     // Assume this is a string to use as a file path.
        //     if (!($args[0] = @fopen($args[0], 'w'))) {
        //         $message = "invalid input stream or path: {$args[0]}";
        //         throw new RuntimeException($message);
        //     }
        // }
        // $class = new ReflectionClass('Derived_Class_Name_Goes_Here');
        // $writer = $class->newInstanceArgs($args);
        // $writer->closing = true;  // take responsiblity for closing stream
        // return $rwriter;
        $message = 'Serial_Core_TabularWriter::open() is not implemented';
        throw new BadMethodCallException($message);
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
            while (is_resource($this->stream) && fclose($this->stream)) {
                // Need a loop here because sometimes fclose() doesn't actually
                // close the stream on the first try even if it returns true.
                // Can't throw an exception from a destructor, so let fclose()
                // report a warning if it fails (data may be lost).
                continue;
            }
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
