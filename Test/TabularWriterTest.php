<?php
/**
 * Base class for tabular writer class unit testing.
 *
 */
abstract class Test_TabularWriterTest extends PHPUnit_Framework_TestCase
{
    const ENDL = 'X';
    
    static public function reject_filter($record)
    {
        return $record['int'] != 123 ? $record : null;
    }

    static public function modify_filter($record)
    {
        $record['int'] *= 2;
        return $record;
    }

    protected $data;
    protected $records;
    protected $stream;
    protected $fields;
    protected $writer;
    
    protected function setUp()
    {
        $this->records = array(
            array(
                'int' => 123,
                'arr' => array(array('x' => 'abc', 'y' => 'def')), 
            ),
            array(
                'int' => 456,
                'arr' => array(array('x' => 'ghi', 'y' => 'jkl')), 
            ),
        );
        $this->stream = fopen('php://memory', 'rw');
        return;
    }
    
    public function test_write()
    {
        foreach ($this->records as $record) {
            $this->writer->write($record);
        }
        rewind($this->stream);
        $this->assertEquals($this->data, stream_get_contents($this->stream));
        return;
    }

    public function test_dump()
    {
        $this->writer->dump($this->records);
        rewind($this->stream);
        $this->assertEquals($this->data, stream_get_contents($this->stream));
        return;
    }
}
