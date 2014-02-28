<?php
/**
 * Library installation and packaging script.
 *
 * This is based on python.distutils setup scripts.
 */
require_once 'Serial/Core/autoload.php';
require_once 'Test/autoload.php';

// The package-specific configuration array.

$PACKAGE_CONFIG = array(
    'name' => 'serial-core',
    'path' => 'Serial/Core',
    'version' => Serial_Core::VERSION,
);



// Execute the script.

$DEBUG = false;  // should be command-line setting
$COMMANDS = array('test' => 'TestCommand', 'phar' => 'PharCommand');

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
    echo 'ERROR: '.$ex->getMessage().PHP_EOL;
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
        if (($status = $command->__invoke($config)) != 0) {
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
        return Test::run();
    }
}


/**
 * Create a PHP Archive (.phar) file.
 *
 * For PHP 5.2 the optional Phar extension is required.
 */
class PharCommand extends Command
{
    /** 
     * Execute the command.
     *
     */
    public function __invoke($config)
    {
        if (!class_exists('Phar')) {
            $message = 'the phar command requires the Phar extension';
            throw new RuntimeException($message);
        }
        if (!array_key_exists('init', $config)) {
            $config['init'] = 'autoload.php';
        }
        $name = "{$config['name']}-{$config['version']}.phar";
        $path = $name;
        @unlink($path);  // always create new archive
        $phar = new Phar($path, 0, $name);
        $phar->buildFromDirectory($config['path'], '/\.php/'); 
        $phar->setStub($this->stub($config['init']));
        printf("%d file(s) added to archive {$path}".PHP_EOL, $phar->count());
        // TODO: Make verification a command-line option.
        return $this->verify($path);        
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
    
    /** 
     * Verify the archive by running the test suite on it.
     *
     */
    private function verify($path)
    {
        $env = "PHPUNIT_TEST_SOURCE";
        putenv("{$env}={$path}");
        echo "verifying {$path}".PHP_EOL;
        $status = Test::run();
        putenv("{$env}=");  // unset variable
        return $status;
    }
}
