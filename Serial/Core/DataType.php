<?php
/**
 * Base class for translating text tokens to/from PHP types.
 *
 */ 
abstract class Serial_Core_DataType
{
    protected $fmt;
    protected $default;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($fmt, $default)
    {
        $this->fmt = $fmt;
        $this->default = $default;
        return;
    }
    
    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    abstract public function decode($token);
    
    /**
     * Convert a PHP value to a string.
     *
     * This is called by a Writer and does not need to be called by the user.
     */
    abstract public function encode($value);
} 
