<?php
/**
 * Translate text tokens to/from integer values.
 *
 */
class Serial_Core_IntType extends Serial_Core_DataType
{
    /**
     * Initialize this object.
     * 
     */
    public function __construct($fmt='%d', $default=null)
    {
        parent::__construct($fmt, $default);
        return;
    }

    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called be the user.
     */
    public function decode($token)
    {
        if (($token = trim($token)) === '') {
            return $this->default;
        }
        return intval($token);
    }
    
    /**
     * Convert a PHP value to a string.
     *
     * This is called by a Writer and does not need to be called by the user.
     */
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default;
        }
        return $value !== null ? sprintf($this->fmt, $value) : '';
    }
}
