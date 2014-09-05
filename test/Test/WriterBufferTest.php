<?php
/**
 * Unit testing for the WriterBuffer class.
 *
 */
class Test_WriterBufferTest extends Test_BufferTest
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
        $this->writer = new WriterBufferTest_MockWriter();
        $this->buffer = new WriterBufferTest_MockWriterBuffer($this->writer);
        return;
    }

    /**
     * Test the write() method.
     *
     */
    public function testWrite()
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
    public function testDump()
    {
        $this->buffer->dump($this->input);
        $this->assertEquals($this->output, $this->writer->output);
        return;
    }
    
    /**
     * Test the filter() method.
     *
     */
    public function testFilter()
    {
        $this->buffer->filter('Test_BufferTest::reject_filter');
        array_splice($this->output, 0, 1);
        $this->testDump();
        return;
    }
}


/**
 * Concrete implemenation of WriterBuffer for testing.
 *
 */
class WriterBufferTest_MockWriterBuffer extends Serial_Core_WriterBuffer
{
    private $buffer = null;
    
    /**
     * Initialize this object.
     *
     */
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


/**
 * Capture WriterBuffer output for testing.
 *
 */ 
class WriterBufferTest_MockWriter
{
    public $output = array();

    /**
     * Implement the Writer interface.
     *
     */
    public function write($record)
    {
        $this->output[] = $record;
        return;
    }
}
