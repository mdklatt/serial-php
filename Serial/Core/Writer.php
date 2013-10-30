<?php
/**
 * Base class for all writers.
 *
 */
abstract class Serial_Core_Writer
{
    private $filters = array();
    
    /**
     * Clear all filters (default) or add a filter to this writer.
     *
     * A filter is a callback that accepts a data record as its only argument.
     * Based on this record the filter can perform the following actions:
     * 1. Return null to reject the record (the iterator will drop it).
     * 2. Return the data record as is.
     * 3. Return a new/modified record.
     */
    public function filter(/* $args */)
    {
        $this->filters = array();
        foreach (func_get_args() as $callback) {            
            // PHP 5.2 workaround: Check for callable objects and call __invoke
            // explicitly.
            if (method_exists($callback, '__invoke')) {
                $callback = array($callback, '__invoke');
            }
            $this->filters[] = $callback;
        }
        return;
    }


    public function write($record)
    {   
        foreach ($this->filters as $callback) {
            if (!($record = call_user_func($callback, $record))) {
                return;
            }
        }
        $this->put($record);
        return;
    }

    public function dump($records)
    {
        foreach ($records as $record) {
            $this->write($record);
        }
        return;
    }
        
    abstract protected function put($record); 
}
