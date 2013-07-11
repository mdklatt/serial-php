<?php
/**
 * Library installation and packaging script.
 *
 * This is based on python.distutils setup scripts.
 */
require_once 'serial/version.php';


// The package-specific configuration array.

$PACKAGE_CONFIG = array(
    "name" => "serial",
    "version" => SERIAL_VERSION,
);



// Execute the script.

$DEBUG = false;  // should be command-line setting
$COMMANDS = array('test' => 'TestCommand', 'dist' => 'DistCommand');

try {
    if ($argc == 1) {
        echo 'usage: setup.php cmd'.PHP_EOL;
        exit(1);
    }
    exit(main($PACKAGE_CONFIG, $argv)); 
}
catch (Exception $ex) {
    if ($DEBUG) {
        throw $ex;  // force a stack trace
    }
    echo $ex->getMessage().PHP_EOL;
    exit(1);
}


/** 
 * Script controller.
 *
 */
function main($config, $argv)
{
    // Configuration values and command-line options are blended together into
    // a single array with command-line options taking precedence in the case
    // of duplicates.
    global $COMMANDS;
    foreach (array_slice($argv, 1) as $cmdname) {
        if (!($cmdclass = @$COMMANDS[$cmdname])) {
            $message = "unknown command name: {$cmdname}";
            throw new InvalidArgumentException($message);
        }
        $command = new $cmdclass();
        if (($status = $command($config)) != 0) {
            $message = "command failed: {$cmdname} ({$status})";
            throw new RuntimeException($message);
        }        
    }
    return 0;
}


abstract class Command
{
    /** 
     * Return an array of options that this command is interested in.
     * 
     */
    public function register_options()
    {
        return array();
    }
    
    /**
     * Execute the command.
     *
     * The command should return zero on success or any nonzero value on
     * failure.
     */
    abstract public function __invoke($config);
}


/**
 * Execute the test suite for this package.
 *
 */
class TestCommand extends Command
{
    /**
     * Execute the command.
     *
     * A nonzero value is returned if the test suite fails.
     */
    public function __invoke($config)
    {
        system('php -f test/run_tests.php', $status);
        return $status;
    }
}


/**
 * Create a PHP Archive (.phar) file for distribution.
 *
 */
class DistCommand extends Command
{
    /** 
     * Execute the command.
     *
     */
    public function __invoke($config)
    {
        if (!array_key_exists('path', $config)) {
            $config['path'] = $config['name'];
        }
        if (!array_key_exists('init', $config)) {
            $config['init'] = $config['name'].'.php';
        }
        $name = "{$config['name']}-{$config['version']}.phar";
        $path = $name;
        @unlink($path);  // always create new archive
        $phar = new Phar($path, 0, $name);
        $phar->buildFromDirectory($config['path'], '/\.php/'); 
        $phar->setStub($this->stub($config['init']));
        printf("%d file(s) added to archive {$path}".PHP_EOL, $phar->count());
        return 0;        
    }
    
    /**
     * Generate the stub code for this archive.
     *
     */
    private function stub($init)
    {
        $template = file_get_contents('bootstrap.template');
        $stub = str_replace('{{init}}', "'{$init}'", $template);
        $length = strlen($stub) - strlen('{{length}}');
        $length += (strlen($length + strlen($length))); 
        return str_replace('{{length}}', $length, $stub);        
    }
}
