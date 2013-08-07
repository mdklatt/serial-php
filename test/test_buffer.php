<?php
/**
 * Unit tests for buffer.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */


/**
 * Concrete implemenation of _ReaderBuffer for testing.
 *
 */
class ReaderBuffer extends Serial_ReaderBuffer
{
    private $buffer = null;
    
    public function __construct($reader)
    {
        parent::__construct($reader);
        return;
    }
    
    /**
     * Merge every two records.
     *
     */
    protected function queue($record)
    {
        if (!$this->buffer) {
            // First record in a pair.
            $this->buffer = $record;
        }
        else {
            // Complete the pair.
            $record['int'] = $this->buffer['int'];
            $this->output[] = $record;
            $this->buffer = null;
        }
        return;
    }

    /**
     * Flush a partial pair to the output queue.
     *
     */
    protected function uflow()
    {
        if ($this->buffer) {
            // No more input is coming, so output the last record as-is.
            $this->output[] = $this->buffer;
            $this->buffer = null;
        }
        else {
            throw new Serial_EofException();            
        }
        return;
    }
}


/**
 * Concrete implemenation of _WriterBuffer for testing.
 *
 */
class WriterBuffer extends Serial_WriterBuffer
{
    private $buffer = null;
    
    public function __construct($writer)
    {
        parent::__construct($writer);
        return;
    }
    
    /**
     * Merge every two records.
     *
     */
    protected function queue($record)
    {
        if (!$this->buffer) {
            // First record in a pair.
            $this->buffer = $record;
        }
        else {
            // Complete the pair.
            $record['int'] = $this->buffer['int'];
            $this->output[] = $record;
            $this->buffer = null;
        }
        return;
    }

    /**
     * Flush a partial pair to the output queue.
     *
     */
    protected function flush()
    {
        if ($this->buffer) {
            // No more input, so output the last record as-is.
            $this->output[] = $this->buffer;
        }
        return;
    }
}


class MockWriter
{
    public $output = array();

    public function write($record)
    {
        $this->output[] = $record;
        return;
    }
}


/**
 * Unit testing for buffer classes.
 *
 */
abstract class BufferTest extends PHPUnit_Framework_TestCase
{   
    static public function reject_filter($record)
    {
        return $record['int'] != 123 ? $record : null;
    }
    
    protected $input;
    protected $output;
    protected $reader;
    protected $writer;

    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->input = array(
            array('int' => 123, 'arr' => array(array('x' => 'abc', 'y' => 'def'))),
            array('int' => 456, 'arr' => array(array('x' => 'ghi', 'y' => 'jkl'))),
            array('int' => 789, 'arr' => array(array('x' => 'mno', 'y' => 'pqr'))),
        );
        $this->output = array(
            array('int' => 123, 'arr' => array(array('x' => 'ghi', 'y' => 'jkl'))),
            array('int' => 789, 'arr' => array(array('x' => 'mno', 'y' => 'pqr'))),
        );
        return;
    }
}


/**
 * Unit testing for the ReaderBuffer class.
 *
 */
class ReaderBufferTest extends BufferTest
{
    protected $buffer;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        parent::setUp();
        $reader = new ArrayIterator($this->input);
        $this->buffer = new ReaderBuffer($reader);
        return;
    }

    /**
     * Test the iterator interface.
     *
     */
    public function test_iter()
    {
        $output = iterator_to_array($this->buffer);
        $this->assertEquals($this->output, $output);
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function test_filter()
    {
        $this->buffer->filter('ReaderBufferTest::reject_filter');
        array_splice($this->output, 0, 1);
        $this->test_iter();
        return;
    }
}


/**
 * Unit testing for the WriterBuffer class.
 *
 */
class WriterBufferTest extends BufferTest
{
    protected $buffer;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->writer = new MockWriter();
        $this->buffer = new WriterBuffer($this->writer);
        return;
    }

    /**
     * Test the write() method.
     *
     */
    public function test_write()
    {
        foreach ($this->input as $record) {
            $this->buffer->write($record);
        }
        $this->buffer->close();
        $this->assertEquals($this->output, $this->writer->output);
        return;
    }

    /**
     * Test the dump() method.
     *
     */
    public function test_dump()
    {
        $this->buffer->dump($this->input);
        $this->assertEquals($this->output, $this->writer->output);
        return;
    }
    
    /**
     * Test the filter() method.
     *
     */
    public function test_filter()
    {
        $this->buffer->filter('BufferTest::reject_filter');
        array_splice($this->output, 0, 1);
        $this->test_dump();
        return;
    }
}
