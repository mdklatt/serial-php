<?php
/**
 * Base class for translating text tokens to/from PHP types.
 *
 */ 
abstract class Serial_Core_ScalarField
{
    public $name;
    public $pos;
    public $width;
    protected $fmt;
    protected $default;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt='%s', $default=null)
    {
        $this->name = $name;
        $this->pos = $pos;
        $this->fmt = $fmt;
        $this->default = $default;
        $this->width = is_array($pos) ? $pos[1] : 1;        
        return;
    }
    
    /**
     * Convert a string token to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    abstract public function decode($token);
    
    /**
     * Convert a PHP value to a string token.
     *
     * This is called by a Writer and does not need to be called by the user.
     */
    abstract public function encode($value);
} 
