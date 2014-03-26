<?php
/**
 * Read a sequence of streams as a single series of records.
 *
 * The SequenceReader opens streams as necessary and closes them once they have
 * been read.
 */
class Serial_Core_SequenceReader extends Serial_Core_Reader
{
    /**
     * Open a SequenceReader with automatic stream handling.
     *
     * The arguments are passed to the class contructor. Each stream is closed
     * once it has been exhausted, and any remaining open streams in the 
     * sequence will be closed when the reader's destructor is called.
     *
     * Due to a circular reference, unsetting a SequenceReader variable is not
     * enough to trigger its destructor. It will be called when the process 
     * ends, or it can be called explicitly, i.e. $reader->__destruct().
     */
    public static function open(/* $args */)
    {
        // Every derived class must implement its own open() method that
        // returns the correct type of object.
        $class = new ReflectionClass('Serial_Core_SequenceReader');
        $reader = $class->newInstanceArgs(func_get_args());
        $reader->closing = true;  // automatically call close
        return $reader;
    }
    
    protected $closing = false;
    private $streams;
    private $callback;
    private $reader;
    private $args;

    /**
     * Initialize this object.
     *
     * The first argument is an array of file paths and/or open streams. The
     * second argument is callback that takes an open stream and returns a 
     * reader, e.g. a Reader class name. Any remaining arguments are passed to
     * the reader function.
     *
     * Filtering is applied at the ReaderSequnce level, but for filters that
     * throw a Serial_Core_StopIteration exception this may not be the desired 
     * behavior. Throwing StopIteration from a ReaderSquence filter will halt
     * input from all remaining streams in the sequence. If the intent is to 
     * stop input on an individual stream, define the callback function to 
     * return a Reader that already has the desired filter(s) applied.
     */
    public function __construct(/* $args */)
    {
        $args = func_get_args();
        if (count($args) < 2) {
            throw new InvalidArgumentException('missing required arguments');
        }
        $streams = array_shift($args);
        $this->streams = new Serial_Core_StreamQueue($streams,
            array($this, 'stream'));  // circular reference
        $this->streams->rewind();
        $reader = array_shift($args);
        $fixed = array_combine(range(1, count($args)), $args);
        $this->callback  = new Serial_Core_Callback($reader, $fixed);
        return;
    }
    
    /**
     * Clean up this object.
     *
     */
    public function __destruct()
    {
        if (!$this->closing) {
            return;
        }
        foreach ($this->streams as $stream) {
            Serial_Core::close($stream);
        }
        return;
    }

    /**
     * Get the next parsed record from the sequence.
     *
     * This is called before any filters have been applied. A StopIteration
     * exception is thrown when all streams in the sequence have been
     * exhausted.
     */
    protected function get()
    {
        while ($this->streams->valid()) {
            if (!$this->reader) {
                $this->reader = $this->callback->__invoke(
                    array($this->streams->current()));
                $this->reader->rewind();
            }
            if ($this->reader->valid()) {
                $record = $this->reader->current();
                $this->reader->next();
                return $record;
            }
            $this->reader = null;
            $this->streams->next();
        }
        throw new Serial_Core_StopIteration();
    }
    
    /**
     * Return an open stream for the given expression.
     *
     * The expression is either a path to open as a text file or an already
     * stream. Derived classes can override this to support other types of
     * streams, e.g. network streams. Return null to skip this item in the
     * sequence.
     */
    public function stream($expr)
    {
        return is_resource($expr) ? $expr : fopen($expr, 'r');
    } 
}


/**
 * Iterate of over a sequence of streams.
 *
 * The iterator always points to an open stream. Invalid stream arguments in 
 * the sequence are ignored. Streams are closed as the sequence is advanced.
 */
class Serial_Core_StreamQueue implements Iterator
{
    private $count = 0;
    private $streams;
    private $callback;
    
    /**
     * Initialize this object.
     *
     * The $streams array is treated as a FIFO queue. It will be empty once the
     * iterator is complete. The callback is passed each item in the sequence
     * and should return an open stream or null to skip a given item.
     */
    public function __construct(&$streams, $callback)
    {
        $this->streams = $streams;
        $this->callback = $callback;
        $this->open();
        return;       
    }
    
    /**
     * Rewind the iterator (no op).
     *
     */
    public function rewind()
    {
        // This must be implemented to satisfy the Iterator interface, but the
        // iterator cannot be rewound.
        return;     
    }
    
    /**
     * Return true if the iterator is still valid.
     *
     */
    public function valid()
    {
        return $this->streams != false;  // don't want strict comparison
    }
    
    /**
     * Return the iterator value.
     *
     */
    public function current()
    {
        return $this->streams[0];
    }
    
    /**
     * Advance the iterator to the next value.
     *
     */
    public function next()
    {
        Serial_Core::close(array_shift($this->streams));
        $this->open();
        ++$this->count;
        return;
    }
    
    /**
     * Return a unique key for the current iterator item.
     *
     */
    public function key()
    {
        return $this->count;
    }
    
    /**
     * Open the next valid stream.
     *
     * Starting with the current value, the sequence is consumed until a valid 
     * stream is found.
     */
    private function open()
    {
        if (!$this->streams) {
            return;
        }
        $stream = call_user_func($this->callback, $this->streams[0]);
        if (!$stream) {
            // Continue until there's a valid stream.
            array_shift($this->streams);
            $this->open();
        }
        $this->streams[0] = $stream;
        return;
    }
}
