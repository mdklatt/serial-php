<?php
/**
 * Base class for all Readers.
 *
 * Serial data consists of sequential records. A Reader iterates over serial
 * data and allows for preprocessing of the data using filters.
 */
abstract class Serial_Core_Reader implements RecursiveIterator
{
    // Class filters are always applied before any user filters. Derived
    // classes can use these to do any preliminary data manipulation after
    // the record is parsed. 
    
    // NB: Adding a method of $this as a class or user filter creates a
    // circular reference that prevents its destructor from being called
    // automatically when it reference becomes undefined, e.g. unset($reader).
    // The destructor will be called when the process exits, or call it
    // explicitly, e.g. $reader->__destruct().
    
    private $classFilters;
    private $userFilters;
    private $filterIter;
    private $record;
    private $index = -1;
    
    /**
     * Initialize this object.
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
     * Add filters to this reader or clear all filters (default).
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
        // This does not affect class filters.
        if (!($callbacks = func_get_args())) {
            // Clear all filters.
            $this->userFilters->exchangeArray(array());
            return;
        }
        foreach ($callbacks as $callback) {            
            $this->userFilters[] = $callback;
        }
        return;
    }

    /**
     * Position the iterator at the first valid record.
     */
    public function rewind()
    {
        // This is not a true rewind, but rather a one-time initialization to
        // comply with the Iterator interface.  
        $this->next();
        return;
    }
    
    /**
     * Advance to the next valid record while applying filtering.
     */
    public function next()
    {
        // TODO: Implement this by extending FilterIterator?
        while (true) {
            // Repeat until a record successfully passes through all filters or
            // the end of input.
            try {
                $this->record = $this->get();
                foreach ($this->filterIter as $callback) {
                    $this->record = call_user_func($callback, $this->record);
                    if ($this->record === null) {
                        // This record failed a filter, try the next one.
                        continue 2;                        
                    }
                }
                ++$this->index;
            }
            catch (Serial_Core_StopIteration $ex) {
                // EOF or a filter signaled the end of valid input.
                $this->record = null;
            }
            break;
        }           
        return;
    }
    
    /**
     * Return true if the current iterator position is valid.
     */
    public function valid()
    {
        return $this->record !== null;
    }
    
    /**
     * Return the current filtered record.
     */
    public function current()
    {
        return $this->record;
    }
    
    /**
     * Return an index for the current record.
     *
     * Key values are guaranteed to be unique for every record (assuming no
     * overflow; see PHP_INT_MAX), but do not have any specific meaning.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * RecursiveIterator: Return true if the current element is iterable.
     */
    public function hasChildren()
    {
        // If this returns true, the leaves will be individual record fields.
        // For now, return false to make each record a leaf as required by
        // ChainReader.
        return false;
    }
    
    /**
     * RecursiveIterator: Return a RecursiveIterator for the current record.
     */
    public function getChildren()
    {
        return new RecursiveArrayIterator($this->current());
    }

    /**
     * Add class filters to the reader.
     */
    protected function classFilter(/* $args */)
    {
        foreach (func_get_args() as $callback) {            
            $this->classFilters[] = $callback;
        }
        return;
    }    
    
    /**
     * Get the next parsed record from the input stream.
     *
     * This is called before any filters have been applied. The implementation 
     * must raise a Serial_Core_StopIteration exception to signal when input 
     * has been exhausted.
     */   
    abstract protected function get();
}
