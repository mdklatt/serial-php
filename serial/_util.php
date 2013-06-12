<?php

class Field
{
    public $pos;
    public $dtype;
    public $width;
    
    public function __construct($pos, $dtype)
    {
        $this->pos = $pos;
        $this->dtype = $dtype;
        $this->width = is_array($pos) ? $pos[1] : 1;
        return;
    }
}


class Sequence
implements Countable
{
    private $_data;
    private $_count;
    private $_slice;  // function alias
    
    
    public function __construct($data)
    {
        $this->_data = $data;
        if (is_string($data)) {
            $this->_count = strlen($data);
            $this->_slice = 'substr';
        }
        else {
            $this->_count = count($data);
            $this->_slice = 'array_slice';
        }
        return;
    }
    
    public function get($pos) {
        if (is_array($pos)) {
            // Slice notation.
            list($beg, $len) = $pos;
            return call_user_func($this->_slice, $this->_data, $beg, $len);
        }
        return $this->_data[$pos];
    }
      
    /**
     * Countable: Return the length of the sequence.
     *
     */
    public function count()
    {
        return $this->_count;
    }
}
