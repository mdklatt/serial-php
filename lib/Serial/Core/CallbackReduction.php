<?php
/**
 * Wrap a callback as a reduction. 
 *
 */
class Serial_Core_CallbackReduction
{
    private $field;

    /**
     * Initialize this object.
     *
     * The callback should take an array of values as its argument and return
     * a value, e.g. the built-in function 'array_sum()' The field argument
     * specifies which values to pass to the callback from each record being
     * reduced. This is either a single name or an array of names. In the
     * latter case the callback receives an array of associative arrays.
     * By default the reduction field is named after the input field, or
     * specify an alias. If $field is an array, an alias must be specified.
     */
    public function __construct($callback, $field, $alias=null)
    {
        $this->callback = $callback;
        if (is_array($field)) {
            if (!$alias) {
                $message = 'an alias is required for a multi-field reduction';
                throw new InvalidArgumentException($message);                
            }
            $this->field = array_flip($field);
        }
        else {
            $this->field = $field;
        }
        $this->alias = $alias ? $alias : $field;
        return;
    }
    
    /**
     * Execute the reduction.
     *
     */
    public function __invoke($records)
    {
        if (is_array($this->field)) {
            $args = $this->arrayField($records);
        }
        else {
            $args = $this->scalarField($records);            
        }
        return array($this->alias => call_user_func($this->callback, $args));
    }
    
    /**
     * Retrieve a single field from each record.
     */
    protected function scalarField($records)
    {
        $args = array();
        foreach ($records as $record) {
            $args[] = $record[$this->field];
        }
        return $args;
    }
    
    /**
     * Return an array of fields from each record.
     */
    protected function arrayField($records)
    {
        $args = array();
        foreach ($records as $record) {
            $args[] = array_intersect_key($record, $this->field);
        }
        return $args;
    }
}
