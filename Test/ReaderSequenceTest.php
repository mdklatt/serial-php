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
            new Serial_Core_StringField('x', 0),
            new Serial_Core_StringField('y', 1),
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
        rewind($stream1);
        $stream2 = fopen('php://memory', 'rw');
        fwrite($stream2, strtoupper($data));
        rewind($stream2);
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
     * Test the iterator protocol for an empty input sequence.
     *
     */
    public function testIterEmpty()
    {
        $reader = 'Test_ReaderSequenceTest::reader';
        $sequence = new Serial_Core_ReaderSequence($reader);
        $this->assertEquals(array(), iterator_to_array($sequence));
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
