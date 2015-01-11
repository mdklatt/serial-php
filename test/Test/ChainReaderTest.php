<?php
/**
 * Unit testing for the ChainReader class.
 *
 */
class Test_ChainReaderTest extends PHPUnit_Framework_TestCase
{
    
    private $streams;
    private $records;
    private $fields;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $data = array(" 123 abc\n 456 def\n", " 123 ABC\n 456 DEF\n");
        $this->fields = array(
            new Serial_Core_IntField('int', array(0, 4)),
            new Serial_Core_StringField('str', array(4, 4)),
        );
        $this->streams = array();
        foreach ($data as $str) {
            $stream = fopen('php://memory', 'rw');
            fwrite($stream, $str);
            rewind($stream);
            $this->streams[] = $stream;
        }
        $this->records = array(
            array('int' => 123, 'str' => 'abc'),
            array('int' => 456, 'str' => 'def'),
            array('int' => 123, 'str' => 'ABC'),
            array('int' => 456, 'str' => 'DEF'),
        );
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
            @fclose($stream);
        }
        return;
    }

    
    /**
     * Test the open() method.
     *
     */
    public function testOpen()
    {
        // Due to a circular reference, calling unset() on a ChainReader is not
        // enough to trigger its destructor, so call it explicitly.
        $reader = Serial_Core_ChainReader::open($this->streams,
            'Serial_Core_FixedWidthReader', $this->fields);
        $reader->rewind();
        $this->assertEquals($this->records[0], $reader->current());
        $reader->__destruct();  // should call close()
        foreach ($this->streams as $stream) {
            // Make sure each stream was closed.
            $this->assertFalse(is_resource($stream));
        }
        return;
    }

    /**
     * Test the iterator protocol.
     *
     */
    public function testIter()
    {
        $reader = new Serial_Core_ChainReader($this->streams,
            'Serial_Core_FixedWidthReader', $this->fields);
        $this->assertEquals($this->records, iterator_to_array($reader));
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
        $reader = new Serial_Core_ChainReader(array(),
            'Serial_Core_FixedWidthReader', $this->fields);
        $this->assertEquals(array(), iterator_to_array($reader));
        return;
    }
}
