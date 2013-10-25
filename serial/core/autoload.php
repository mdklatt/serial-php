<?php
/**
 * Load the serial-core library.
 *
 */

// Global functions and constants cannot be autoloaded.

include 'version.php';
include 'util.php';


/**
 * Autoloader for the serial-core library.
 *
 */
class Serial_Core_Autoloader
{
    // This is a compromise between putting every class in its own file
    // and loading the entire library. Classes are grouped into modules
    // that are loaded on demand.

    private $classes = array(
        'ArrayType' => 'dtype.php',
        'ConstType' => 'dtype.php',
        'DataType' => 'dtype.php',
        'DateTimeType' => 'dtype.php',
        'DelimitedReader' => 'reader.php',
        'DelimitedWriter' => 'writer.php',
        'Field' => 'util.php',
        'FixedWidthReader' => 'reader.php',        
        'FixedWidthWriter' => 'writer.php',
        'FloatType' => 'dtype.php',
        'IntType' => 'dtype.php',
        'IStreamAdaptor' => 'stream.php',
        'Reader' => 'reader.php',
        'ReaderBuffer' => 'buffer.php',
        'Sequence' => 'util.php',
        'StringType' => 'dtype.php',
        'TabularReader' => 'reader.php',
        'TabularWriter' => 'writer.php',
        'Writer' => 'writer.php',
        'FieldFilter' => 'filter.php',
    );
    
    /**
     * Load the module containing the desired class.
     *
     */
    public function __invoke($name)
    {   
        list($lib, $pkg, $cls) = array_pad(explode('_', $name), 3, null);
        if ($lib != 'Serial' || $pkg != 'Core') {
            return;
        }
        if (isset($this->classes[$cls])) {
            include_once $this->classes[$cls];
        }
        return;
    }
}

// PHP 5.2 does not support callable objects so call __invoke explicitly.
$loader = new Serial_Core_Autoloader();
spl_autoload_register(array($loader, '__invoke'));

