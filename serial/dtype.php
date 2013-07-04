<?php
/**
 * Data types for translating text tokens to/from PHP types.
 *
 * Client code defines the Serial_DataType for each input/ouput field, but the 
 * _Reader and _Writer classes are responsible for calling decode() and 
 * encode().
 */ 
  
abstract class Serial_DataType
{
    protected $_fmt;
    protected $_default;
    
    private $_callback;
    
    public function __construct($callback, $fmt, $default)
    {
        $this->_callback = $callback;
        $this->_fmt = $fmt;
        $this->_default = $default;
        return;
    }
    
    public function decode($token)
    {
        if (($token = trim($token)) === '') {
            $value = $this->_default;
        }
        else {
            $value = call_user_func($this->_callback, $token);
        }
        return $value;
    }
    
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->_default;
        }
        return $value !== null ? sprintf($this->_fmt, $value) : "";
    }
} 


class Serial_IntType extends Serial_DataType
{
    public function __construct($fmt='%d', $default=null)
    {
        parent::__construct('intval', $fmt, $default);
        return;
    }
}


class Serial_FloatType extends Serial_DataType
{
    public function __construct($fmt='%g', $default=null)
    {
        parent::__construct('floatval', $fmt, $default);
        return;
    }
}


class Serial_StringType extends Serial_DataType
{
    public function __construct($fmt='%s', $quote='', $default=null)
    {
        parent::__construct(null, $fmt, $default);
        $this->_quote = $quote;
        return;
    }
    
    public function decode($token)
    {
        if (!($value = trim(trim($token), $this->_quote))) {
            $value = $this->_default;
        }
        return $value;
    }
    
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->_default !== null ? $this->_default : '';
        }
        return $this->_quote.sprintf($this->_fmt, $value).$this->_quote;
    }
}


class Serial_ConstType extends Serial_DataType
{
    public function __construct($value, $fmt='%s')
    {
        parent::__construct(null, $fmt, $value);
        return;
    }

    public function decode($token)
    {
        // Token is ignored.
        return $this->_default;
    }

    public function encode($value)
    {
        // Value is ignored.
        return sprintf($this->_fmt, $this->_default);
    }
}


class Serial_DateTimeType extends Serial_DataType
{
    private $_timefmt;
    
    public function __construct($timefmt, $default=null)
    {
        parent::__construct(null, '%s', $default);
        $this->_timefmt = $timefmt;
        return;
    }
    
    public function decode($token)
    {
        if (!($token = trim($token))) {
            return $this->_default;
        }
        return DateTime::createFromFormat($this->_timefmt, $token);
    }
    
    public function encode($value)
    {
        if ($value === null) {
            if ($this->_default === null) {
                return '';
            }
            $value = $this->_default;
        }
        return $value->format($this->_timefmt);
    }
}


class Serial_ArrayType extends Serial_DataType
{
    public $width;
    private $_fields = array();
    private $_stride = 0;

    public function __construct($fields, $default=array())
    {
        parent::__construct(null, '%s', $default);
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $field = new Serial_Field($pos, $dtype);
            $this->_fields[$name] = $field;
            $this->_stride += $field->width;
        }
        return;
    }
    
    public function decode($token_array)
    {
        $token_array = new Serial_Sequence($token_array);
        $value_array = array();
        for ($beg = 0; $beg < count($token_array); $beg += $this->_stride) {
            $elem = new Serial_Sequence($token_array->get(array($beg, $this->_stride)));
            $value = array();
            foreach ($this->_fields as $name => $field) {
                $value[$name] = $field->dtype->decode($elem->get($field->pos));
            }
            $value_array[] = $value;
        }
        $this->width = count($value_array) * $this->_stride;
        return $value_array ? $value_array : $this->_default;
    }
    
    public function encode($value_array)
    {
        if (!$value_array) {
            $value_array = $this->_default;
        }
        $this->width = count($value_array) * $this->_stride;        
        $token_array = array();
        foreach ($value_array as $elem) {
            foreach ($this->_fields as $name => $field) {
                $token_array[] = $field->dtype->encode($elem[$name]);
            }
        }
        return $token_array;
    }
}
