<?php
/**
 * Unit tests for stream classes.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */

/**
 * Unit tests for the FilterProtocol class.
 *
 */
class FilterProtocolTest extends PHPUnit_Framework_TestCase
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
        $lines = array("abc\n", "def\n", "ghi");  // test no trailing newline
        $this->stream = tmpfile();
        fwrite($this->stream, implode('', $lines));
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
     * Test stream filtering with a function.
     *
     */
    public function testAttachFunction()
    {
        Serial_Core_FilterProtocol::attach($this->stream, 'strtoupper', 
            STREAM_FILTER_READ);
        $filtered = stream_get_contents($this->stream);
        $this->assertEquals("ABC\nDEF\nGHI", $filtered);
        return;
    }

    /**
     * Test stream filtering with a class method.
     *
     */
    public function testAttachMethod()
    {
        
        $filter = new MockFilterClass();
        Serial_Core_FilterProtocol::attach($this->stream, $filter, STREAM_FILTER_READ);
        $filtered = stream_get_contents($this->stream);
        $this->assertEquals("nop\nqrs\ntuv", $filtered);
        return;
    }
}


class MockFilterClass
{
    /**
     * Execute the filter.
     *
     */
    public function __invoke($line)
    {
        return str_rot13($line);
    }
}
