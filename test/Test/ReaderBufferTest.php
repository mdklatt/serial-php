<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

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
        $reader = new \ArrayIterator($this->input);
        $this->buffer = new ReaderBufferMockReaderBuffer($reader);
        return;
    }

    /**
     * Test the iterator interface.
     *
     */
    public function testIter()
    {
        $output = iterator_to_array($this->buffer);
        $this->assertEquals($this->output, $output);
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function testFilter()
    {
        $this->buffer->filter(
            __NAMESPACE__.'\ReaderBufferTest::reject_filter');
        array_splice($this->output, 0, 1);
        $this->testIter();
        return;
    }
}


/**
 * Concrete implemenation of ReaderBuffer for testing.
 *
 */
class ReaderBufferMockReaderBuffer extends Core\ReaderBuffer
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
            throw new Core\StopIteration();            
        }
        return;
    }
}
