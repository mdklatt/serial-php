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
}
