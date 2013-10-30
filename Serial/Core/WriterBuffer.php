<?php
/**
 * A buffer for Writers.
 * 
 */
abstract class Serial_Core_WriterBuffer extends Serial_Core_Writer
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
