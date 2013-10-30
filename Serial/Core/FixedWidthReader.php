<?php
/**
 * A reader for tabular data with fixed-width fields.
 *
 */
class Serial_Core_FixedWidthReader extends Serial_Core_TabularReader
{
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
