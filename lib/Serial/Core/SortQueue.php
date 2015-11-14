<?php
namespace Serial\Core;

/**
 * Sort a sequence of records.
 *
 * This is used internally by SortReader and SortWriter and is not part of the
 * public library API.
 */
class SortQueue
{
    public $sorted = array();

    private $keyFunc;
    private $groupFunc;
    private $groupKey;
    private $buffer = array();
    
    public function __construct($key, $group=null)
    {
        if (!is_callable($key)) {
            // Use the default key function.
            $key = array(new KeyFunc($key), '__invoke');
        }
        $this->keyFunc = $key;
        if ($group && !is_callable($group)) {
            // Use the default key function.
            $group = array(new KeyFunc($group), '__invoke');
        }
        $this->groupFunc = $group;
        return;        
    }
    
    public function push($record)
    {
        if ($this->groupFunc) {
            $groupKey = call_user_func($this->groupFunc, $record);
            if ($groupKey != $this->groupKey) {
                // This is a new group, flush the previous group.
                $this->flush();
            }
            $this->groupKey = $groupKey;
        }
        $this->buffer[] = $record;
    }
        
    public function flush()
    {
        if (!$this->buffer) {
            return;
        }
        $keyCols = array();
        foreach ($this->buffer as $row => $record) {
            // Build an N x K array of key values to use to with multisort.
            $keyCols[] = call_user_func($this->keyFunc, $record); 
        }
        array_multisort($keyCols, $this->buffer);
        $this->sorted = $this->buffer;
        $this->buffer = array();
        return;        
    }
}