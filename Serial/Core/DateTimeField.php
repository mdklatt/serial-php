<?php
/**
 * Translate text tokens to/from DateTime values.
 *
 */
class Serial_Core_DateTimeField extends Serial_Core_ScalarField
{
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
     * CAUTION: This does *not* use the format string to parse the token into
     * a DateTime; instead the token must be in a format that the DateTime
     * constructor can correctly parse. This is called by a Reader and does not 
     * need to be called by the user.
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
