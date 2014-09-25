<?php
require_once 'phing/Task.php';

/**
 * Include a PHP file.
 *
 */
class PhpIncludeTask extends Task
{

    protected $path;  // file path to include
    protected $require = true;  // true if file is required

    /** Main entry point. */
    public function main()
    {
        if ($this->path === null) {
            throw new BuildException('You must specify a file path.', $this->location);
        }
        if ((include_once $this->path) === false && $this->require) {
            throw new BuildException("Could not include required file {$this->path}");
        }
    }

    /** Set include path. */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /** Set boolean controlling whether included file is required. */
    public function setRequire($require)
    {
        $this->require = ($require === true);
    }
}
