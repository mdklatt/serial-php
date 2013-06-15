<?php
/**
 * Unit tests for reader.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';
require_once 'reader.php';

function reader_reject_filter($record)
{
    return $record["int"] != 123 ? $record : null;
}


function reader_modify_filter($record)
{
    $record["int"] *= 2;
    return $record;
}


function reader_stop_filter($record)
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
                "int" => 123,
                "arr" => array(array("x" => "abc", "y" => "def")),
            ), 
            array(
                "int" => 456,
                "arr" => array(array("x" => "ghi", "y" => "jkl")),
            ), 
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
        $this->_records[0]["int"] = 912;
        $this->_reader->filter('reader_reject_filter');
        $this->_reader->filter('reader_modify_filter');
        $this->test_iter();
        return;
    }
    
    public function test_filter_stop()
    {
        $this->_records = array_slice($this->_records, 0, 1);
        $this->_reader->filter('reader_stop_filter');
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
            "int" => array(array(0, 3), new IntType("%3d")),
            "arr" => array(array(3, null), new ArrayType($array_fields)), 
        );
        $this->_data = "123abcdef\n456ghijkl\n";
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
            "int" => array(0, new IntType()),
            "arr" => array(array(1, null), new ArrayType($array_fields)), 
        );
        $this->_data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->_reader = new DelimitedReader($this->_stream, $fields, ',');
        return;
    }
}
