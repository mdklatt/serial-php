<?php
/**
 * PHP Archive (Phar) stub to autoload a library.
 *
 * The library's header file is automatically loaded when the phar file is 
 * included, giving the client code access to the library. This file is used
 * during the Phar creation process.
 */


////////////////////////////////////////////////
////                                        ////
////   *DO NOT* MODIFY THIS FILE WITHOUT    ////
////  UPDATING THE STUB LENGTH PARAMETER!   ////
////                                        ////
////////////////////////////////////////////////


// This was adapted from the code generated by Phar::createDefaultStub() to fix
// the limitations of the default stub, namely that its global definitions make
// it impossible to use in phar files that might be used simultaneously. The
// Phar extension is used if it's available (PHP 5.3+ by default or optionally
// on PHP 5.2), or else a self-contained decoder is defined. NB: Within an
// archive context, __FILE__ refers to the archive file, not this file.
//
// Make the following modifications to use this stub with another library:
// * Change the header file name (two locations).
// * Update the stub length with the new file size.

if (class_exists('Phar')) {
    // Use the Phar extension.
    Phar::mapPhar();
    include 'phar://'.__FILE__.DIRECTORY_SEPARATOR.'Serial/Core.php';
    return;
}

if (!class_exists('PharExtractor')) {
    /**
     * A basic Phar file extractor for PHP 5.2.
     *
     * Files are extracted to a temporary directory that is available for the
     * duration of the script, and any initialization file (e.g. a library 
     * header) is loaded.
     */
    class PharExtractor
    {
        // <http://php.net/manual/en/phar.fileformat.phar.php>
        
        private static $compression = array(
            // Available compression algorithms.
            'gzip' => array(0x1000, 'gzinflate'),
            'bzip2' => array(0x2000, 'bzdecompress'),
        );
        private static $registry = array();
        private $phar;
        private $manifest;
        private $root;

        /**
         * Extract the archive and load the init file.
         *
         * This is the complete client interface. This needs to be called by 
         * the stub.
         */
        public static function extract($phar, $init, $stubLen)
        {
            // Use a static registry prevents the need for global variables and
            // delays file cleanup via the destructor until the end, but there
            // may be some problematic edge cases. Consider a global object 
            // whose destructor uses a previously unused class; the relevant
            // PharExtractor destructor may have already been called, deleting
            // the required class file.
            $extractor = new PharExtractor($phar, $stubLen);
            self::$registry[] = $extractor;
            $extractor->__invoke($init);
            return;
        }
                
        /**
         * Initialize this object.
         *
         * This is not part of the public interface; use extract().
         */
        protected function __construct($phar, $stubLen)
        {
            // Read the file manifest.
            $this->phar = fopen($phar, 'rb');
            fseek($this->phar, $stubLen);  // skip over stub 
            $fmt = 'V1mlen/V1count/n1api/V1flags';
            $this->manifest = unpack($fmt, fread($this->phar, 14));
            $this->manifest['alias'] = $this->string();
            $this->manifest['meta'] = unserialize($this->string());
            $this->manifest['files'] = array();
            for ($i = 0; $i < $this->manifest['count']; ++$i) {
                $this->manifest['files'][] = $this->entry();
            }
            return;
        }
        
        /**
         * Extract the archive and load the init file.
         *
         * The directory holding the extracted files is added to the PHP
         * include path.
         */
        protected function __invoke($init=null)
        {
            // Files will be extracted to a uniquely-named directory in the
            // system tmp path; these files need to be cleaned up by the 
            // destructor. 
            $this->root = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('phar');
            if (!mkdir($this->root, 0777)) {
                // Web applications may not have write privileges to the system
                // tmp path; Phar::getDefaultStub() attempts to get around this
                // using session_save_path().
                $message = "could not create directory: {$this->root}";
                throw new RuntimeException($message);
            }
            foreach ($this->manifest['files'] as $entry) {
                // Write each archive file to the temporary directory, creating
                // new directories as necessary.
                // TODO: For now mkdir errors are surpressed; any errors are
                // probably because the directory was already created, but need
                // better checking.
                $path = $this->root.DIRECTORY_SEPARATOR.$entry['name'];
                @mkdir(dirname($path), 0777, true);
                file_put_contents($path, $this->file($entry));
            }
            fclose($this->phar);
            set_include_path(get_include_path().PATH_SEPARATOR.$this->root);
            if ($init) {
                require $init;
            }
            return;
        }
        
        /**
         * Read a file from the phar archive.
         */
        private function file($entry)
        {
            $data = fread($this->phar, $entry['csize']);
            foreach (self::$compression as $name => $item) {
                // Uncompress data if necessary.
                list($flag, $func) = $item;
                if ($entry['flags'] & $flag) {
                    $data = call_user_func($func, $data);
                    break;
                }
            }
            if (crc32($data) != $entry['crc32']) {
                var_dump(crc32($data), $entry['crc32']);
                $message = "checksum failed for {$entry['name']}";
                throw new RuntimeException($message);
            }
            return $data;
        }
        
        /**
         * Read a string from the phar archive.
         */
        private function string()
        {
            // The string is defined by a 4-byte count followed by the given
            // number of characters.
            list($len) = array_values(unpack('V', fread($this->phar, 4)));
            return $len ? fread($this->phar, $len) : null;
        }
        
        /**
         * Read a manifest entry from the phar archive.
         */
        private function entry()
        {
            $entry = array('name' => $this->string());
            $fmt = 'V1usize/V1time/V1csize/V1crc32/V1flags';
            $entry = array_merge($entry, unpack($fmt, fread($this->phar, 20)));
            $entry["meta"] = unserialize($this->string());
            return $entry;
        }

        /**
         * Remove the file tree beginning at $root (inclusive).
         */
        private function rmtree($root)
        {
            foreach (new DirectoryIterator($root) as $item) {
                // Delete each file and recursively call rmtree() for each
                // directory.
                if ($item->isDot()) {
                    // VERY IMPORTANT: Prevent traversal of parent directory
                    // and infinite recursion on this directory.
                    continue;
                }
                $path = $item->getPathname();
                $item->isDir() ? $this->rmtree($path) : unlink($path);
            }
            rmdir($root);
            return;
        }

        /**
         * Clean up this object.
         */
        public function __destruct()
        {
            // Remove the extracted files.
            $this->rmtree($this->root);
            return;
        }
    }
}

// The stub length parameter *MUST* match the length of the stub as inserted
// into by Phar::setStub(). For convienience, using two trailing blank lines
// (LF) here will ensure that the stub length matches the file length.

PharExtractor::extract(__FILE__, 'Serial/Core.php', 8320);
__HALT_COMPILER(); ?>
