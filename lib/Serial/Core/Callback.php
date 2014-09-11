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
     * The fixed arguments are specified using an array keyed by the position
     * of each argument in the callbacks signature (0 is the first argument).
     */
    public function __construct($callback, $fixed=array())
    {
        $this->callback = $callback;
        if (is_string($callback) && class_exists($this->callback)) {
            // Callback is a class.
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
    public function __invoke($args=array())
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
     * As with the array_map() function, $argv consists of one array per 
     * argument in the callback signature (excluding fixed arguments). Each 
     * array should be the same size. For example, given a callback that adds 
     * two numbers, map(array(1, 2), array(3, 4)) would return array(4, 6).
     */
    public function map($argv)
    {
        // Transpose the argument array so that each element along the first
        // dimension is the argument vector for each function call.
        array_unshift($argv, null);
        $argv = call_user_func_array('array_map', $argv);
        if (!($ny = count($argv))) {
            return array();
        }
        $results = array_fill(0, $ny, null);
        foreach ($argv as $key => $args) {
            $results[$key] = $this->__invoke(array($args));
        }
        return $results;
    }
}
