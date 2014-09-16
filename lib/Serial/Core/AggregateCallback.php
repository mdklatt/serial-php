<?php
/**
 * Wraps a callback 
 *
 */
class Serial_Core_AggregateCallback
{
    /**
     * Initialize this object.
     */
    public function __construct($callback, $field, $alias=null)
    {
        $this->callback = $callback;
        $this->field = $field;
        if ($alias) {
            $this->alias = $alias;
        }
        else {
            if (!is_array($field)) {
                $message = 'an alias is required for a multi-field reduction';
                throw new InvalidArgumentError($message);
            }
            $this->alias = $field;
        }
        return;
    }
    
    public function __invoke($records)
    {
        if (is_array($this->field)) {
            $args = $this->arrayArgs($records);
        }
        else {
            $args = $this->scalarArgs($records);            
        }
        return array($this->alias => call_user_func($this->callback, $args));
    }
    
    protected function scalarArgs($records)
    {
        $args = array();
        foreach ($records as $record) {
            $args[] = $record[$this->field];
        }
        return $args;
    }
    
    protected function arrayArgs($records)
    {
        $args = array();
        $arr = array_fill_keys($this->field, null);
        foreach ($records as $record) {
            foreach ($arr as $key => $val) {
                $arr[$key] = $record[$key];
            }
            $args[] = $arr;
        }
        return $args;
    }
}
