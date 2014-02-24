<?php
/**
 * Translate text tokens to/from string values.
 *
 */
class Serial_Core_StringField extends Serial_Core_Field
{
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt='%s', $quote='', $default=null)
    {
        parent::__construct($name, $pos, $fmt, $default);
        $this->quote = $quote;
        return;
    }
    
    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function decode($token)
    {
        if (!($value = trim(trim($token), $this->quote))) {
            $value = $this->default;
        }
        return $value;
    }
    
    /**
     * Convert a PHP value to a string.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default !== null ? $this->default : '';
        }
        return $this->quote.sprintf($this->fmt, $value).$this->quote;
    }
}
