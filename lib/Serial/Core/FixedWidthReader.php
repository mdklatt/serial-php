<?php
namespace Serial\Core;

/**
 * A reader for tabular data consisting of fixed-width fields.
 *
 * The position of each field is given as a (begin, length) substring
 * expression where the end is null for a variable-length array.
 */
class FixedWidthReader extends TabularReader
{
    /**
     * Return the class name.
     *
     * This *MUST* be implemented by all derived classes. It is sufficient to
     * copy this function verbatim.
     */
    protected static function className()
    {
        return __CLASS__;
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
