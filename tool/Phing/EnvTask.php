<?php
require_once 'phing/Task.php';

/**
 * Get or set an environment variable.
 *
 */
class EnvTask extends Task
{

    protected $name;  // variable name
    protected $value;  // variable value
    protected $property;  // property to hold variable value

    /** Main entry point. */
    public function main()
    {
        if ($this->name === null) {
            throw new BuildException("You must specify a variable name.", $this->location);
        }
        if (!($this->value === null xor $this->property === null)) {
            throw new BuildException("You must specify either a variable value or a property name.", $this->location);
        }
        if ($this->value === null) {
            // Get an environment variable.
            if (($this->value = getenv($this->name)) === false) {
                throw new BuildException('Failed to get environment variable.');
            }
            $this->project->setProperty($this->property, $this->value);
        } else {
            // Set an environment variable (use an empty string to unset it).
            if (!putenv("{$this->name}={$this->value}")) {
                throw new BuildException('Failed to set environment variable.');      
            }
        }
    }

    /** Set environment variable name. */
    public function setName($name)
    {
        $this->name = $name;
    }

    /** Set environment variable value. */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /** Set property name to hold environment variable. */
    public function setProperty($prop)
    {
        $this->property = $prop;
    }
}
