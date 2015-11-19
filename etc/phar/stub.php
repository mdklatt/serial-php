<?php
/**
 * PHP Archive (Phar) stub to autoload a library.
 *
 * The library's header file is automatically loaded when the phar file is 
 * included, giving the client code access to the library. This file is used
 * during the Phar creation process.
 */
Phar::mapPhar();
require 'phar://'.__FILE__.DIRECTORY_SEPARATOR.'Serial/Core/autoload.php';
__HALT_COMPILER(); 
?>
