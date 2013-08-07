<?php
/**
 * Unit tests for reader.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */


/**
 * Unit testing for the tabular data reader classes.
 *
 */
abstract class TabularReaderTest extends PHPUnit_Framework_TestCase
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

    static public function stop_filter($record)
    {
        if ($record['int'] == 456) {
            throw new Serial_EofException();
        }
        return $record;
    }

    protected $data;
    protected $records;
    protected $stream;
    protected $reader;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->stream = fopen('php://memory', 'rw');
        fwrite($this->stream, $this->data);
        rewind($this->stream);
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
        return;
    }

    /**
     * Tear down the test fixture.
     *
     * This is called after every test is run.
     */
    protected function tearDown()
    {
        @fclose($this->stream);
        return;
    }
    
    /**
     * Test the iterator interface.
     *
     */
    public function test_iter()
    {
        $records = iterator_to_array($this->reader);
        $this->assertEquals($this->records, $records);
        return;
    }
    
    /**
     * Test the filter() method.
     *
     */
    public function test_filter()
    {
        $this->records = array_slice($this->records, 1);
        $this->records[0]['int'] = 912;
        $this->reader->filter('TabularReaderTest::reject_filter', 
                              'TabularReaderTest::modify_filter');
        $this->test_iter();
        return;
    }
    
    /**
     * Test the filter() method with STOP_ITERATION.
     *
     */
    public function test_filter_stop()
    {
        $this->records = array_slice($this->records, 0, 1);
        $this->reader->filter('TabularReaderTest::stop_filter');
        $this->test_iter();
        return;        
    }
}


class Serial_FixedWidthReaderTest extends TabularReaderTest
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
            'x' => array(array(0, 3), new Serial_StringType('%3s')),
            'y' => array(array(3, 3), new Serial_StringType('%3s')),
        );
        $fields = array(
            'int' => array(array(0, 3), new Serial_IntType('%3d')),
            'arr' => array(array(3, null), new Serial_ArrayType($array_fields)), 
        );
        $this->data = "123abcdef\n456ghijkl\n";
        parent::setUp();
        $this->reader = new Serial_FixedWidthReader($this->stream, $fields);        
        return;
    }
}


class Serial_DelimitedReaderTest extends TabularReaderTest
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
            'x' => array(0, new Serial_StringType()),
            'y' => array(1, new Serial_StringType()),
        );
        $fields = array(
            'int' => array(0, new Serial_IntType()),
            'arr' => array(array(1, null), new Serial_ArrayType($array_fields)), 
        );
        $this->data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->reader = new Serial_DelimitedReader($this->stream, $fields, ',');
        return;
    }
}
