<?php
/**
 * Data types for translating text tokens to/from PHP types.
 *
 * Client code defines the _DataType for each input/ouput field, but the 
 * _Reader and _Writer classes are responsible for calling decode() and 
 * encode().
 */
  
abstract class _DataType
{
    public function __construct($callback, $fmt, $default)
    {
        $this->_callback = $callback;
        $this->_fmt = $fmt;
        $this->_default = $default;
        return;
    }
    
    public function decode($token)
    {
        if (!($token = trim($token))) {
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
        return sprintf($this->_fmt, $value);
    }
} 


class IntType extends _DataType
{
    public function __construct($fmt='%d', $default=null)
    {
        parent::__construct('intval', $fmt, $default);
        return;
    }
}


class FloatType extends _DataType
{
    public function __construct($fmt='%g', $default=null)
    {
        parent::__construct('floatval', $fmt, $default);
        return;
    }
}


class StringType extends _DataType
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
        return $this->_quote.$value.$this->_quote;
    }
}


class ConstType extends _DataType
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


class DateTimeType extends _DataType
{
    public function __construct($timefmt, $default=null)
    {
        parent::__construct(null, '%s', $default);
        $this->_timefmt = $timefmt;
        return;
    }
    
    public function decode($token)
    {
        if (!($value = trim($token))) {
            $value = $this->_default;
        }
        return DateTime::createFromFormat($this->_timefmt, $token);
    }
    
    public function encode($value)
    {
        if ($value === null) {
            $value = $this->_default !== null ? $this->_default : '';
        }
        return $value->format($this->_timefmt);
    }
}

