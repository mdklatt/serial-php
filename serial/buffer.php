<?php
/**
 * Buffer classes.
 *
 */
    
/**
 * A buffer for Readers
 * 
 */
abstract class Serial_ReaderBuffer extends Serial_Reader
{
    protected $output = array();  // FIFO
    
    private $reader;
    
    public function __construct($reader)
    {
        $this->reader = $reader;
        $this->reader->rewind();
        return;
    }

    /**
     * Return the next buffered record.
     *
     */
    protected function get()
    {
        while (!$this->output) {
            if ($this->reader && $this->reader->valid()) {
                $this->queue($this->reader->current());
                $this->reader->next();                
            }
            else {
                // Underflow condition.
                $this->reader = null;
                if (!$this->uflow()) {
                    break;
                }
            }
        }
        return array_shift($this->output); 
    }
    
    /**
     * Process each incoming record.
     *
     * This is called for each record that is read from the input reader.
     */
    abstract protected function queue($record);

    /**
     * Handle an underflow condition.
     *
     * This is called if the output queue is empty and the input reader is no
     * longer valid. Derived classes should override it as necessary.
     */
    protected function uflow()
    {
        // Return true if the next call to get() will succeed or false to 
        // signal the end of input.
        return false;
    }
}


/**
 * A buffer for Writers.
 * 
 */
abstract class Serial_WriterBuffer extends Serial_Writer
{
    protected $output = array();  // FIFO
    
    private $writer;
    
    public function __construct($writer)
    {
        $this->writer = $writer;
        return;
    }
    
    public function write($record)
    {
        $this->queue($record);
        foreach ($this->output as $record) {
            // Base class applies filters.
            parent::write($record);
        }
        $this->output = array();
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
        $this->flush();
        foreach ($this->output as $record) {
            // Base class applies filters.
            parent::write($record);
        }
        $this->output = null;
        $this->writer = null;
        return;
    }
    
    protected function put($record)
    {
        // At this point the record as already been buffered and filtered.
        $this->writer->write($record);
    }
    
    abstract protected function queue($record);

    protected function flush()
    {
        return;
    }
}
