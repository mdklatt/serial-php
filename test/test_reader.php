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
    return $record["str"] != "abc" ? $record : null;
}


function modify_filter($record)
{
    $record["str"] = strtoupper($record["str"]);
    return $record["str"] != "abc" ? $record : null;
}


function stop_filter($record)
{
    return $record["str"] != "def" ? $record : DelimitedReader::STOP_ITERATION;
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
            array("str" => "abc", "int" => 123),
            array("str" => "def", "int" => 456),
        );
        return;
    }
    
    public function test_iter()
    {
        $records = iterator_to_array($this->_reader, false);
        $this->assertEquals($this->_records, $records);
        return;
    }
    
    public function test_fields()
    {
        $this->assertEquals(array("str", "int"), $this->_reader->fields());
        return;
    }
    
    public function test_filter()
    {
        $this->_records = array(
            array("str" => "DEF", "int" => 456),
        );
        $this->_reader->filter('reject_filter');
        $this->_reader->filter('modify_filter');
        $records = iterator_to_array($this->_reader, false);
        $this->assertEquals($this->_records, $records);
        return;        
    }
    
    public function test_filter_stop()
    {
        $this->_records = array(
            array("str" => "abc", "int" => 123),
        );
        $this->_reader->filter('stop_filter');
        $records = iterator_to_array($this->_reader, false);
        $this->assertEquals($this->_records, $records);
        return;        
    }
}


class FixedWidthReaderTest extends _TabularReaderTest
{   
    protected function setUp()
    {
        $this->_data = "abc123\ndef456\n";
        parent::setUp();
        $fields = array(
            "str" => array(array(0, 3), new StringType()), 
            "int" => array(array(3, 3), new IntType()),
        );
        $this->_reader = new FixedWidthReader($this->_stream, $fields);
        return;
    }
}


class DelimitedReaderTest extends _TabularReaderTest
{
    protected function setUp()
    {
        $this->_data = "abc,123\ndef,456\n";
        parent::setUp();
        $fields = array(
            "str" => array(0, new StringType()), 
            "int" => array(1, new IntType()),
        );
        $this->_reader = new DelimitedReader($this->_stream, $fields, ',');
        return;
    }
}


