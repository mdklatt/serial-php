<?php
/**
 * Translate text tokens to/from floating point values.
 *
 */
class Serial_Core_FloatField extends Serial_Core_ScalarField
{
    private $special = array('nan' => NAN);
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt='%g', $default=null)
    {
        parent::__construct($name, $pos, $fmt, $default);
        $this->special[''] = $this->default;
        return;
    }

    /**
     * Convert a string token to a PHP value.
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
     * Convert a PHP value to a string token.
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
        return $value !== null ? sprintf($this->fmt, $value) : '';
    }
}
