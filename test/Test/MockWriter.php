<?php
/**
 * Mock writer for test purposes.
 */
class Test_MockWriter
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
