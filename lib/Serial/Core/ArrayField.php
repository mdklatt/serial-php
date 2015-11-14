<?php
namespace Serial\Core;

/**
 * Translate text tokens to/from an array of PHP values.
 *
 */
class ArrayField
{
    public $name;
    public $pos;
    public $width;
    public $fixed;
    private $fields;
    private $default;
    private $stride = 0;

    /**
     * Initialize this object.
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
        $this->fixed = $this->fields[0]->fixed;
        return;
    }
    
    /**
     * Convert a sequence of string tokens to PHP values.
     *
     * The input is an array of strings for delimited data for a string for
     * fixed-width data. This is called by a Reader and does not need to be 
     * called by the user.
     */
    public function decode($tokens)
    {
        if ($this->fixed) {
            $values = $this->decodeString($tokens);
        }
        else {
            $values = $this->decodeArray($tokens);
        }
        return $values ? $values : $this->default;
    }
    
    /**
     * Convert an array of PHP values to an array of string tokens.
     *
     * If the argument is null or an empty array the default field value is
     * used (null is encoded as an empty array). Each element of the array
     * should be an associative array that corresponds to the field
     * definitions for this array.
     */
    public function encode($values)
    {
        if (!$values) {
            $values = $this->default;
        }
        $tokens = array();
        foreach ($values as $elem) {
            foreach ($this->fields as $field) {
                $value = isset($elem[$field->name]) ? $elem[$field->name] : null;
                $tokens[] = $field->encode($value);
            }
        }
        return $tokens;
    }
    
    /**
     * Decode a token string for fixed-width data.
     */
    private function decodeString($tokens)
    {
        $values = array();
        foreach (str_split($tokens, $this->stride) as $elem) {
            $value = array();
            foreach ($this->fields as $field) {
                list($beg, $len) = $field->pos;
                $value[$field->name] = $field->decode(substr($elem, $beg, $len));
            }
            $values[] = $value;
        }
        return $values;        
    }

    /**
     * Decode a token array for delimited data.
     */
    private function decodeArray($tokens)
    {
        $values = array();
        foreach (array_chunk($tokens, $this->stride) as $elem) {
            $value = array();
            foreach ($this->fields as $pos => $field) {
               $value[$field->name] = $field->decode($elem[$pos]);
            }
            $values[] = $value;
        }            
        return $values;
    }
}
