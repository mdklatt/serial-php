<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Base class for tabular writer class unit testing.
 *
 */
abstract class TabularWriterTest extends \PHPUnit_Framework_TestCase
{
    const ENDL = 'X';
    
    static public function rejectFilter($record)
    {
        return $record['int'] != 123 ? $record : null;
    }

    static public function modifyFilter($record)
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
    
    public function testWrite()
    {
        foreach ($this->records as $record) {
            $this->writer->write($record);
        }
        rewind($this->stream);
        $this->assertEquals($this->data, stream_get_contents($this->stream));
        return;
    }

    public function testDump()
    {
        $this->writer->dump($this->records);
        rewind($this->stream);
        $this->assertEquals($this->data, stream_get_contents($this->stream));
        return;
    }
}
