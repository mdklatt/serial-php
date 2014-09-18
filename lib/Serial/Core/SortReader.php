<?php
/**
 * Sort input from another reader.
 * 
 */
class Serial_Core_SortReader extends Serial_Core_ReaderBuffer
{
    // Except for the additional uflow() method this has essentially the same
    // implementation as and should be kept in sync with SortWriter. Hooray for
    // the lack of multiple inheritence!
    // TODO: Add support for grouping.
    // TODO: Factor common sorting code out as some kind of Sorter object?
    
    private $buffer = array();
    private $keyfunc;
    
    
    /**
     * Initialize this object.
     *
     * The $key argument is either a single field name or an array of names.
     */
    public function __construct($reader, $key)
    {
        parent::__construct($reader);
        if (!is_callable($key)) {
            // Use the default key function.
            $key = array(new Serial_Core_KeyFunc($key), '__invoke');
        }
        $this->keyfunc = $key;
        return;
    }

    /**
     * Process an incoming record.
     */
    protected function queue($record)
    {
        $this->buffer[] = $record;
        return;
    }
    
    /**
     * Handle an underflow condition.
     *
     * This is called when the input reader is exhuasted and there are no
     * records in the output queue.
     */
    protected function uflow()
    {
        if (!$this->buffer) {
            throw new Serial_Core_StopIteration();
        }
        $this->flush();
        return;
    }
    
    /**
     * Send sorted records to the output queue.
     */
    private function flush()
    {
        if (!$this->buffer) {
            return;
        }
        $keycols = array();
        foreach ($this->buffer as $row => $record) {
            // Build an N x K array of key values to use to with multisort.
            $keycols[] = call_user_func($this->keyfunc, $record); 
        }
        array_multisort($keycols, $this->buffer);
        $this->output = $this->buffer;
        $this->buffer = array();
        return;
    }
}
