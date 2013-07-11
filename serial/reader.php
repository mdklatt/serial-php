<?php
/**
 * Reader types.
 *
 * Readers parse lines of text into data records.
 */


/**
 * Base class for all readers.
 *
 */
abstract class Serial_Reader implements Iterator
{
    const STOP_ITERATION = 0;  // false-y but not null
        
    private $filters = array();
    private $current = null;
        
    /**
     * Clear all filters (default) or add filters to this reader.
     *
     * A filter is a callback that accepts a data record as its only argument.
     * Based on this record the filter can perform the following actions:
     * 1. Return null to reject the record (the iterator will drop it).
     * 2. Return the data record as is.
     * 3. Return a new/modified record.
     * 4. Return STOP_ITERATION to signal the end of input.
     */
    public function filter(/* variadic: $callbacks */)
    {
        if (func_num_args() == 0) {
            // Default: clear all filters.
            $this->filters = array();
            return;
        }
        foreach (func_get_args() as $callback) {
            if (is_array($callback)) {
                $this->filters = array_merge($this->filters, $callback);
            } 
            else {
                $this->filters[] = $callback;
            }            
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
        // support the iterator protocol, e.g. a foreach statement. Derived
        // classes which support rewinding should override this, being sure
        // to call next() once the rewound stream is positioned at the first
        // data record.
        $this->next();
        return;
    }
    
    /**
     * Ierator: Advance position to the next valid record.
     *
     */
    public function next()
    {
        $this->current = null;
        while (!$this->current) {
            // Repeat until a record succesfully passes through all filters.
            if (!($record = $this->get())) {
                break;  // EOF
            }
            foreach ($this->filters as $callback) {
                // Pass this record through each filter.
                if (!($record = call_user_func($callback, $record))) {
                    break;
                }
            }
            if ($this->current === self::STOP_ITERATION) {
                break;
            }
            $this->current = $record;
         }
        return;        
    }
    
    public function valid()
    {
        return $this->current == true;  // want implicit bool conversion
    }
    
    public function current()
    {
        return $this->current;
    }
    
    public function key()
    {
        // Not implmented for streams.
        return;
    }
        
    abstract protected function get();  // return null on EOF  
}


abstract class Serial_TabularReader extends Serial_Reader
{
    protected $stream;
    protected $fields;
    
    public function __construct($stream, $fields, $endl="\n")
    {
        $this->stream = $stream;
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->fields[$name] = new Serial_Field($pos, $dtype);
        }
        $this->endl = $endl;
        return;
    }
    
    protected function get()
    {
        if (!($line = @fgets($this->stream))) {
            return null;
        }
        $tokens = $this->split(rtrim($line, $this->endl));
        $record = array();
        $pos = 0;
        foreach ($this->fields as $name => $field) {
            $record[$name] = $field->dtype->decode($tokens[$pos++]);
        }
        return $record;
    }
    
    abstract protected function split($line);
}


class Serial_FixedWidthReader extends Serial_TabularReader
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


class Serial_DelimitedReader extends Serial_TabularReader
{
    private $delim;
    
    public function __construct($stream, $fields, $delim, $endl="\n")
    {
        parent::__construct($stream, $fields, $endl);
        $this->delim = $delim;
        return;
    }
    
    protected function split($line)
    {
        $line = explode($this->delim, $line);
        $tokens = array();
        foreach ($this->fields as $field) {
            if (is_array($field->pos)) {
                // Token is an array.
                list($beg, $len) = $field->pos;
                $tokens[] = array_slice($line, $beg, $len);
            }
            else {
                $tokens[] = $line[$field->pos];                
            }
        }
        return $tokens;
    }
}
