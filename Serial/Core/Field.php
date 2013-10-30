<?php
/**
 * Tabular data field metadata.
 *
 */
class Serial_Core_Field
{
    public $pos;
    public $dtype;
    public $width;
   
    /**
     * Initialize this object.
     *
     */ 
    public function __construct($pos, $dtype)
    {
        $this->pos = $pos;
        $this->dtype = $dtype;
        $this->width = is_array($pos) ? $pos[1] : 1;
        return;
    }
}
