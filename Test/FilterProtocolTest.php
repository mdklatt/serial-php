<?php
/**
 * Unit tests for the FilterProtocol class.
 *
 */
class Test_FilterProtocolTest extends PHPUnit_Framework_TestCase
{
    protected $data;
    protected $stream;
        
    /**
     * PHPUnit: Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->data = "abc\ndef\nghi";  // test without trailing \n
        $this->filtered = "ABC\nDEF\nGHI";
        $this->stream = tmpfile();
        fwrite($this->stream, $this->data);
        fseek($this->stream, 0);
        return;
    }

    /** 
     * PHPUnit: Tear down the test fixture.
     *
     * This is called after after test is run.
     */
    protected function tearDown()
    {
        // Remove temporary file.
        fclose($this->stream);
        return;
    }

    /**
     * Test input stream filtering with a function.
     *
     */
    public function testInputFunction()
    {
        Serial_Core_FilterProtocol::attach($this->stream, 'strtoupper', 
            STREAM_FILTER_READ);
        $filtered = stream_get_contents($this->stream);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }

    /**
     * Test input stream filtering with a class method.
     *
     */
    public function testInputMethod()
    {
        
        $filter = new FilterProtocolTest_MockFilterClass();
        Serial_Core_FilterProtocol::attach($this->stream, $filter, STREAM_FILTER_READ);
        $filtered = stream_get_contents($this->stream);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }
}

/**
 * Filter class for testing.
 *
 */
class FilterProtocolTest_MockFilterClass
{
    /**
     * Execute the filter.
     *
     */
    public function __invoke($line)
    {
        return strtoupper($line);
    }
}

