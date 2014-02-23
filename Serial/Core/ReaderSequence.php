<?php
/**
 * Iterate over multiple input sources as a single sequence of records.
 *
 */
class Serial_Core_ReaderSequence extends Serial_Core_Reader
{
    private $input = array();
    private $callback;
    private $reader;
    
    /**
     * Initialize this object.
     *
     * The first argument is required and is a function or callable object
     * that takes a stream as an argument and returns a Reader to use on that
     * stream. The remaining arguments are either open streams or paths to open
     * as plain text files. Each stream is closed once it has been exhausted.
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
        if (!($args = func_get_args())) {
            throw new InvalidArgumentException('first argument is required');
        }
        $this->callback = array_shift($args);
        if (method_exists($this->callback, '__invoke')) {
            // PHP 5.2 workaround for callable objects.
            $this->callback = array($this->callback, '__invoke');
        }
        foreach ($args as $expr) {
            // Treat an array as a list of stream arguments.
            if (is_array($expr)) {
                $this->input = array_merge($this->input, $expr);
            }
            else {
                $this->input[] = $expr;
            }
        }
        //$this->open();
        return;
    }
    
    /**
     * Clean up this object.
     *
     */
    public function __destruct()
    {
        foreach ($this->input as $expr) {
            // Close each open stream.
            if (is_resource($expr)) {
                fclose($expr);
            }
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
        while (!$this->reader || !$this->reader->valid()) {
            $this->open();  // throws StopIteration
        }
        $record = $this->reader->current();
        $this->reader->next();
        return $record;
    }
    
    /**
     * Create a reader for the next stream in the sequence.
     *
     */
    private function open()
    {
        if ($this->reader) {
            // Close the currently open stream.
            fclose(array_shift($this->input));
        }
        if (!$this->input) {
            throw new Serial_Core_StopIteration();
        }
        if (is_string($this->input[0])) {
            // Open a path as a plain text file.
            $this->input[0] = fopen($this->input[0], 'rb');
        }
        $this->reader = call_user_func($this->callback, $this->input[0]);
        $this->reader->rewind();
        return;
    }    
}