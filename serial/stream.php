<?php
/**
 * Tools for working with streams.
 *
 */

/**
 * The basic interface for an input stream.
 *
 * In this context, an input stream is anything which implements the Iterator 
 * interface to return lines of text. This default implementation is for basic 
 * PHP streams, i.e. anything that works with fgets(). This by itself is not 
 * especially useful, but this interface is intended for new stream types that 
 * would otherwise require the overkill of creating a new PHP stream wrapper.
 */
class Serial_IStreamAdaptor implements Iterator
{
    protected $stream;
    
    private $index = -1;
    private $line;
    
    /**
     * Initialize this object.
     *
     * The client code is responsible for opening and closing the stream.
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
        return;
    }
    
    /**
     * Return the entire stream as a single string.
     *
     */
    public function read()
    {
        $lines = iterator_to_array($this);
        return implode('', $lines);
    }
        
    /**
     * Iterator: Position the iterator at the first line.
     *
     */
    public function rewind() 
    { 
        // This is not a true rewind, but rather a one-time initialization to
        // support the iterator protocol, e.g. a foreach statement.
        if ($this->index != -1) {
            throw new RuntimeException('iterator cannot be rewound');
        }
        $this->next();
        return; 
    }
    
    /**
     * Iterator: Advance to the next line in the stream.
     *
     */
    public function next()
    {
        if (($this->line = fgets($this->stream)) !== false) {
            ++$this->index;
        }
        return;
    }
    
    /**
     * Iterator: Return true if the current stream position is valid.
     *
     */
    public function valid()
    {
        return $this->line !== false;
    }
    
    /**
     * Iterator: Return the current line.
     *
     */
    public function current()
    {
        return $this->line;
    }
    
    /**
     * Iterator: Return the current line number.
     *
     */
    public function key()
    {
        return $this->index;
    }
}
