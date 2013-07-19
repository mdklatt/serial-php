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
    public function filter(/* variadic */)
    {
        $callbacks = func_get_args();
        if (!$callbacks) {
            // Default: clear all filters.
            $this->filters = array();
            return;
        }
        foreach (func_get_args() as $callback) {
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
    
    /**
     * Iterator: Return true if the current iterator position is valid.
     *
     */
    public function valid()
    {
        return $this->current == true;  // want implicit bool conversion
    }
    
    /**
     * Iterator: Return the current record.
     *
     */
    public function current()
    {
        return $this->current;
    }
    
    public function key()
    {
        // Not implmented for streams.
        return;
    }
    
    /**
     * Return the next parsed record or null for EOF.
     *
     */   
    abstract protected function get();
}


abstract class Serial_TabularReader extends Serial_Reader
{
    protected $stream;
    protected $fields;
    
    /**
     * Initialize this object.
     *
     * The input stream must be an instance of a Serial_IStreamAdaptor or a
     * regular PHP stream that works with fgets().
     */
    public function __construct($stream, $fields, $endl="\n")
    {
        if ($stream instanceof Serial_IStreamAdaptor) {
            $this->stream = $stream;
        }
        else {
            $this->stream = new Serial_IStreamAdaptor($stream);
        }
        $this->stream->rewind();
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->fields[$name] = new Serial_Field($pos, $dtype);
        }
        $this->endl = $endl;
        return;
    }
    
    /**
     * Split a line of text into tokens.
     *
     */
    abstract protected function split($line);

    /**
     * Retrieve the next parsed data record from the stream.
     * 
     */
    protected function get()
    {
        if (!$this->stream->valid()) {
            return null;  // EOF
        }
        $tokens = $this->split(rtrim($this->stream->current(), $this->endl));
        $this->stream->next();
        $record = array();
        $pos = 0;
        foreach ($this->fields as $name => $field) {
            $record[$name] = $field->dtype->decode($tokens[$pos++]);
        }
        return $record;
    }    
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
