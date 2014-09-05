<?php
/**
 * Apply line-based filtering to a stream.
 *
 * The user interface consists of the attach() static method. Users do not need
 * to create an instance of this class. The public instance methods are used by 
 * the PHP stream filter protocol. 
 */
class Serial_Core_StreamFilterManager extends php_user_filter
{    
    const READ = STREAM_FILTER_READ;
    const WRITE = STREAM_FILTER_WRITE;
    const READWRITE = STREAM_FILTER_ALL;
    
    private static $registry = array();

    /**
     * Attach a callback to a stream as a filter.
     *
     * A filter is a function or callable object that will be applied to each
     * line of stream input or ouput for a stream. The filter takes a single 
     * line of text as an argument and takes one of the following three actions
     * on it:
     * 1. Return null to ignore the line.
     * 2. Return the line as is.
     * 3. Return a new/modified line.
     */
    public static function attach($stream, $callback, $mode=null, $prepend=false)
    {
        // PHP identifies filters by class, not object. However, each class can
        // be mapped to multiple filter names. Here, the filter name is used as
        // a key to store data for each filter instance. 
        // TODO: This uses the static class name to register each filter, which
        // breaks inheritance. If PHP 5.2 support is no longer needed, take
        // advantage of late static binding in PHP 5.3+ to fix this.
        $uid = uniqid();
        $callback = new Serial_Core_Callback($callback);
        self::$registry[$uid] = array('endl' => PHP_EOL, 'callback' => $callback);
        stream_filter_register($uid, __CLASS__);
        if ($prepend) {
            stream_filter_prepend($stream, $uid, $mode);
        }
        else {
            stream_filter_append($stream, $uid, $mode);
        }
        return;
    }
   
    // Implement the php_user_filter interface. This is not part of the class
    // user interface. NB: Filters are registered using the static class name
    // (see attach() above), so overriding these methods in a derived class
    // will have no effect.
    
    private $buffer;
    private $bucket;
    
    /**
     * Initialize the filter.
     *
     */
    public function onCreate()
    {
        // Class constructors are never called for a php_user_filter. Instead,
        // this is called when the filter is bound to a stream, e.g.
        // stream_filter_append(). The $filtername attribute specifies the
        // name associated with this filter, which in this case uniquely
        // identifies a filter instance.
        $this->callback = self::$registry[$this->filtername]['callback'];
        $this->buffer = '';
        return;
    }

    /**
     * Filter stream data.
     *
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        // With each pass through the filter a "bucket brigade" of bytes is
        // processed. Multiple passes may be required to process an entire
        // stream.
        // TODO: Allow use of StopIteration to halt further processing.
        $consumed = 0;
        while ($bucket = stream_bucket_make_writeable($in)) {
            // Read each bucket. The brigade will be processed as a single
            // string. Save a valid bucket for this stream so it can be
            // written to later.
            $consumed += $bucket->datalen;
            $this->buffer .= $bucket->data;
            $this->bucket = $bucket;
        }
        if (!($lines = $this->lines($closing))) {
            // No data to pass on.
            return PSFS_FEED_ME;
        }
        $this->bucket->data = implode(PHP_EOL, $lines);
        if (!$closing) {
            // Add a trailing newline unless this is the last line. In that
            // case, the output will mirror the presence of a trailing newline
            // in the input.
            $this->bucket->data.= PHP_EOL;
        }
        $this->bucket->datalen = strlen($this->bucket->data);
        stream_bucket_append($out, $this->bucket);
        return PSFS_PASS_ON;
    }
    
    /**
     * Parse buffered data into lines.
     *
     */
    private function lines($closing)
    {
        // Lines may be split across buckets, so buffer the last token in
        // anticipation of more data. If the buffer ends with a newline, the
        // last token will be an empty string.
        $tokens = explode(PHP_EOL, $this->buffer);
        $lines = $this->callback->map(array(array_slice($tokens, 0, -1)));
        $this->buffer = end($tokens);
        if ($closing && $this->buffer !== '') {
            // This is the last pass through the filter, so flush the buffer.
            // $lines[] = call_user_func($this->callback, $this->buffer);
            $lines[] = $this->callback->__invoke(array($this->buffer));
        }
        return $lines;
    }
}    
