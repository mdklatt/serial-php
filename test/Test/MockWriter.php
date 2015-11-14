<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Mock writer for test purposes.
 */
class MockWriter
{
    public $output = array();
    
    /**
     * Write a record.
     */
    public function write($record)
    {
        $this->output[] = $record;
    }
}
