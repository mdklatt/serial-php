<?php
/**
 * Sort output for another writer.
 * 
 */
class Serial_Core_SortWriter extends Serial_Core_WriterBuffer
{
    // Except for the lack of a uflow() method this has essentially the same
    // implementation as and should be kept in sync with SortReader. Hooray for
    // the lack of multiple inheritence!
    // TODO: Factor common sorting code out as some kind of Sorter object?

    private $buffer = array();
    private $keyfunc;
    private $groupfunc;
    private $groupval;
    
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
        if (!is_callable($key)) {
            // Use the default key function.
            $key = array(new Serial_Core_KeyFunc($key), '__invoke');
        }
        $this->keyfunc = $key;
        if ($group && !is_callable($group)) {
            // Use the default key function.
            $group = array(new Serial_Core_KeyFunc($group), '__invoke');
        }
        $this->groupfunc = $group;
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
        if ($this->groupfunc) {
            $groupval = call_user_func($this->groupfunc, $record);
            if ($groupval != $this->groupval) {
                // This is a new group, process the previous group.
                $this->flush();
            }
            $this->groupval = $groupval;
        }
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
            // Build an N x K array of key values to use to with multisort.
            $keycols[] = call_user_func($this->keyfunc, $record); 
        }
        array_multisort($keycols, $this->buffer);
        $this->output = $this->buffer;
        $this->buffer = array();
        return;
    }
}
