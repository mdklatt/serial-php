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
        return;
    }

    protected function get()
    {
        while (!$this->output && $this->reader) {
            // Retrieve input from the reader until a record is available for
            // output or the reader is exhausted.
            if (!$this->reader->valid()) {
                // The reader is exhausted, but there may still be some records
                // in the buffer.
                $this->reader = null;
                $this->flush();
                break;
            }
            $this->queue($this->reader->current());
            $this->reader->next();
        }
        return array_shift($this->output);
    }
    
    abstract protected function queue($record);

    protected function flush()
    {
        return;
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