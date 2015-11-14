<?php
namespace Serial\Core;

/**
 * Base class for tabular data readers.
 *
 * Tabular data is organized into named fields such that each field occupies
 * the same position in each input record. One line of text corresponds to one
 * complete record.
 */
abstract class TabularReader extends Reader
{
    /**
     * Create a reader with automatic stream handling.
     *
     * The first argument is a Reader class name, and the next argument is 
     * either an open stream or a path to open as a text file. In both cases,
     * the input stream will automatically be closed when the the Reader's 
     * destructor is called. Any additional arguments are passed along to the
     * Reader's constructor. Derived classes should implement their own static 
     * open() method that calls this method with the appropriate class name.
     *
     * If the object contains a circular reference, e.g. a class method used
     * as a filter callback, unsetting the variable is not enough to trigger
     * the destructor. It will be called when the process ends, or it can be 
     * called explicitly, i.e. $reader->__destruct().
     */
    protected static function openReader($args)
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
                 throw new \RuntimeException($message);
             }
         }
         $class = new \ReflectionClass($className);
         $reader = $class->newInstanceArgs($args);
         $reader->closing = true;  // take responsibility for closing stream
         return $reader;
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
            close($this->stream);
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
            throw new StopIteration();
        }
        $tokens = $this->split(rtrim($line, $this->endl));
        $record = array();
        foreach ($this->fields as $pos => $field) {
            $record[$field->name] = $field->decode($tokens[$pos]);
        }
        return $record;
    }
}
