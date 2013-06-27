<?php
/**
 * Buffer classes.
 *
 */
require_once 'reader.php';
require_once 'writer.php';

/**
 * A buffer for Readers
 * 
 */
abstract class _ReaderBuffer extends _Reader
{
    protected $_output = array();  // FIFO
    
    private $_reader;
    
    public function __construct($reader)
    {
        $this->_reader = $reader;
        return;
    }

    protected function _get()
    {
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
        return array_shift($this->_output);
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
abstract class _WriterBuffer extends _Writer
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
            // Base class applies filters.
            parent::write($record);
        }
        $this->_output = array();
        return;           
    }
       
    public function dump($records)
    {
        parent::dump($records);
        $this->close();
        return;
    }
    
    public function close()
    {
        $this->_flush();
        foreach ($this->_output as $record) {
            // Base class applies filters.
            parent::write($record);
        }
        $this->_output = null;
        $this->_writer = null;
        return;
    }
    
    protected function _put($record)
    {
        // At this point the record as already been buffered and filtered.
        $this->_writer->write($record);
    }
    
    abstract protected function _queue($record);

    protected function _flush()
    {
        return;
    }
}