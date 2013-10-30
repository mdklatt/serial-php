<?php
/**
 * A writer for tabular data with fixed-width fields.
 *
 */
class Serial_Core_FixedWidthWriter extends Serial_Core_DelimitedWriter
{
    public function __construct($stream, $fields, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, '', $endl);
        return;
    }
}
