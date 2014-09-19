<?php
/**
 * Sort output for another writer.
 * 
 */
class Serial_Core_SortWriter extends Serial_Core_WriterBuffer
{
    private $buffer;

    /**
     * Initialize this object.
     *
     * The $key argument is either a single field name, an array of names, or
     * a custom key function that returns the sort key values to use for each 
     * record.
     *
     * The optional $group argument is like the $key argument but is used to
     * group records that are already partially sorted. Records will be sorted
     * within each group rather than as a single sequence. If the groups are
     * small relative to the total sequence length this can significantly 
     * improve performance and memory usage.
     */
    public function __construct($writer, $key, $group=null)
    {
        parent::__construct($writer);
        $this->buffer = new Serial_Core_SortQueue($key, $group);
        $this->output = &$this->buffer->sorted;
        return;
    }

    /**
     * Process an incoming record.
     *
     * Records are buffered until the beginning of a new group is encountered
     * or flush() is called.
     */
    protected function queue($record)
    {
        $this->buffer->push($record);
        return;
    }
    
    /**
     * Send all buffered records to the output queue.
     */
    protected function flush()
    {
        $this->buffer->flush();
        return;
    }
}
