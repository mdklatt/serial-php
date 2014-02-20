<?php
/**
 * A buffer for Readers
 * 
 */
abstract class Serial_Core_ReaderBuffer extends Serial_Core_Reader
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
                $this->uflow();  // throws StopIteration
            }
        }
        return array_shift($this->output); 
    }
    
    /**
     * Process each incoming record.
     *
     * This is called for each record that is read from the input reader.
     * A StopIteration exception can be used to signal the end of input prior
     * to an EOF condition.
     */
    abstract protected function queue($record);

    /**
     * Handle an underflow condition.
     *
     * This is called if the output queue is empty and the input reader has
     * been exhausted. Derived classes may override it as necessary. A
     * StopIteration exception must be thrown when there is no more input.
     */
    protected function uflow()
    {
        throw new Serial_Core_StopIteration();
    }
}
