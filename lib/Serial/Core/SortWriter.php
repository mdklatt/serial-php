<?php
/**
 * Sort output for another writer.
 * 
 */
class Serial_Core_SortWriter extends Serial_Core_WriterBuffer
{
    // TODO: Add support for key functions.
    // TODO: Add support for grouping.

    private $buffer = array();
    
    /**
     * Initialize this object.
     *
     * The $key argument is either a single field name or an array of names.
     */
    public function __construct($writer, $key)
    {
        parent::__construct($writer);
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
     * Send sorted records to the output queue.
     */
    protected function flush()
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
