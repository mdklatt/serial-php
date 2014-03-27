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

    protected $classFilters;
    private $userFilters;
    private $filterIter;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct()
    {
        $this->userFilters = new ArrayObject();
        $this->classFilters = new ArrayObject();
        $this->filterIter = new AppendIterator();
        $this->filterIter->append($this->classFilters->getIterator());
        $this->filterIter->append($this->userFilters->getIterator());
        return;
    }

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
            $this->userFilters->exchangeArray(array());
            return;
        }
        foreach (func_get_args() as $callback) {            
            $this->userFilters[] = new Serial_Core_Callback($callback);
        }
        return;
    }

    /**
     * Write a record while applying filtering.
     *
     */
    public function write($record)
    {   
        foreach ($this->filterIter as $callback) {
            if (!($record = $callback->__invoke(array($record)))) {
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
