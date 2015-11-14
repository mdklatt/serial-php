<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Base class for tabular reader class unit testing.
 *
 */
abstract class TabularReaderTest extends \PHPUnit_Framework_TestCase
{
    static public function rejectFilter($record)
    {
        return $record['int'] != 123 ? $record : null;
    }
    
    static public function modifyFilter($record)
    {
        $record['int'] *= 2;
        return $record;
    }

    static public function stopFilter($record)
    {
        if ($record['int'] == 456) {
            throw new Core\StopIteration();
        }
        return $record;
    }

    protected $data;
    protected $records;
    protected $stream;
    protected $fields;
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
    public function testIter()
    {
        $records = iterator_to_array($this->reader);
        $this->assertEquals($this->records, $records);
        return;
    }
    
    /**
     * Test the filter() method.
     *
     */
    public function testFilter()
    {
        $this->records = array_slice($this->records, 1);
        $this->records[0]['int'] = 912;
        $this->reader->filter(
            __NAMESPACE__.'\TabularReaderTest::rejectFilter', 
            __NAMESPACE__.'\TabularReaderTest::modifyFilter');
        $this->testIter();
        return;
    }
    
    /**
     * Test the filter() method with a StopIteration exception.
     *
     */
    public function testFilterStop()
    {
        $this->records = array_slice($this->records, 0, 1);
        $this->reader->filter(
            __NAMESPACE__.'\TabularReaderTest::stopFilter');
        $this->testIter();
        return;        
    }
}
