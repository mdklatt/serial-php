<?php
/**
 * Reader types.
 *
 * Readers parse lines of text into data records.
 */

abstract class _Reader
implements Iterator
{
    const STOP_ITERATION = 0;  // false-y but not null
        
    protected $_stream;
    private $_filters = array();
    private $_current = null;
    
    /**
     * Abstract base class for all readers.
     *
     */
    
    public function __construct($stream)
    {
        $this->_stream = $stream;
        return;
    }

    /**
     * Clear all filters (default) or add a filter to this reader.
     *
     * A filter is a callback that accepts a data record as its only argument.
     * Based on this record the filter can perform the following actions:
     * 1. Return null to reject the record (the iterator will drop it).
     * 2. Return the data record as is.
     * 3. Return a new/modified record.
     * 4. Throw a StopIteration exception to stop iteration prior to the EOF.
     */
    public function filter($callback=null)
    {
        if (!$callback) {
            $this->_filters = array();
        }
        else {
            $this->_filters[] = $callback;
        }
        return;
    }

    /**
     * Iterator: Reset position to the first record.
     *
     * Not all stream types are rewindable, in which case this does nothing.
     */
    public function rewind()
    {
        @rewind($this->_stream);
        $this->next();
        return;
    }
    
    /**
     * Ierator: Advance position to the next valid record.
     *
     */
    public function next()
    {
        $this->_current = null;
        while (!$this->_current) {
            // Repeat until a record succesfully passes through all filters.
            if (!($record = $this->_get())) {
                break;  // EOF
            }
            foreach ($this->_filters as $callback) {
                // Pass this record through each filter.
                if (!($record = call_user_func($callback, $record))) {
                    break;
                }
            }
            if ($this->_current === self::STOP_ITERATION) {
                break;
            }
            $this->_current = $record;
         }
        return;        
    }
    
    public function valid()
    {
        return $this->_current == true;  // want implicit bool conversion
    }
    
    public function current()
    {
        return $this->_current;
    }
    
    public function key()
    {
        // Not implmented for streams.
        return;
    }
        
    abstract protected function _get();  // return null on EOF  
}


abstract class _TabularReader extends _Reader
{
    protected $_fields;
    
    public function __construct($stream, $fields, $endl="\n")
    {
        parent::__construct($stream);
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->_fields[$name] = new stdClass();
            $this->_fields[$name]->pos = $pos;
            $this->_fields[$name]->dtype = $dtype;            
        }
        $this->_endl = $endl;
        return;
    }
    
    protected function _get()
    {
        if (!($line = @fgets($this->_stream))) {
            return null;
        }
        $tokens = $this->_split(rtrim($line, $this->_endl));
        $record = array();
        $pos = 0;
        foreach ($this->_fields as $name => $field) {
            $record[$name] = $field->dtype->decode($tokens[$pos++]);
        }
        return $record;
    }
    
    abstract protected function _split($line);
}


class FixedWidthReader extends _TabularReader
{
    protected function _split($line)
    {
        $tokens = array();
        foreach ($this->_fields as $field) {
            list($beg, $len) = $field->pos;
            $tokens[] = substr($line, $beg, $len);
        }
        return $tokens;
    }
}


class DelimitedReader extends _TabularReader
{
    private $_delim;
    
    public function __construct($stream, $fields, $delim, $endl="\n")
    {
        parent::__construct($stream, $fields, $endl);
        $this->_delim = $delim;
    }
    
    protected function _split($line)
    {
        $line = explode($this->_delim, $line);
        $tokens = array();
        foreach ($this->_fields as $field) {
            $tokens[] = $line[$field->pos];
        }
        return $tokens;
    }
}
