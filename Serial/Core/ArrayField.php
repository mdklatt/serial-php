<?php
/**
 * Translate text tokens to/from an array of PHP values.
 *
 */
class Serial_Core_ArrayField
{
    public $name;
    public $pos;
    public $width;
    private $fields;
    private $default;
    private $stride = 0;

    /**
     * Initialize this object.
     *
     */
    public function __construct($name, $pos, $fields, $default=array())
    {
        $this->name = $name;
        $this->pos = $pos;
        $this->default = $default;
        $this->width = $pos[1];
        $this->fields = $fields; 
        foreach ($this->fields as $field) {
            $this->stride += $field->width;
        }
        return;
    }
    
    /**
     * Convert a string to a PHP value.
     *
     * This is called by a Reader and does not need to be called by the user.
     */
    public function decode($tokens)
    {
        $tokens = new Serial_Core_Sequence($tokens);
        $values = array();
        for ($beg = 0; $beg < count($tokens); $beg += $this->stride) {
            $elem = new Serial_Core_Sequence($tokens->get(array($beg, $this->stride)));
            $value = array();
            foreach ($this->fields as $field) {
                $value[$field->name] = $field->decode($elem->get($field->pos));
            }
            $values[] = $value;
        }
        $this->width = count($values) * $this->stride;
        return $values ? $values : $this->default;
    }
    
    /**
     * Convert an array of PHP values to an array of string tokens.
     *
     * If the argument is null or an empty array the default field value is
     * used (null is encoded as an empty array). Each element of the array
     * should be an associative array that corresponsds the to the field
     * definitions for this array.
     */
    public function encode($values)
    {
        if (!$values) {
            $values = $this->default;
        }
        if ($this->width === null) {
            // Update the width of a variable-length array with each record.
            $this->width = count($values) * $this->stride;            
        }
        $tokens = array();
        foreach ($values as $elem) {
            foreach ($this->fields as $field) {
                $tokens[] = $field->encode($elem[$field->name]);
            }
        }
        return $tokens;
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
