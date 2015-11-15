<?php
require_once 'phing/Task.php';

/**
 * Returns a PHP global or class constant.
 *
 */
class PhpConstTask extends Task
{

    protected $name;  // constant name
    protected $property;  // property to hold return value

    /** Main entry point. */
    public function main()
    {
        if ($this->name === null) {
            throw new BuildException("You must specify the constant name.", $this->location);
        }
        if ($this->property === null) {
            throw new BuildException("You must specify a property to hold the constant.", $this->location);
        }
        $this->project->setProperty($this->property, constant($this->name));
    }

    /** Set constant name. */
    public function setName($name)
    {
        $this->name = $name;
    }

    /** Set class which contains constant. */
    public function setClass($class)
    {
        $this->class = Phing::import($class);
    }
    
    /** Sets property name to hold constant. */
    public function setProperty($prop)
    {
        $this->property = $prop;
    }
}
