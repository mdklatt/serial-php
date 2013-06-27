<?php
/**
 * Unit tests for writer.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';
require_once 'writer.php';

/**
 * Unit testing for tabular data writer classes.
 *
 */
abstract class _TabularWriterTest extends PHPUnit_Framework_TestCase
{
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


/**
 * Unit testing for the DelimitedWriter class.
 *
 */
class DelimitedWriterTest extends _TabularWriterTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $array_fields = array(
            'x' => array(0, new StringType()),
            'y' => array(1, new StringType()),
        );
        $fields = array(
            'int' => array(0, new IntType()),
            'arr' => array(array(1, null), new ArrayType($array_fields)), 
        );
        parent::setUp();
        $this->writer = new DelimitedWriter($this->stream, $fields, ',', 'X');
        $this->data = '123,abc,defX456,ghi,jklX';
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function test_filter()
    {
        $this->writer->filter('_TabularWriterTest::reject_filter', 
                              '_TabularWriterTest::modify_filter');
        $this->data = '912,ghi,jklX';
        $this->test_dump();
        return;
    }
}


/**
 * Unit testing for the FixedWidthWriter class.
 *
 */
class FixedWidthWriterTest extends _TabularWriterTest
{   
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $array_fields = array(
            'x' => array(array(0, 3), new StringType('%3s')),
            'y' => array(array(3, 3), new StringType('%3s')),
        );
        $fields = array(
            'int' => array(array(0, 3), new IntType('%3d')),
            'arr' => array(array(3, null), new ArrayType($array_fields)), 
        );
        parent::setUp();
        $this->writer = new FixedWidthWriter($this->stream, $fields, 'X');        
        $this->data = '123abcdefX456ghijklX';
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function test_filter()
    {
        $this->writer->filter('_TabularWriterTest::reject_filter', 
                               '_TabularWriterTest::modify_filter');
        $this->data = '912ghijklX';
        $this->test_dump();        
        return;
    }
}
