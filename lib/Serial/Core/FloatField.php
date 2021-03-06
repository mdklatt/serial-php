<?php
namespace Serial\Core;

/**
 * A floating-point field.
 *
 */
class FloatField extends ScalarField
{
    private $valfmt;
    private $strfmt;
    private $default;
    private $special = array('nan' => NAN);
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fmt='%g', $default=null)
    {
        parent::__construct($name, $pos);
        $this->valfmt = $fmt;
        $this->strfmt = "%{$this->width}s";
        $this->default = $default;
        $this->special[''] = $this->default;
        return;
    }

    /**
     * Convert a string token to a float.
     *
     * If the token is an empty string the default field value is used.
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
     * Convert a float to a string token.
     *
     * If the value is null the default field value is used (null is encoded as
     * a null string). For fixed-width fields the token is padded on the left
     * or trimmed on the right to fit the allotted width.
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
        $token = $value !== null ? sprintf($this->valfmt, $value) : '';
        if ($this->fixed) {
            $token = sprintf($this->strfmt, substr($token, 0, $this->width));
        }
        return $token;
    }
}
