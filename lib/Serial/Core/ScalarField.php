<?php
/**
 * Base class for scalar fields.
 *
 */ 
abstract class Serial_Core_ScalarField
{
    public $name;
    public $pos;
    public $width = 1;
    public $fixed = false;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos)
    {
        $this->name = $name;
        $this->pos = $pos;
        if (is_array($this->pos)) {
            // This is a fixed-width field; the width is in characters.
            $this->width = $pos[1];
            $this->fixed = true;
        }
        return;
    }
    
    /**
     * Convert a string token to a PHP value.
     *
     * This is called by a Reader while parsing an input record.
     */
    abstract public function decode($token);
    
    /**
     * Convert a PHP value to a string token.
     *
     * This is called by a Writer while formatting an output record.
     */
    abstract public function encode($value);
} 
