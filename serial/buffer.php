<?php
/**
 * Buffer classes.
 *
 */


/**
 * A buffer for Readers
 * 
 */
abstract class _ReaderBuffer
implements Iterator
{
    protected $_output = array();  // FIFO
    
    private $_reader;
    
    public function __construct($reader)
    {
        $this->_reader = $reader;
        return;
    }

    /**
     * Iterator: Reset the buffer.
     *
     * Not all readers are rewindable, in which case this does nothing.
     */
    public function rewind()
    {
        @rewind($this->_reader);
        $this->next();
        return;
    }
    
    /**
     * Iterator: Advance to the next record in the output queue.
     *
     */
    public function next()
    {
        array_shift($this->_output);
        while (!$this->_output && $this->_reader) {
            // Retrieve input from the reader until a record is available for
            // output or the reader is exhausted.
            if (!$this->_reader->valid()) {
                // The reader is exhausted, but there may still be some records
                // in the buffer.
                $this->_reader = null;
                $this->_flush();
                break;
            }
            $this->_queue($this->_reader->current());
            $this->_reader->next();
        }
        return;
    }
    
    /**
     * Iterator: Return true if the output queue is not empty.
     *
     */
    public function valid()
    {
        return $this->_output;
    }
    
    /**
     * Return the first record in the output queue.
     *
     */
    public function current()
    {
        return $this->_output[0];
    }
    
    public function key()
    {
        // Not implmented for streams.
        return;
    }

    
    abstract protected function _queue($record);

    protected function _flush()
    {
        return;
    }
}


/**
 * A buffer for Writers.
 * 
 */
abstract class _WriterBuffer
{
    protected $_output = array();  // FIFO
    
    private $_writer;
    
    public function __construct($writer)
    {
        $this->_writer = $writer;
        return;
    }
    
    public function write($record)
    {
        $this->_queue($record);
        foreach ($this->_output as $record) {
            $this->_writer->write($record);
        }
        $this->_output = array();
        return;           
    }
    
    public function dump($records)
    {
        foreach($records as $record) {
            $this->write($record);
        }
        $this->close();
        return;
    }
    
    public function close()
    {
        $this->_flush();
        foreach ($this->_output as $record) {
            $this->_writer->write($record);
        }
        $this->_output = array();
        $this->_writer = null;
        return;
    }
    
    abstract protected function _queue($record);

    protected function _flush()
    {
        return;
    }
}