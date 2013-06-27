<?php
/**
 * Unit tests for reader.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';
require_once 'reader.php';

abstract class _TabularReaderTest extends PHPUnit_Framework_TestCase
{
    static public function reject_filter($record)
    {
        return $record["int"] != 123 ? $record : null;
    }
    
    static public function modify_filter($record)
    {
        $record["int"] *= 2;
        return $record;
    }

    static public function stop_filter($record)
    {
        return $record["int"] != 456 ? $record : DelimitedReader::STOP_ITERATION;
    }

    protected $data;
    protected $records;
    protected $stream;
    protected $reader;
    
    protected function setUp()
    {
        $this->stream = fopen("php://memory", "rw");
        fwrite($this->stream, $this->data);
        rewind($this->stream);
        $this->records = array(
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
        $records = iterator_to_array($this->reader, false);
        $this->assertEquals($this->records, $records);
        return;
    }
    
    public function test_filter()
    {
        $this->records = array_slice($this->records, 1);
        $this->records[0]["int"] = 912;
        $this->reader->filter('_TabularReaderTest::reject_filter', 
                              '_TabularReaderTest::modify_filter');
        $this->test_iter();
        return;
    }
    
    public function test_filter_stop()
    {
        $this->records = array_slice($this->records, 0, 1);
        $this->reader->filter('_TabularReaderTest::stop_filter');
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
        $this->data = "123abcdef\n456ghijkl\n";
        parent::setUp();
        $this->reader = new FixedWidthReader($this->stream, $fields);        
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
        $this->data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->reader = new DelimitedReader($this->stream, $fields, ',');
        return;
    }
}
