<?php
/**
 * Aggregate output for another writer.
 * 
 * During aggregation, records are grouped, reduction functions are applied to
 * each group, and a single record is returned for each group. Input records 
 * are presumed to be already sorted such that all records in a group are
 * group contiguous.
 */
class Serial_Core_AggregateWriter extends Serial_Core_WriterBuffer
{
    private $buffer = array();
    private $keyfunc;
    private $keyval;
    private $reductions = array();
    
    /**
     * Initialize this object.
     *
     * The $key argument is either a single field name, an array of names, or a
     * key function. A key function must return an associative array containing
     * the name and value for each key field. Key functions are free to create
     * key fields that are not in the incoming data.
     */
    public function __construct($writer, $key)
    {
        parent::__construct($writer);
        if (!is_callable($key)) {
            // Use the default key function.
            $key = array(new Serial_Core_KeyFunc($key), '__invoke');
        }
        $this->keyfunc = $key;
        return;
    }

    /**
     * Add one or more reductions or clear all reductions (default).
     *
     * A reduction is a callable object that takes an array of records and
     * aggregates them into a single associative array keyed by field name.
     * A reduction can return on or more fields. A reduction is free to crate 
     * new fields, and, conversely, fields that do not have a reduction will 
     * not be in the aggregated data. The `CallbackRedcution` class can be used 
     * to generate a reduction from basic array functions like `array_sum`.
     *
     * Reductions are applied in order to each group of records, and the
     * results are merged to create one record per group. If multiple
     * reductions return a field with the same name, the latter value will 
     * overwrite the existing value.
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
     * Apply reductions to buffered records and send to output queue.
     */
    protected function flush()
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
