<?php
/**
 * Apply line-based filtering to a stream.
 *
 * The user interface consists of the attach() static method. Users do not need
 * to create an instance of this class. The public instance methods are used by 
 * the PHP stream filter protocol. 
 */
class Serial_Core_FilterProtocol extends php_user_filter
{
    private static $registry = array();

    /**
     * Attach a filter to a stream.
     *
     * A filter is a function or callable object that will be applied to each
     * line of stream input or ouput for a stream. The filter takes a single 
     * line of text as an argument and takes on of the following three actions
     * on it:
     * 1. Return null to ignore the line.
     * 2. Return the line as is.
     * 3. Return a new/modified line.
     */
    public static function attach($stream, $callback, $mode, $prepend=false)
    {
        $key = self::register($callback);
        if ($prepend) {
            stream_filter_prepend($stream, $key, $mode);
        }
        else {
            stream_filter_append($stream, $key, $mode);
        }
        return;   
    }
    
    /**
     * Register a callback to use as a filter.
     *
     */
    private static function register($callback) 
    {
        if (method_exists($callback, '__invoke')) {
            // PHP 5.2 workaround for callable objects.
            $callback = array($callback, '__invoke');
        }
        if (is_array($callback)) {
            list($object, $method) = $callback;
            $key = get_class($object).'::'.$method;
        }
        else {
            $key = $callback;
        }
        stream_filter_register($key, __CLASS__);
        self::$registry[$key] = $callback;
        return $key;
    }
    
    private $buffer;
    private $bufpos;
    
    /**
     * Initialize the filter.
     *
     * This is used by the the PHP stream filter protocol and is not part of
     * the user interface.
     */
    public function onCreate()
    {
        // The class constructor is never called. Instead, this is called when
        // the filter is bound to a stream, e.g. stream_filter_append().
        $this->callback = self::$registry[$this->filtername];
        return;
    }

    /**
     * Filter stream data.
     *
     * This is used by the the PHP stream filter protocol and is not part of
     * the user interface.
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        // This is called when reading and/or writing from the stream, c.f.
        // stream_filter_append(). 
        $this->buffer = '';
        $this->bufpos = 0;
        while ($bucket = stream_bucket_make_writeable($in)) {
            // Filter the data in each input bucket and pass it along as an
            // output bucket.
            // TODO: Allow use of EOF|EofException to halt further processing.
            $this->buffer .= $bucket->data;
            $consumed += $bucket->datalen;
            $filtered = array_map($this->callback, $this->lines());
            $bucket->data = implode('', $filtered);
            $bucket->datalen = strlen($bucket->data);
            stream_bucket_append($out, $bucket);
        }
        if ($this->buffer) {
            // Process the incomplete last line (no newline).
            $filtered = call_user_func($this->callback, $this->buffer);
            $bucket = stream_bucket_new($this->stream, $filtered);
            stream_bucket_append($out, $bucket);
        }            
        return PSFS_PASS_ON;
    }
    
    /**
     * Split the buffer contents into complete lines.
     *
     */
    private function lines()
    {
        $lines = array();
        while (($pos = strpos($this->buffer, PHP_EOL, $this->bufpos)) !== false) {
            // Find each complete line. If the last line does not contain a 
            // newline it is left in the buffer to await additional data.
            $pos += 1;  // include newline with line
            $len = $pos - $this->bufpos;
            $lines[] = substr($this->buffer, $this->bufpos, $len);
            $this->bufpos += $len;
        }
        $this->buffer = substr($this->buffer, $this->bufpos);
        $this->bufpos = 0;
        return $lines;
    }
}    
