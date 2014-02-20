<?php
/**
 * Base class for tabular reader class unit testing.
 *
 */
abstract class Test_TabularReaderTest extends PHPUnit_Framework_TestCase
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
            throw new Serial_Core_StopIteration();
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
        $this->reader->filter('Test_TabularReaderTest::reject_filter', 
                              'Test_TabularReaderTest::modify_filter');
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
        $this->reader->filter('Test_TabularReaderTest::stop_filter');
        $this->test_iter();
        return;        
    }
}
