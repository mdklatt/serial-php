<?php
/**
 * Base class for all Readers.
 *
 * Serial data consists of sequential records. A Reader iterates over serial
 * data and allows for preprocessing of the data using filters.
 */
abstract class Serial_Core_Reader implements Iterator
{
    private $filters = array();
    private $record;
    private $index = -1;
        
    /**
     * Clear all filters (default) or add filters to this reader.
     *
     * A filter is a callback that accepts a data record as its only argument.
     * Based on this record the filter can perform the following actions:
     * 1. Return null to reject the record (the iterator will drop it).
     * 2. Return the data record as is.
     * 3. Return a new/modified record.
     * 4. Throw a Serial_Core_StopIteration exception to end input.
     */
    public function filter(/* $args */)
    {
        $this->filters = array();
        foreach (func_get_args() as $callback) {            
            // PHP 5.2 workaround for callable objects.
            if (method_exists($callback, '__invoke')) {
                $callback = array($callback, '__invoke');
            }
            $this->filters[] = $callback;
        }
        return;
    }

    /**
     * Iterator: Position the iterator at the first valid record.
     *
     */
    public function rewind()
    {
        // This is not a true rewind, but rather a one-time initialization to
        // support the iterator protocol, e.g. a foreach statement. This can't
        // be moved to __construct() because any filters aren't in place yet.
        $this->next();
        return;
    }
    
    /**
     * Iterator: Advance position to the next valid record.
     *
     */
    public function next()
    {
        try {
            while (true) {
                // Repeat until a record successfully passes through all 
                // filters or a StopIteration exception is thrown.
                $this->record = $this->get();
                foreach ($this->filters as $callback) {
                    $this->record = call_user_func($callback, $this->record);
                    if ($this->record === null) {
                        continue 2;                        
                    }
                }
                ++$this->index;
                break;
            }            
        }
        catch (Serial_Core_StopIteration $ex) {
            $this->record = null;
        }
        return;
    }
    
    /**
     * Iterator: Return true if the current iterator position is valid.
     *
     */
    public function valid()
    {
        return $this->record !== null;
    }
    
    /**
     * Iterator: Return the current record.
     *
     */
    public function current()
    {
        return $this->record;
    }
    
    /**
     * Iterator: Return a unique key for the current record.
     *
     */
    public function key()
    {
        return $this->index;
    }
    
    /**
     * Return the next parsed record.
     *
     * A StopIteration exception must be thrown when there is no more input.
     */   
    abstract protected function get();
}
