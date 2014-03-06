<?php
/**
 * A reader for tabular data consisting of character-delimited fields.
 *
 * The position of each scalar field is be given as an integer index, and the
 * position of an array field is a (begin, length) pair where the length is
 * null for a variable-length array.
 *
 */
class Serial_Core_DelimitedReader extends Serial_Core_TabularReader
{
    // TODO: Add default delimiter to split on all whitespace.
    // TODO: Add delimiter escaping.
    
    /**
     * Open a DelimitedReader with automatic stream handling.
     *
     * The first argument is a either an open stream or a path to use to open
     * a text file. In either case, the input stream will automatically be
     * closed when the reader object is destroyed. Any additional arguments are
     * passed along to the DelimitedReader constructor.
     */
    public static function open(/* $args */)
    {
        // Every derived class *MUST* implement its own open() method that
        // returns the correct type of object.
        $args = func_get_args();
        if (count($args) < 3) {
            $message = 'call to open() is missing required arguments';
            throw new BadMethodCallException($message);
        }
        if (!is_resource($args[0])) {
            // Assume this is a string to use as a file path.
            $args[0] = fopen($args[0], 'r');
        }
        $class = new ReflectionClass('Serial_Core_DelimitedReader');
        $reader = $class->newInstanceArgs($args);
        $reader->closing = true;  // take responsiblity for closing stream
        return $reader;
    }
    
    private $delim;
    
    /**
     * Iniialize this object.
     *
     */
    public function __construct($stream, $fields, $delim, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, $endl);
        $this->delim = $delim;
        return;
    }
    
    /**
     * Split a line of text into an array of string tokens.
     *
     * Lines are split at each occurrence of the delimiter; the delimiter is
     * discarded.
     */
    protected function split($line)
    {
        $line = explode($this->delim, $line);
        $tokens = array();
        foreach ($this->fields as $field) {
            if (is_array($field->pos)) {
                // Token is an array.
                list($beg, $len) = $field->pos;
                $tokens[] = array_slice($line, $beg, $len);
            }
            else {
                $tokens[] = $line[$field->pos];                
            }
        }
        return $tokens;
    }
}
