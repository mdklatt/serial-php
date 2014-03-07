<?php
/**
 * A reader for tabular data consisting of fixed-width fields.
 *
 * The position of each field is given as a (begin, length) substring
 * expression where the end is null for a variable-length array.
 */
class Serial_Core_FixedWidthReader extends Serial_Core_TabularReader
{
    /**
     * Open a FixedWidhtReader with automatic stream handling.
     *
     * The first argument is a either an open stream or a path to use to open
     * a text file. In either case, the input stream will automatically be
     * closed when the reader object is destroyed. Any additional arguments are
     * passed along to the FixedWidthReader constructor.
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
            if (!($args[0] = @fopen($args[0], 'r'))) {
                $message = "invalid input stream or path: {$args[0]}";
                throw new RuntimeException($message);
            }
        }
        $class = new ReflectionClass('Serial_Core_FixedWidthReader');
        $reader = $class->newInstanceArgs($args);
        $reader->closing = true;  // take responsiblity for closing stream
        return $reader;
    }

    /**
     * Split a line of text into an array of string tokens.
     *
     * Lines are split based on the string position of each field.
     */
    protected function split($line)
    {
        $tokens = array();
        foreach ($this->fields as $field) {
            list($beg, $len) = $field->pos;
            if ($len === null) {
                $len = strlen($line);
            }
            $tokens[] = substr($line, $beg, $len);
        }
        return $tokens;
    }
}
