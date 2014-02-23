<?php
/**
 * Abstract base class for all Reader buffers.
 *    
 * A ReaderBuffer applies postprocessing to records being read from another 
 * Reader.
 */
abstract class Serial_Core_ReaderBuffer extends Serial_Core_Reader
{
    protected $output = array();  // FIFO
    
    private $reader;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($reader)
    {
        $this->reader = $reader;
        $this->reader->rewind();
        return;
    }

    /**
     * Read the next record from the input reader.
     *
     * This is called before any of this buffer's filters have been applied, 
     * but the input reader will have already applied its own filters.
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
                $this->uflow();  // throws StopIteration
            }
        }
        return array_shift($this->output); 
    }
    
    /**
     * Process an incoming record.
     *
     * This is called for each record that is read from the input reader.
     * A StopIteration exception can be used to signal the end of input prior
     * to an EOF condition.
     */
    abstract protected function queue($record);

    /**
     * Handle an underflow condition.
     *
     * This is called to retrieve additional records if the output queue is 
     * empty and the input reader has been exhausted. Derived classes can 
     * override this as necessary. A StopIteration exception must be thrown
     * to signal that there are no more records in the buffer. 
     */
    protected function uflow()
    {
        throw new Serial_Core_StopIteration();
    }
}
