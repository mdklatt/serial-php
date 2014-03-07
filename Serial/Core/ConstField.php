<?php
/**
 * A constant value field.
 *
 */
class Serial_Core_ConstField extends Serial_Core_ScalarField
{
    private $token;
    private $value;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $value, $fmt='%s')
    {
        parent::__construct($name, $pos);
        $this->value = $value;
        $token = sprintf($fmt, $this->value);
        if ($this->fixed) {
            $fmt = "%{$this->width}s";
            $token = sprintf($fmt, substr($token, 0, $this->width));
        }
        $this->token = $token;
        return;
    }

    /**
     * Return a constant value (token is ignored).
     *
     */
    public function decode($token)
    {
        return $this->value;
    }

    /**
     * Return a constant token (value is ignored).
     *
     */
    public function encode($value)
    {
        return $this->token;
    }
}
