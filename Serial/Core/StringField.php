<?php
/**
 * A string field.
 *
 */
class Serial_Core_StringField extends Serial_Core_ScalarField
{
    private $valfmt;
    private $strfmt;
    private $quote;
    private $default;
        
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt='%s', $quote='', $default=null)
    {
        parent::__construct($name, $pos);
        $this->valfmt = $fmt;
        $this->strfmt = "%{$this->width}s";
        $this->quote = $quote;
        $this->default = $default;
        return;
    }
    
    /**
     * Convert a string to a PHP value.
     *
     * Surrounding whitespace and quotes are removed, and if the resulting
     * string is null the default field value is used.
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
     * If the value is null the default field value is used (null is encoded as
     * a null string). For fixed-width fields the token is padded on the left
     * or trimmed on the right to fit the allotted width.
     */
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default !== null ? $this->default : '';
        }
        $token = $this->quote.sprintf($this->valfmt, $value).$this->quote;
        if ($this->fixed) {
            $token = sprintf($this->strfmt, substr($token, 0, $this->width));
        }
        return $token;
    }
}
