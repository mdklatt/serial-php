<?php
/**
 * Abstract base class for all Writer buffers.
 * 
 * A WriterBuffer applies preprocessing to records being written to another
 * Writer.
 */
abstract class Serial_Core_WriterBuffer extends Serial_Core_Writer
{
    protected $output = array();  // FIFO
    private $writer;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($writer)
    {
        $this->writer = $writer;
        return;
    }
    
    /**
     * Write a record while applying buffering and filtering.
     *
     */
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

    /**
     * Close the buffer.
     *
     * This is a signal to flush any remaining records in the buffer to the
     * output writer. No further writes should be done to this buffer. This
     * does not close the destination writer itself.
     *
     * If multiple WriterBuffers are being chained, their close() methods
     * should be called in the correct order (outermost buffer first).
     */
    public function close()
    {
        if (!$this->writer) {
            // Buffer is already closed.
            return;
        }
        $this->flush();
        foreach ($this->output as $record) {
            // Base class applies filters.
            parent::write($record);
        }
        $this->output = null;
        $this->writer = null;
        return;
    }

    /**
     * Write all records while applying buffing and filtering.
     *
     * This automaically calls close().
     */ 
    public function dump($records)
    {
        parent::dump($records);
        $this->close();
        return;
    }
        
    /**
     * Send a buffered record to the destination writer.
     *
     * This is called after the record has passed through all this buffer's
     * filters.
     */
    protected function put($record)
    {
        // At this point the record as already been buffered and filtered.
        $this->writer->write($record);
    }
    
    /**
     * Process an incoming record.
     *
     */
    abstract protected function queue($record);

    /**
     * Complete any buffering operations.
     *
     * This is called as soon as close() is called, so the derived class has
     * on last chance to do something. There will be no more records to
     * process, so any remaining records in the buffer should be queued for
     * output.
     */
    protected function flush()
    {
        return;
    }
}
