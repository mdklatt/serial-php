<?php
/**
 * Data types for translating text tokens to/from PHP types.
 *
 * Client code defines the Serial_Core_DataType for each input/ouput field, but the 
 * _Reader and _Writer classes are responsible for calling decode() and 
 * encode().
 */ 
  
abstract class Serial_Core_DataType
{
    protected $fmt;
    protected $default;
    
    private $callback;
    
    public function __construct($callback, $fmt, $default)
    {
        $this->callback = $callback;
        $this->fmt = $fmt;
        $this->default = $default;
        return;
    }
    
    public function decode($token)
    {
        if (($token = trim($token)) === '') {
            $value = $this->default;
        }
        else {
            $value = call_user_func($this->callback, $token);
        }
        return $value;
    }
    
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default;
        }
        return $value !== null ? sprintf($this->fmt, $value) : "";
    }
} 


class Serial_Core_IntType extends Serial_Core_DataType
{
    public function __construct($fmt='%d', $default=null)
    {
        parent::__construct('intval', $fmt, $default);
        return;
    }
}


class Serial_Core_FloatType extends Serial_Core_DataType
{
    /**
     * Initialize this object.
     *
     */
    public function __construct($fmt='%g', $default=null)
    {
        parent::__construct('floatval', $fmt, $default);
        return;
    }

    /**
     * Encode a PHP value as a string.
     *
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


class Serial_Core_StringType extends Serial_Core_DataType
{
    public function __construct($fmt='%s', $quote='', $default=null)
    {
        parent::__construct(null, $fmt, $default);
        $this->quote = $quote;
        return;
    }
    
    public function decode($token)
    {
        if (!($value = trim(trim($token), $this->quote))) {
            $value = $this->default;
        }
        return $value;
    }
    
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->default !== null ? $this->default : '';
        }
        return $this->quote.sprintf($this->fmt, $value).$this->quote;
    }
}


class Serial_Core_ConstType extends Serial_Core_DataType
{
    public function __construct($value, $fmt='%s')
    {
        parent::__construct(null, $fmt, $value);
        return;
    }

    public function decode($token)
    {
        // Token is ignored.
        return $this->default;
    }

    public function encode($value)
    {
        // Value is ignored.
        return sprintf($this->fmt, $this->default);
    }
}


class Serial_Core_DateTimeType extends Serial_Core_DataType
{
    private $timefmt;
    
    public function __construct($timefmt, $default=null)
    {
        parent::__construct(null, '%s', $default);
        $this->timefmt = $timefmt;
        return;
    }
    
    public function decode($token)
    {
        if (!($token = trim($token))) {
            return $this->default;
        }
        return DateTime::createFromFormat($this->timefmt, $token);
    }
    
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


class Serial_Core_ArrayType extends Serial_Core_DataType
{
    public $width;
    private $fields = array();
    private $stride = 0;

    public function __construct($fields, $default=array())
    {
        parent::__construct(null, '%s', $default);
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $field = new Serial_Core_Field($pos, $dtype);
            $this->fields[$name] = $field;
            $this->stride += $field->width;
        }
        return;
    }
    
    public function decode($token_array)
    {
        $token_array = new Serial_Core_Sequence($token_array);
        $value_array = array();
        for ($beg = 0; $beg < count($token_array); $beg += $this->stride) {
            $elem = new Serial_Core_Sequence($token_array->get(array($beg, $this->stride)));
            $value = array();
            foreach ($this->fields as $name => $field) {
                $value[$name] = $field->dtype->decode($elem->get($field->pos));
            }
            $value_array[] = $value;
        }
        $this->width = count($value_array) * $this->stride;
        return $value_array ? $value_array : $this->default;
    }
    
    public function encode($value_array)
    {
        if (!$value_array) {
            $value_array = $this->default;
        }
        $this->width = count($value_array) * $this->stride;        
        $token_array = array();
        foreach ($value_array as $elem) {
            foreach ($this->fields as $name => $field) {
                $token_array[] = $field->dtype->encode($elem[$name]);
            }
        }
        return $token_array;
    }
}
