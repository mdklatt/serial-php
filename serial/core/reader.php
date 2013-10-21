<?php
/**
 * Reader types.
 *
 * Readers parse lines of text into data records.
 */


class Serial_Core_EofException extends Exception
{
    /**
     * Initialize this object.
     *
     */
    public function __construct()
    {
        parent::__construct('EOF');
        return;
    }
}


/**
 * Base class for all readers.
 *
 */
abstract class Serial_Core_Reader implements Iterator
{
    //const EOF = 0;  // must be false-y but can't be null
        
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
     * 4. Return Serial_Core_Reader::EOF to signal the end of input.
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
                // filters or EOF.
                $this->record = $this->get();  // throws EofException
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
        catch (Serial_Core_EofException $ex) {
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
     * Return the next parsed record or EOF.
     *
     */   
    abstract protected function get();
}


abstract class Serial_Core_TabularReader extends Serial_Core_Reader
{
    protected $stream;
    protected $fields;
    
    /**
     * Initialize this object.
     *
     * The input stream must be an instance of a Serial_Core_IStreamAdaptor or a
     * regular PHP stream that works with fgets().
     */
    public function __construct($stream, $fields, $endl="\n")
    {
        if ($stream instanceof Serial_Core_IStreamAdaptor) {
            $this->stream = $stream;
        }
        else {
            $this->stream = new Serial_Core_IStreamAdaptor($stream);
        }
        $this->stream->rewind();
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->fields[$name] = new Serial_Core_Field($pos, $dtype);
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
            throw new Serial_Core_EofException();
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


class Serial_Core_FixedWidthReader extends Serial_Core_TabularReader
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


class Serial_Core_DelimitedReader extends Serial_Core_TabularReader
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
