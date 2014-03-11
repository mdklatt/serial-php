<?php
/**
 * A DateTime field.
 *
 */
class Serial_Core_DateTimeField extends Serial_Core_ScalarField
{
    private $valfmt;
    private $strfmt;
    private $default;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt, $default=null)
    {
        parent::__construct($name, $pos);
        $this->valfmt = $fmt;
        $this->strfmt = "%{$this->width}s";
        $this->default = $default;
        return;
    }
    
    /**
     * Convert a string token to a DateTime.
     *
     * CAUTION: This does *not* use the format string to parse the token into
     * a DateTime; instead the token must be in a format that the DateTime
     * constructor can correctly parse. If the token is an empty string the
     * default field value is used.
     */
    public function decode($token)
    {
        // Can't use DateTime::createFromFormat() because of PHP 5.2, so rely 
        // on the DateTime constructor. The legacy date/time interface is 
        // pretty well worthless here (doesn't work the same on all platforms,
        // doesn't reliably work for years before 1970, etc.), so this is the
        // best that that can be done as long as PHP 5.2 compatibility is
        // needed.
        if (!($token = trim($token))) {
            return $this->default;
        }
        return new DateTime($token);
    }
    
    /**
     * Convert a DateTime to a string token.
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
        $token = $value !== null ? $value->format($this->valfmt) : '';
        if ($this->fixed) {
            $token = sprintf($this->strfmt, substr($token, 0, $this->width));
        }
        return $token;
    }
}
