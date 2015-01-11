<?php
/**
 * Unit testing for the StreamFilterManager class.
 *
 */
class Test_StreamFilterManagerTest extends PHPUnit_Framework_TestCase
{
    protected $data;
    protected $tmpname;
    private $filtered;
        
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
     * Test input stream filtering.
     *
     */
    public function testInput()
    {
        file_put_contents($this->tmpname, $this->data);
        $stream = fopen($this->tmpname, 'r');
        Serial_Core_StreamFilterManager::attach($stream, 'strtoupper'); 
        $filtered = stream_get_contents($stream);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }

    /**
     * Test output stream filtering.
     *
     */
    public function testOutput()
    {
        $stream = fopen($this->tmpname, 'w');
        Serial_Core_StreamFilterManager::attach($stream, 'strtoupper'); 
        fwrite($stream, $this->data);
        fclose($stream);
        $filtered = file_get_contents($this->tmpname);
        $this->assertEquals($this->filtered, $filtered);
        return;
    }
}

