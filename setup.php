<?php
/**
 * Library installation and packaging script.
 *
 * This is based on python.distutils setup scripts. THIS IS DEPRECATED. USE
 * PHING INSTEAD.
 */

require_once 'config.php';
foreach (array('lib', 'test') as $key) {
    $path = array($CONFIG["{$key}_path"], $CONFIG["{$key}_init"]);
    require_once implode(DIRECTORY_SEPARATOR, $path);
}
$class = new ReflectionClass(str_replace('\\', '_', $NAMESPACE));
$CONFIG['version'] = $class->getConstant('VERSION');


// Execute the script.

$DEBUG = false;  // should be command-line setting
$COMMANDS = array(
    'test' => 'TestCommand', 
    'phar' => 'PharCommand',
);

try {
    if ($argc == 1) {
        echo 'usage: setup.php cmd'.PHP_EOL;
        exit(1);
    }
    exit(main($CONFIG, $argv)); 
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
    echo "\nTHIS IS DEPRECATED IN FAVOR OF PHING.\n\n";
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
 * This is only supported for PHP 5.3+.
 */
class PharCommand extends Command
{
    /** 
     * Execute the command.
     *
     */
    public function __invoke($config)
    {
        $name = "{$config['lib_name']}-{$config['version']}.phar";
        $path = $name;
        @unlink($path);  // always create new archive
        $phar = new Phar($path, 0, $name);
        $phar->buildFromDirectory($config['lib_path'], '/\.php/'); 
        $phar->setStub($this->stub($config['lib_init']));
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
