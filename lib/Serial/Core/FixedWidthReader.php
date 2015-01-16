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
     * Open a FixedWidthReader with automatic stream handling.
     *
     * The first argument is either an open stream or a path to open as a text
     * file. In either case, the input stream will automatically be closed when
     * the reader's destructor is called. Any additional arguments are passed
     * along to the FixedWidthReader constructor.
     *
     * If the object contains a circular reference, e.g. a class method used
     * as a filter callback, unsetting the variable is not enough to trigger
     * the destructor. It will be called when the process ends, or it can be 
     * called explicitly, i.e. $reader->__destruct().
     */
    public static function open(/* $args */)
    {
        // Every derived class must implement its own open() method that calls
        // openReader() with the correct class name. This is a workaround for
        // PHP 5.2's lack of late static binding.
        $args = func_get_args();
        array_unshift($args, __CLASS__);
        return parent::openReader($args);
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
