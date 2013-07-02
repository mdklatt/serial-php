<?php
/**
 * Writer types.
 *
 */
require_once('_util.php');

/**
 * Base class for all writers.
 *
 */
abstract class Serial_Writer
{
    private $_filters = array();
    
    /**
     * Clear all filters (default) or add a filter to this writer.
     *
     * A filter is a callback that accepts a data record as its only argument.
     * Based on this record the filter can perform the following actions:
     * 1. Return null to reject the record (the iterator will drop it).
     * 2. Return the data record as is.
     * 3. Return a new/modified record.
     */
    public function filter(/* variadic: $callbacks */)
    {
        if (func_num_args() == 0) {
            // Default: clear all filters.
            $this->_filters = array();
            return;
        }
        foreach (func_get_args() as $callback) {
            if (is_array($callback)) {
                $this->_filters = array_merge($this->_filters, $callback);
            }
            else {
                $this->_filters[] = $callback;
            }            
        }
        return;
    }


    public function write($record)
    {   
        foreach ($this->_filters as $callback) {
            if (!($record = call_user_func($callback, $record))) {
                return;
            }
        }
        $this->_put($record);
        return;
    }

    public function dump($records)
    {
        foreach ($records as $record) {
            $this->write($record);
        }
        return;
    }
        
    abstract protected function _put($record); 
}


abstract class Serial_TabularWriter extends Serial_Writer
{
    protected $_stream;
    protected $_fields;
    
    public function __construct($stream, $fields, $endl=PHP_EOL)
    {
        $this->_stream = $stream;
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->_fields[$name] = new Serial_Field($pos, $dtype);
        }
        $this->_endl = $endl;
        return;
    }
    
    protected function _put($record)
    {
        $tokens = array();
        foreach ($this->_fields as $name => &$field) {
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
        fwrite($this->_stream, $this->_join($tokens).$this->_endl);
        return $this->_join($tokens);
    }
    
    abstract protected function _join($tokens);
}


class Serial_DelimitedWriter extends Serial_TabularWriter
{
    private $_delim;
    
    public function __construct($stream, $fields, $delim, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, $endl);
        $this->_delim = $delim;
        return;
    }
    
    protected function _join($tokens)
    {
        return implode($this->_delim, $tokens);
    }
}


class Serial_FixedWidthWriter extends Serial_DelimitedWriter
{
    public function __construct($stream, $fields, $endl=PHP_EOL)
    {
        parent::__construct($stream, $fields, '', $endl);
        return;
    }
}


