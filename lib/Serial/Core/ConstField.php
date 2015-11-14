<?php
namespace Serial\Core;

/**
 * A constant value field.
 *
 */
class ConstField extends ScalarField
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
     * For fixed-width fields the token is padded on the left or trimmed on the
     * right to fit the allotted width.
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
