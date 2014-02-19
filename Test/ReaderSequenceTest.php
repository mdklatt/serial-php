<?php
/**
 * Unit testing for the ReaderSequence class.
 *
 */
class Test_ReaderSequenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Create a new reader.
     * 
     */
    public static function reader($stream)
    {
        $fields = array(
            'x' => array(0, new Serial_Core_StringType()),
            'y' => array(1, new Serial_Core_StringType()),
        );
        return new Serial_Core_DelimitedReader($stream, $fields, ',');
    }
    
    private $streams;
    private $records;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $data = "abc, def\nghi, jkl\n";
        $stream1 = fopen('php://memory', 'rw');
        fwrite($stream1, $data);
        fseek($stream1, 0);
        $stream2 = fopen('php://memory', 'rw');
        fwrite($stream2, strtoupper($data));
        fseek($stream2, 0);
        $this->streams = array($stream1, $stream2);
        $this->records = array(
            array('x' => 'abc', 'y' => 'def'),
            array('x' => 'ghi', 'y' => 'jkl'),
            array('x' => 'ABC', 'y' => 'DEF'),
            array('x' => 'GHI', 'y' => 'JKL'),
        );
    }
    
    /**
     * Test the iterator protocol.
     *
     */
    public function testIter()
    {
        $reader = 'Test_ReaderSequenceTest::reader';
        $sequence = new Serial_Core_ReaderSequence($reader, $this->streams);
        $this->assertEquals($this->records, iterator_to_array($sequence));
        foreach ($this->streams as $stream) {
            // Make sure each stream was closed.
            $this->assertFalse(is_resource($stream));
        }
        return;
    }
    
    /**
     * Tear down the test fixture.
     *
     * This is called after every test is run.
     */
    protected function tearDown()
    {  
        foreach ($this->streams as $stream) {
            @fclose($this->stream);            
        }
        return;
    }
}