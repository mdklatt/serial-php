<?php
/**
 * Sort input from another reader.
 * 
 */
class Serial_Core_SortReader extends Serial_Core_ReaderBuffer
{
    private $buffer;
    
    /**
     * Initialize this object.
     *
     * The $key argument is either a single field name, an array of names, or
     * a custom key function that returns the sort key values to use for each 
     * record. PHP sorts are not stable; the order of records with the same
     * key value is not preserved.
     *
     * The optional $group argument is like the $key argument but is used to
     * group records that are already partially sorted. Records will be sorted
     * within each group rather than as a single sequence. If the groups are
     * small relative to the total sequence length this can significantly 
     * improve performance and memory usage.
     */
    public function __construct($reader, $key, $group=null)
    {
        parent::__construct($reader);
        $this->buffer = new Serial_Core_SortQueue($key, $group);
        $this->output = &$this->buffer->sorted;
        return;
    }

    /**
     * Process an incoming record.
     *
     * Records are buffered until the the input reader has been consumed or
     * the beginning of a new group is encountered.
     */
    protected function queue($record)
    {
        $this->buffer->push($record);
        return;
    }
    
    /**
     * Handle an underflow condition.
     *
     * This is called when the input reader is exhausted and there are no
     * records in the output queue.
     */
    protected function uflow()
    {
        $this->buffer->flush();
        if (!$this->output) {
            throw new Serial_Core_StopIteration();
        }
        return;
    }
}
