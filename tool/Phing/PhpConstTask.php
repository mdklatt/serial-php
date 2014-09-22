<?php
require_once 'phing/Task.php';

/**
 * Returns a PHP global or class constant.
 *
 */
class PhpConstTask extends Task
{

    protected $name;  // constant name
    protected $class;  // class name for a class constant
    protected $property;  // property to hold return value

    /** Main entry point. */
    public function main()
    {
        if ($this->name === null) {
            throw new BuildException("You must specify the constant name.", $this->location);
        }
        if ($this->property === null) {
            throw new BuildException("You must specify a property to hold the contant.", $this->location);
        }
        if ($this->class === null) {
            // Get global constant.
            $value = constant($this->name);
        } else {
            $class = new ReflectionClass($this->class);
            $value = $class->getConstant($this->name);
        }
        $this->project->setProperty($this->property, $value);
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
