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
* Filtering
* Aggregation
* Sorting
* Advanced data transformations


Requirements
------------
* PHP 5.2 - 5.5


Installation
------------
Place the [Serial][6] directory or a self-contained *phar* file anywhere in the 
PHP [include path][7]. A *phar* file can be downloaded from GitHub or created
using the [build script][10] (requires [Phing][8] and PHP 5.3+ or the 
[Phar extension][4]).

    phing phar


Usage
-----

Include the library header or the *phar* file. 
  
    require 'Serial/Core.php';
    
    require 'serial-core-v0.1.0.phar';    


The [tutorial][9] has examples of how to use and extend this library.


Testing
-------

Use the [build script][10] to run the test suite (requires [Phing][8] and
[PHPUnit][5]). 

    phing test
    phing test -Dlib.header=serial-core-0.1.0.phar  # test a phar file



<!-- REFERENCES -->
[1]: https://travis-ci.org/mdklatt/serial-php.png?branch=master "Travis build status"
[2]: https://travis-ci.org/mdklatt/serial-php "Travis-CI"
[3]: http://github.com/mdklatt/serial-php "GitHub/serial-php"
[4]: http://pecl.php.net/package/phar "Phar extension"
[5]: https://github.com/sebastianbergmann/phpunit "PHPUnit"
[6]: https://github.com/mdklatt/serial-php/tree/master/Serial "Serial tree"
[7]: http://php.net/manual/en/ini.core.php#ini.include-path  "PHP include path"
[8]: http://www.phing.info/ "Phing"
[9]: http://github.com/mdklatt/serial-php/blob/master/doc/tutorial.md "tutorial.md"
[10]: https://github.com/mdklatt/serial-php/blob/master/build.xml
