<?php
/**
 * Sort input from another reader.
 * 
 */
class Serial_Core_SortReader extends Serial_Core_ReaderBuffer
{
    // TODO: Add support for key functions.
    // TODO: Add support for grouping.
    
    private $buffer = array();
    
    /**
     * Initialize this object.
     *
     * The $key argument is either a single field name or an array of names.
     */
    public function __construct($reader, $key)
    {
        parent::__construct($reader);
        $this->keys = is_array($key) ? array_values($key) : array($key);
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
            foreach ($this->keys as $col => $key) {
                $keycols[$row][$col] = $record[$key];
            }
        }
        array_multisort($keycols, $this->buffer);
        $this->output = $this->buffer;
        $this->buffer = array();
        return;
    }
}
