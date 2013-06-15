<?php
/**
 * Unit tests for writer.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';
require_once 'writer.php';


function writer_reject_filter($record)
{
    return $record["int"] != 123 ? $record : null;
}


function writer_modify_filter($record)
{
    $record["int"] *= 2;
    return $record;
}


abstract class _TabularWriterTest extends PHPUnit_Framework_TestCase
{
    protected $_data;
    protected $_records;
    protected $_stream;
    protected $_writer;
    
    protected function setUp()
    {
        $this->_records = array(
            array(
                "int" => 123,
                "arr" => array(array("x" => "abc", "y" => "def")), 
            ),
            array(
                "int" => 456,
                "arr" => array(array("x" => "ghi", "y" => "jkl")), 
            ),
        );
        $this->_stream = fopen("php://memory", "rw");
        return;
    }
    
    public function test_write()
    {
        foreach ($this->_records as $record) {
            $this->_writer->write($record);
        }
        rewind($this->_stream);
        $this->assertEquals($this->_data, stream_get_contents($this->_stream));
        return;
    }

    public function test_dump()
    {
        $this->_writer->dump($this->_records);
        rewind($this->_stream);
        $this->assertEquals($this->_data, stream_get_contents($this->_stream));
        return;
    }
    
}


class DelimitedWriterTest extends _TabularWriterTest
{
    protected function setUp()
    {
        $array_fields = array(
            "x" => array(0, new StringType()),
            "y" => array(1, new StringType()),
        );
        $fields = array(
            "int" => array(0, new IntType()),
            "arr" => array(array(1, null), new ArrayType($array_fields)), 
        );
        parent::setUp();
        $this->_writer = new DelimitedWriter($this->_stream, $fields, ',', 'X');
        $this->_data = "123,abc,defX456,ghi,jklX";
        return;
    }

    public function test_filter()
    {
        $this->_writer->filter('writer_reject_filter');
        $this->_writer->filter('writer_modify_filter');
        $this->_data = "912,ghi,jklX";
        $this->test_dump();
        return;
    }
}


class FixedWidthWriterTest extends _TabularWriterTest
{   
    protected function setUp()
    {
        $array_fields = array(
            "x" => array(array(0, 3), new StringType("%3s")),
            "y" => array(array(3, 3), new StringType("%3s")),
        );
        $fields = array(
            "int" => array(array(0, 3), new IntType("%3d")),
            "arr" => array(array(3, null), new ArrayType($array_fields)), 
        );
        parent::setUp();
        $this->_writer = new FixedWidthWriter($this->_stream, $fields, 'X');        
        $this->_data = "123abcdefX456ghijklX";
        return;
    }

    public function test_filter()
    {
        $this->_writer->filter('writer_reject_filter');
        $this->_writer->filter('writer_modify_filter');
        $this->_data = "912ghijklX";
        $this->test_dump();        
        return;
    }
}
