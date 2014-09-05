serial-php
==========

Overview
--------
[![status][1]][2]

The [**serial-php**][3] library provides tools for reading and writing
record-oriented data in various formats. The core library is contained in the
`Serial/Core` directory. Library extensions will have their own `Serial`
subdirectories.


Features
--------
* Read/write delimited and fixed-width data
* Named and typed data fields
* Data filtering via callbacks
* Advanced data transformations


Requirements
------------
* PHP 5.2 - 5.5


Installation
------------
Place the [Serial][6] directory anywhere in the PHP [include path][7], or use
a self-contained *phar* file downloaded from GitHub or created using the
[`setup.php`][8] script (requires PHP 5.3+ or the [`Phar` extension][4]).

    php -f setup.php phar


Usage
-----

Include the library header or the *phar* file. 
  
    require 'Serial/Core.php';
    require 'serial-core-0.1.0.phar';    


The [tutorial][9] has examples of how to use and extend this library.


Testing
-------

Use the [`setup.php`][8] script to run the test suite (requires [PHPUnit][5]). 

    php -f setup.php test



<!-- REFERENCES -->
[1]: https://travis-ci.org/mdklatt/serial-php.png?branch=master "Travis build status"
[2]: https://travis-ci.org/mdklatt/serial-php "Travis-CI"
[3]: http://github.com/mdklatt/serial-php "GitHub/serial-php"
[4]: http://pecl.php.net/package/phar "Phar extension"
[5]: http://pear.phpunit.de "PHPUnit PEAR package"
[6]: http://github.com/mdklatt/serial-php/tree/master/Serial "Serial tree"
[7]: http://www.php.net/manual/en/ini.core.php#ini.include-path  "PHP include path"
[8]: https://github.com/mdklatt/serial-php/blob/master/setup.php "setup.php"
[9]: http://github.com/mdklatt/serial-php/blob/master/doc/tutorial.md "tutorial.md"
