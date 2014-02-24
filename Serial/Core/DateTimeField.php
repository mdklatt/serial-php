<?php
/**
 * Translate text tokens to/from DateTime values.
 *
 */
class Serial_Core_DateTimeField extends Serial_Core_Field
{
    private $timefmt;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt, $default=null)
    {
        parent::__construct($name, $pos, $fmt, $default);
        return;
    }
    
    /**
     * Convert a string token to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function decode($token)
    {
        if (!($token = trim($token))) {
            return $this->default;
        }
        return DateTime::createFromFormat($this->fmt, $token);
    }
    
    /**
     * Convert a PHP value to a string token.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function encode($value)
    {
        if ($value === null) {
            if ($this->default === null) {
                return '';
            }
            $value = $this->default;
        }
        return $value->format($this->fmt);
    }
}
