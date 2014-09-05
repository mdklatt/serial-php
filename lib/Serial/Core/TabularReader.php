<?php
/**
 * Base class for tabular data readers.
 *
 * Tabular data is organized into named fields such that each field occupies
 * the same position in each input record. One line of text corresponds to one
 * complete record.
 */
abstract class Serial_Core_TabularReader extends Serial_Core_Reader
{
    /**
     * Create a reader with automatic stream handling.
     *
     * The first argument is either an open stream or a path to open as a text
     * file. In either case, the input stream will automatically be closed when
     * the reader's destructor is called. Any additional arguments are passed
     * along to the reader's constructor.
     *
     * If the object contains a circular reference, e.g. a class method used
     * as a filter callback, unsetting the variable is not enough to trigger
     * the destructor. It will be called when the process ends, or it can be 
     * called explicitly, i.e. $reader->__destruct().
     */
    public static function open(/* $args */)
    {
        // This is strictly for documention purposes. This should return a
        // dynamic type, so derived classes must implement their own open()
        // method if appropriate. Here is a sample implementation.
        //
        // if (!($args = func_get_args())) {
        //     $message = "open() is missing required arguments";
        //     throw new BadMethodCallException($message);
        // }
        // if (!is_resource($args[0])) {
        //     // Assume this is a string to use as a file path.
        //     if (!($args[0] = @fopen($args[0], 'r'))) {
        //         $message = "invalid input stream or path: {$args[0]}";
        //         throw new RuntimeException($message);
        //     }
        // }
        // $class = new ReflectionClass('Derived_Class_Name_Goes_Here');
        // $reader = $class->newInstanceArgs($args);
        // $reader->closing = true;  // take responsiblity for closing stream
        // return $reader;
        $message = 'Serial_Core_TabularReader::open() is not implemented';
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
        parent::__construct();
        $this->stream = $stream;
        $this->fields = $fields;
        $this->endl = $endl;
        return;
    }
    
    /**
     * Object clean-up.
     *
     * If the $closing attribute is true, this object's stream is automatically
     * closed; see the open() method.
     */
    public function __destruct()
    {
        if ($this->closing ) {
            Serial_Core::close($this->stream);
        }
        return;
    }    

    /**
     * Split a line of text into an array of string tokens.
     *
     */
    abstract protected function split($line);

    /**
     * Get the next parsed record from the input stream.
     *
     * This is called before any filters have been applied. A StopIteration 
     * exception is thrown on EOF.
     */
    protected function get()
    {
        if (($line = fgets($this->stream)) === false) {
            throw new Serial_Core_StopIteration();
        }
        $tokens = $this->split(rtrim($line, $this->endl));
        $record = array();
        foreach ($this->fields as $pos => $field) {
            $record[$field->name] = $field->decode($tokens[$pos]);
        }
        return $record;
    }
}
