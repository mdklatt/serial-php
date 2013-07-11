<?php
/**
 * Internal utility classes and functions.
 *
 */

class Serial_Field
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


class Serial_Sequence implements Countable
{
    private $data;
    private $count;
    private $slice;  // function alias
       
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
    
    public function get($pos) {
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
