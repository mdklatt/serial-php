<?php
/**
 * An integer field.
 *
 */
class Serial_Core_IntField extends Serial_Core_ScalarField
{
    private $valfmt;
    private $strfmt;
    private $default;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt='%d', $default=null)
    {
        parent::__construct($name, $pos);
        $this->valfmt = $fmt;
        $this->strfmt = "%{$this->width}s";
        $this->default = $default;
        return;
    }

    /**
     * Convert a string token to an int.
     *
     * If the token is an empty string the default field value is used.
     */
    public function decode($token)
    {
        if (($token = trim($token)) === '') {
            return $this->default;
        }
        return intval($token);
    }
    
    /**
     * Convert an int to a string token.
     *
     * If the value is null the default field value is used (null is encoded as
     * a null string). For fixed-width fields the token is padded on the left
     * or trimmed on the right to fit the allotted width
     */
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default;
        }
        $token = $value !== null ? sprintf($this->valfmt, $value) : '';
        if ($this->fixed) {
            $token = sprintf($this->strfmt, substr($token, 0, $this->width));
        }
        return $token;
    }
}
