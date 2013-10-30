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
                $this->uflow();  // throws EofException
            }
        }
        return array_shift($this->output); 
    }
    
    /**
     * Process each incoming record.
     *
     * This is called for each record that is read from the input reader.
     * An EofException can be used to signal an EOF condition prior to the
     * normal end of input.
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
        // An EofException must be used to signal the end of input.
        throw new Serial_Core_EofException();
    }
}
