serial-php
==========

Overview
--------
[![status][1]][2]

The [**serial-php**][3] library provides tools for reading and writing
record-oriented data in various formats. The core library is contained in the
`Serial/Core` directory. Library extensions are contained in their own `Serial`
subdirectories.


Requirements
------------
* PHP 5.2 - 5.3 (5.4?)
* [PHPUnit][4] (optional; required to run test suite)


Installation
------------
Place the [Serial][5] directory anywhere in the PHP [include path][6], or
use the [setup script][7] to create a self-contained *phar* file.

    php -f setup.php phar


Usage
-----
Include the package autoloader or the appropriate *phar* file.

    require 'Serial/Core/autoload.php';
    require 'serial-core-0.1.0.phar';

The [tutorial][8] has examples of how to use and extend this library.



<!-- REFERENCES -->
[1]: https://travis-ci.org/mdklatt/serial-php.png?branch=master "Travis build status"
[2]: https://travis-ci.org/mdklatt/serial-php "Travis-CI"
[3]: http://github.com/mdklatt/serial-php "GitHub/serial-php"
[4]: http://pear.phpunit.de "PHPUnit PEAR package"
[5]: http://github.com/mdklatt/serial-php/tree/master/Serial "Serial tree"
[6]: http://www.php.net/manual/en/ini.core.php#ini.include-path  "PHP include path"
[7]: https://github.com/mdklatt/serial-php/blob/master/setup.php "setup.php"
[8]: http://github.com/mdklatt/serial-php/blob/master/doc/tutorial.md "tutorial.md"
