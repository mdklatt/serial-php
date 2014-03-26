<?php
/**
 * Execute a callable expression with optional fixed arguments.
 *
 */
class Serial_Core_Callback
{
    /**
     * Initialize this object.
     *
     * The fixed arguments are specified using an associative array keyed by
     * each argument's position in the callable's signature (0 is the first
     * argument).
     */
    public function __construct($callback, $fixed=array())
    {
        $this->callback = $callback;
        if (is_string($callback) && class_exists($this->callback)) {
            $class = new ReflectionClass($this->callback);
            $this->callback = array($class, 'newInstance');
        }        
        if (method_exists($this->callback, '__invoke')) {
            // PHP 5.2 workaround for callable objects.
            $this->callback = array($this->callback, '__invoke');
        }  
        $this->fixed = $fixed;
        return;
    }

    /**
     * Execute the callback.
     *
     * The given arguments are filled in with the fixed arguments to complete
     * the call signature
     */
    public function __invoke($args)
    {
        foreach ($this->fixed as $pos => $arg) {
            // Insert each fixed argument into the argument array.
            array_splice($args, $pos, 0, array($arg));
            
        }
        return call_user_func_array($this->callback, $args);
    }
    
    /**
     * Execute the callback for a sequence of arguments.
     *
     * The argument list consists of one array per argument in the callback
     * signature (excluding fixed arguments). Each array should be the same
     * size.
     */
    public function map(/* $args */)
    {
        // Transpose the argument array so that each element along the first
        // dimension is the argument vector for each function call.
        $args = func_get_args();  // NX x NY
        array_unshift($args, null);
        $args = call_user_func_array('array_map', $args);  // NY x NX
        $ny = count($args);
        if (!($ny = count($args))) {
            return array();
        }
        $results = array_fill(0, $ny, null);
        foreach ($args as $key => $argv) {
            $results[$key] = $this->__invoke(array($argv));
        }
        return $results;
    }
}
