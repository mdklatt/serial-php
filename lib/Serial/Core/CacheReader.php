<?php
/**
 * Cache input from another reader.
 * 
 */
class Serial_Core_CacheReader extends Serial_Core_ReaderBuffer
{
    private $maxlen;
    private $buffer = array();
    private $bufpos = 0;
    
    /**
     * Initialize this object.
     *
     * By default all input is cached, or specify $maxlen to limit the number
     * cached records.
     */
    public function __construct($reader, $maxlen=null)
    {
        parent::__construct($reader);
        $this->maxlen = $maxlen;
        return;
    }

    /**
     * Reverse the reader to a cached record.
     *
     * Go to the first cached record by default, or specify a count. NB: Do not
     * confuse this with the rewind() method from the Iterator interface.
     */
    public function reverse($count=null)
    {
        // This does not reset the Reader index, so when the cached records are
        // retrieved again they will have new keys. This is not a problem as
        // keys should not be relied on to have any specific meaning.
        $this->bufpos = $count === null ? 0 : max(0, $this->bufpos - $count);
        return;
    }

    /**
     * Process an incoming record.
     */
    protected function queue($record)
    {
        if (count($this->buffer) === $this->maxlen) {
            // Remove a record to maintain the buffer at maxlen.
            array_shift($this->buffer);
        }
        $this->buffer[] = $record;
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
        if ($this->bufpos >= count($this->buffer)) {
            // At end of buffer.
            throw new Serial_Core_StopIteration();
        }
        $this->output[] = $this->buffer[$this->bufpos++];
        return;
    }
}
