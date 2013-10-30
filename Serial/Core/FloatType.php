<?php
/**
 * Translate text tokens to/from floating point values.
 *
 */
class Serial_Core_FloatType extends Serial_Core_DataType
{
    private $special = array('nan' => NAN);
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($fmt='%g', $default=null)
    {
        parent::__construct($fmt, $default);
        $this->special[''] = $this->default;
        return;
    }

    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function decode($token)
    {
        $token = strtolower(trim($token));
        if (isset($this->special[$token])) {
            return $this->special[$token];
        }
        return floatval($token);
    }
    
    /**
     * Convert a PHP value to a string.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default;
        }
        if (is_nan($value)) {
            // Workaround for s/printf() bug with NaN on some platforms.
            // <https://bugs.php.net/bug.php?id=49244>
            return 'NaN';
        }
        return $value !== null ? sprintf($this->fmt, $value) : "";
    }
}
