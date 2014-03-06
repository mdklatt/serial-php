<?php
/**
 * Base class for all writers.
 *
 * Serial data consists of sequential records. A Writer provides an interface
 * interface for writing serial data and allows for postprocessing of the data
 * using filters.
 */
abstract class Serial_Core_Writer
{
    // Class filters are always applied after any user filters. Derived 
    // classes can use these to do any final data manipulation before the
    // record is written to the stream.

    // NB: Adding a method of $this as a class or user filter creates a
    // circular reference that prevents its destructor from being called
    // automatically when it reference becomes undefined, e.g. unset($writer).
    // The destructor will be called when the process exits, or call it
    // explicitly, e.g. $writer->__destruct().

    protected $classFilters = array();
    private $userFilters = array();
    
    /**
     * Add filters to this writer or clear all filters (default).
     *
     * A filter is a callback that accepts a data record as its only argument.
     * Based on this record the filter can perform the following actions:
     * 1. Return null to reject the record (the iterator will drop it).
     * 2. Return the data record as is.
     * 3. Return a new/modified record.
     */
    public function filter(/* $args */)
    {
        // This does not affect class filters.
        if (!($callbacks = func_get_args())) {
            // Clear all filters.
            $this->userFilters = array();
            return;
        }
        foreach (func_get_args() as $callback) {            
            if (method_exists($callback, '__invoke')) {
                // PHP 5.2 workaround for callable objects.
                $callback = array($callback, '__invoke');
            }
            $this->userFilters[] = $callback;
        }
        return;
    }

    /**
     * Write a record while applying filtering.
     *
     */
    public function write($record)
    {   
        # TODO: array_merge() probably doesn't need to be done with each write.
        $filters = array_merge($this->userFilters, $this->classFilters);
        foreach ($filters as $callback) {
            if (!($record = call_user_func($callback, $record))) {
                return;
            }
        }
        $this->put($record);
        return;
    }

    /**
     * Write all records while applying filters.
     *
     */
    public function dump($records)
    {
        foreach ($records as $record) {
            $this->write($record);
        }
        return;
    }
     
    /**
     * Put a formatted record into the output stream. 
     *
     * This is called after the record has passed through all filters.
     */   
    abstract protected function put($record); 
}
