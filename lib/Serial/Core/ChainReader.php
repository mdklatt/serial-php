<?php
namespace Serial\Core;

/**
 * Read a sequence of streams as a single set of records.
 *
 * Streams are opened as necessary and closed once they have been read.
 */
class ChainReader extends Reader
{
    protected $closing = false;
    private $readers;  // needed for __destruct() workaround
    private $records;

    /**
     * Return the class name.
     *
     * This *MUST* be implemented by all derived classes. It is sufficient to
     * copy this function verbatim.
     */
    protected static function className()
    {
        return __CLASS__;
    }

    /**
     * Open a ChainReader with automatic stream handling.
     *
     * The arguments are passed to the class constructor. Each stream is closed
     * once it has been exhausted, and any remaining open streams in the 
     * sequence will be closed when the reader's destructor is called.
     *
     * Due to a circular reference, unsetting a ChainReader variable is not
     * enough to trigger its destructor. It will be called when the process 
     * ends, or it can be called explicitly, i.e. $reader->__destruct().
     */
    public static function open(/* $args */)
    {
        $class = new \ReflectionClass(static::className());
        $reader = $class->newInstanceArgs(func_get_args());
        $reader->closing = true;  // automatically call close()
        return $reader;
    }

    /**
     * Initialize this object.
     *
     * The first argument is an array of file paths and/or open streams, the
     * next argument is a reader class name, and addtional arguments are 
     * passed to the reader constructor for each stream in the sequence.
     */
    public function __construct(/* $args */)
    {
        parent::__construct();
        $args = func_get_args();
        if (count($args) < 2) {
            throw new \InvalidArgumentException('missing required arguments');
        }
        array_splice($args, 1, 0, array(array($this, 'openStream')));
        $class = new \ReflectionClass(__NAMESPACE__.'\ReaderIterator');
        $this->readers = $class->newInstanceArgs($args);
        $this->records = new \RecursiveIteratorIterator($this->readers);
        return;        
    }
    
    /**
     * Clean up this object.
     */
    public function __destruct()
    {
        if ($this->closing) {
            // Close all remaining open streams. Ideally, close() could be
            // accessed via $this->records->getInnerIterator(), but for
            // RecursiveIteratorIterators this returns the current value of the
            // inner iterator, not the iterator itself. This behavior is the
            // same as getSubIterator(), so this seems like a PHP bug.
            // <http://php.net/manual/en/class.recursiveiteratoriterator.php>
            $this->readers->close();
        }
        return;
    }

    /**
     * Return the next parsed record from the sequence.
     */
    protected function get()
    {
        $this->records->next();
        if (!$this->records->valid()) {
            throw new StopIteration();
        }
        return $this->records->current();
    }
    
    /**
     * Return an open stream.
     *
     * The argument is either an already opened stream or a path to open as a
     * text file. Derived classes may override this to support other types of
     * streams.
     *
     * This must be public so it can be called by ReaderIterator, but it's not
     * part of the public ChainReader interface.
     */
    public function openStream($expr)
    {
        // This can seemingly be a static, but it's an object method to give
        // more flexibility to any derived class that needs to override it.
        return is_resource($expr) ? $expr : fopen($expr, 'r');
    }
}

/**
 * Return a reader for each stream in a sequence.
 *
 * When used recursively, e.g. via RecursiveIteratorIterator, iterate over all
 * records in all streams in the sequence. This is used by ChainReader and is
 * not part of the Serial\Core API.
 */
class ReaderIterator extends \NoRewindIterator
implements \RecursiveIterator
{
    private $openStream;
    private $readerType;
    private $readerArgs;
    
    /**
     * Initialize this object.
     */
    public function __construct(/* args */)
    {
        $args = func_get_args();
        parent::__construct(new \ArrayIterator(array_shift($args)));
        $this->openStream = array_shift($args);
        $this->readerType = new \ReflectionClass(array_shift($args));
        $this->readerArgs = $args;
        array_unshift($this->readerArgs, null);
        $this->init();
        return;
    }
 
    /**
     * Close all remaining streams in the sequence.
     */
    public function close()
    {
        $inner = $this->getInnerIterator();
        while ($inner->valid()) {
            close($inner->current());
            $inner->next();
        }
        return;
    }
 
    /**
     * Advance to the next stream in the sequence.
     */
    public function next()
    {
        close($this->getInnerIterator()->current());
        parent::next();
        $this->init();
        return;
    }

    /**
     * Return a reader for the current stream in the sequence.
     */
    public function current()
    {
        // Calling this multiple times for the same element results in multiple
        // readers for the same stream, which is bad. In practice, however,
        // current() and next() should always be paired, e.g. by using foreach.
        $this->readerArgs[0] = $this->getInnerIterator()->current();
        return $this->readerType->newInstanceArgs($this->readerArgs);
    }
    
    /**
     * Return true if the current element is iterable.
     */
    public function hasChildren()
    {
        return $this->valid();
    }

    /**
     * Return an iterator for the current element.
     */
    public function getChildren()
    {
        // The Reader base class implements RecursiveIterator.
        return $this->current();
    }
    
    /**
     * Point the inner iterator at an open stream.
     *
     * This needs to be called each time the outer iterator position changes,
     * e.g. via __construct() or next(). Calling code is responsible for
     * closing all streams.
     */
    private function init()
    {
        $inner = $this->getInnerIterator();
        while (($expr = $inner->current()) === null) {
            // If the current element is null, find the next non-null element.
            $inner->next();
            if (!$this->valid()) {
                return;
            }
        }
        $inner[$inner->key()] = call_user_func($this->openStream, $expr);
        return;
    }
}
