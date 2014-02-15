<?php
/**
 * Iterate over a sequence of files/streams.
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
     * that takes a stream as an argument and returns a Reader. The remaining
     * arguments are either open streams or paths to open as plain text files.
     * Each input stream is closed once it has been exhausted.
     *
     * Filtering is applied at the ReaderSequnce level, but for filters that
     * throw an EofException this may not be the desired behavior. Throwing 
     * an EofException at this level effects all remaining streams. If the 
     * intent is to stop iteration on an individual stream, define the open
     * function to return a Reader that already has the appropriate filter(s)
     * applied.
     * 
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
        $this->open();
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
     * Return the next parsed record from the sequence.
     *
     */
    protected function get()
    {
        while (!$this->reader->valid()) {
            $this->open();  // throws EofException on EOF
        }
        $record = $this->reader->current();
        $this->reader->next();
        return $record;
    }
    
    /**
     * Initialize a reader for the next stream.
     *
     */
    private function open()
    {
        if ($this->reader) {
            // Close the currently open stream.
            fclose(array_shift($this->input));
        }
        if (!$this->input) {
            throw new Serial_Core_EofException();
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