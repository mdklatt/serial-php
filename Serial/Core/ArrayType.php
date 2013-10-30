<?php
/**
 * Translate text tokens to/from an array of PHP values.
 *
 */
class Serial_Core_ArrayType extends Serial_Core_DataType
{
    public $width;
    private $fields = array();
    private $stride = 0;

    /**
     * Initialize this object.
     *
     */
    public function __construct($fields, $default=array())
    {
        parent::__construct('%s', $default);
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $field = new Serial_Core_Field($pos, $dtype);
            $this->fields[$name] = $field;
            $this->stride += $field->width;
        }
        return;
    }
    
    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
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
    
    /**
     * Convert a PHP value to a string.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
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


/**
 * Treat a string or an array as a data sequence.
 *
 */
class Serial_Core_Sequence implements Countable
{
    private $data;
    private $count;
    private $slice;  // function alias
       
    /**
     * Initialize this object.
     *
     */
    public function __construct($data)
    {
        $this->data = $data;
        if (is_string($data)) {
            $this->count = strlen($data);
            $this->slice = 'substr';
        }
        else {
            $this->count = count($data);
            $this->slice = 'array_slice';
        }
        return;
    }
    
    /**
     * Retrieve a single element for slice from the sequence.
     *
     */
    public function get($pos) 
    {
        if (is_array($pos)) {
            // Slice notation.
            list($beg, $len) = $pos;
            return call_user_func($this->slice, $this->data, $beg, $len);
        }
        return $this->data[$pos];
    }
      
    /**
     * Countable: Return the length of the sequence.
     *
     */
    public function count()
    {
        return $this->count;
    }
}
