<?php
/**
 * Translate text tokens to/from a constant value.
 *
 */
class Serial_Core_ConstType extends Serial_Core_DataType
{
    /**
     * Initialize this object.
     *
     */
    public function __construct($value, $fmt='%s')
    {
        parent::__construct($fmt, $value);
        return;
    }

    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function decode($token)
    {
        // Token is ignored.
        return $this->default;
    }

    /**
     * Convert a PHP value to a string.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function encode($value)
    {
        // Value is ignored.
        return sprintf($this->fmt, $this->default);
    }
}
