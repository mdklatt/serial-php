<?php
/**
 * Unit tests for reader.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';
require_once 'reader.php';

function reject_filter($record)
{
    return $record["int"] != 123 ? $record : null;
}


function modify_filter($record)
{
    $record["int"] *= 10;
    return $record;
}


function stop_filter($record)
{
    return $record["int"] != 456 ? $record : DelimitedReader::STOP_ITERATION;
}


abstract class _TabularReaderTest extends PHPUnit_Framework_TestCase
{
    protected $_data;
    protected $_records;
    protected $_stream;
    protected $_reader;
    
    protected function setUp()
    {
        $this->_stream = fopen("php://memory", "rw");
        fwrite($this->_stream, $this->_data);
        rewind($this->_stream);
        $this->_records = array(
            array(
                "arr" => array(array("x" => "abc", "y" => "def")), 
                "int" => 123),
            array(
                "arr" => array(array("x" => "ghi", "y" => "jkl")), 
                "int" => 456),
        );
        return;
    }
    
    public function test_iter()
    {
        $records = iterator_to_array($this->_reader, false);
        $this->assertEquals($this->_records, $records);
        return;
    }
    
    public function test_filter()
    {
        $this->_records = array_slice($this->_records, 1);
        $this->_records[0]["int"] = 4560;
        $this->_reader->filter('reject_filter');
        $this->_reader->filter('modify_filter');
        $this->test_iter();
        return;
    }
    
    public function test_filter_stop()
    {
        $this->_records = array_slice($this->_records, 0, 1);
        $this->_reader->filter('stop_filter');
        $this->test_iter();
        return;        
    }
}


class FixedWidthReaderTest extends _TabularReaderTest
{   
    protected function setUp()
    {
        $array_fields = array(
            "x" => array(array(0, 3), new StringType("%3s")),
            "y" => array(array(3, 3), new StringType("%3s")),
        );
        $fields = array(
            "arr" => array(array(0, 6), new ArrayType($array_fields)), 
            "int" => array(array(6, 3), new IntType("%3d")),
        );
        $this->_data = "abcdef123\nghijkl456\n";
        parent::setUp();
        $this->_reader = new FixedWidthReader($this->_stream, $fields);        
        return;
    }
}


class DelimitedReaderTest extends _TabularReaderTest
{
    protected function setUp()
    {
        $array_fields = array(
            "x" => array(0, new StringType()),
            "y" => array(1, new StringType()),
        );
        $fields = array(
            "arr" => array(array(0, 2), new ArrayType($array_fields)), 
            "int" => array(2, new IntType()),
        );
        $this->_data = "abc, def, 123\nghi, jkl, 456\n";
        parent::setUp();
        $this->_reader = new DelimitedReader($this->_stream, $fields, ',');
        return;
    }
}