<?php
/**
 * Unit testing for the ReaderBuffer class.
 *
 */
class Test_ReaderBufferTest extends Test_BufferTest
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
        $this->buffer = new ReaderBufferTest_MockReaderBuffer($reader);
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
        $this->buffer->filter('Test_ReaderBufferTest::reject_filter');
        array_splice($this->output, 0, 1);
        $this->testIter();
        return;
    }
}


/**
 * Concrete implemenation of ReaderBuffer for testing.
 *
 */
class ReaderBufferTest_MockReaderBuffer extends Serial_Core_ReaderBuffer
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
            throw new Serial_Core_StopIteration();            
        }
        return;
    }
}
