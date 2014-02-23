<?php
/**
 * Base class for tabular data writers.
 *
 * Tabular data is organized into named fields such that each field occupies
 * the same position in each output record. One line of text corresponds to one
 * complete record.
 */
abstract class Serial_Core_TabularWriter extends Serial_Core_Writer
{
    protected $stream;
    protected $fields;
    
    /**
     * Initialize this object.
     *
     */
    public function __construct($stream, $fields, $endl=PHP_EOL)
    {
        $this->stream = $stream;
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->fields[$name] = new Serial_Core_Field($pos, $dtype);
        }
        $this->endl = $endl;
        return;
    }
    
    /**
     * Put a formatted record into the output stream.
     * 
     * This is called after the record has been passed through all filters.
     */
    protected function put($record)
    {
        $tokens = array();
        foreach ($this->fields as $name => &$field) {
            // Convert each field to a string token.
            $token = $field->dtype->encode(@$record[$name]);
            if (is_array($token)) {
                // An array of tokens; expand inline and update the field width
                // and position based on the actual size of the field.
                $tokens = array_merge($tokens, $token);
                $field->pos[1] = $field->dtype->width;
                $field->width = $field->dtype->width;
            }
            else {
                $tokens[] = $token;
            } 
        }
        fwrite($this->stream, $this->join($tokens).$this->endl);
        return;
    }
    
    /**
     * Join an array of string tokens into a line of text.
     *
     */
    abstract protected function join($tokens);
}
