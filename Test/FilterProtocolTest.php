<?php
/**
 * Unit tests for the FilterProtocol class.
 *
 */
class Test_FilterProtocolTest extends PHPUnit_Framework_TestCase
{
    protected $data;
    protected $stream;
    protected $tmpnam;
        
    /**
     * PHPUnit: Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        // TODO: Test with and without trailing newline.
        // TODO: Test with empty data.
        $this->data = "abc\ndef\nghi";
        $this->filtered = "ABC\nDEF\nGHI";
        $this->tmpname = tempnam(sys_get_temp_dir(), 'tmp');
        return;
    }

    /** 
     * Tear down the test fixture.
     *
     * This is called after after test is run.
     */
    protected function tearDown()
    {
        // Remove temporary file.
        unlink($this->tmpname);
        return;
    }

    /**
     * Test input stream filtering with a function.
     *
     */
    public function testInputFunction()
    {
        file_put_contents($this->tmpname, $this->data);
        $stream = fopen($this->tmpname, 'r');
        Serial_Core_FilterProtocol::attach($stream, 'strtoupper'); 
        $filtered = stream_get_contents($stream);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }

    /**
     * Test input stream filtering with a class method.
     *
     */
    public function testInputMethod()
    {
        file_put_contents($this->tmpname, $this->data);
        $stream = fopen($this->tmpname, 'r');
        $filter = new FilterProtocolTest_MockFilterClass();
        Serial_Core_FilterProtocol::attach($stream, $filter); 
        $filtered = stream_get_contents($stream);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }

    /**
     * Test output stream filtering with a function.
     *
     */
    public function testOutputFunction()
    {
        $stream = fopen($this->tmpname, 'w');
        Serial_Core_FilterProtocol::attach($stream, 'strtoupper'); 
        fwrite($stream, $this->data);
        fclose($stream);
        $filtered = file_get_contents($this->tmpname);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }

    /**
     * Test output stream filtering with a class method.
     *
     */
    public function testOutputMethod()
    {
        $stream = fopen($this->tmpname, 'w');
        $filter = new FilterProtocolTest_MockFilterClass();
        Serial_Core_FilterProtocol::attach($stream, $filter); 
        fwrite($stream, $this->data);
        fclose($stream);
        $filtered = file_get_contents($this->tmpname);
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

