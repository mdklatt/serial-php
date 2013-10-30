<?php
/**
 * Translate text tokens to/from DateTime values.
 *
 */
class Serial_Core_DateTimeType extends Serial_Core_DataType
{
    private $timefmt;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($timefmt, $default=null)
    {
        parent::__construct('%s', $default);
        $this->timefmt = $timefmt;
        return;
    }
    
    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function decode($token)
    {
        if (!($token = trim($token))) {
            return $this->default;
        }
        return DateTime::createFromFormat($this->timefmt, $token);
    }
    
    /**
     * Convert a PHP value to a string.
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
        return $value->format($this->timefmt);
    }
}
