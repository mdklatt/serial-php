<?php
/**
 * Apply aggregate functions to input from another reader.
 *    
 */
class Serial_Core_AggregateReader extends Serial_Core_ReaderBuffer
{
    private $buffer = array();
    private $keyfunc;
    private $keyval;
    private $reductions = array();
    
    /**
     * Initialize this object.
     *
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
     * Add one or more reductions or clear all reductions (default).
     */
    public function reduce(/* $args */)
    {
        if (!($callbacks = func_get_args())) {
            // Clear all reductions.
            $this->reductions = array();
            return;
        }
        else {
            $this->reductions = array_merge($this->reductions, $callbacks);
        }
        return;
    }
    
    /**
     * Process an incoming record.
     */
    protected function queue($record)
    {
        $keyval = call_user_func($this->keyfunc, $record);
        if ($keyval != $this->keyval) {
            // This is a new group, finalize the buffered group.
            $this->flush();
        }
        $this->buffer[] = $record;
        $this->keyval = $keyval;
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
     * Apply reductions to buffered records and send to output queue.
     */
    private function flush()
    {
        if (!$this->buffer) {
            return;
        }
        $record = $this->keyval;
        foreach ($this->reductions as $callback) {
            $record = array_merge($record, call_user_func($callback, $this->buffer));
        }
        $this->output[] = $record;
        $this->buffer = null;
        return;
    }
}
