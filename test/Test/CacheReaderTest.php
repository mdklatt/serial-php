<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the CacheReader class.
 *
 */
class CacheReaderTest extends \PHPUnit_Framework_TestCase
{ 
    protected $reader;
    protected $records;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->records = array(
            // Need at least 3 records.
            array('str' => 'abc'), 
            array('str' => 'def'), 
            array('str' => 'ghi'),
        );
        $this->reader = new \ArrayIterator($this->records);
        return;
    }
    
    /**
     * Test the iterator interface.
     */
    public function testIter()
    {
        $reader = new Core\CacheReader($this->reader);
        $this->assertEquals($this->records, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface for emtpy input.
     */
    public function testIterEmpty()
    {
        $reader = new Core\CacheReader(new \ArrayIterator());
        $this->assertEquals(array(), iterator_to_array($reader));
        return;
    }

    /**
     * Test the reverse() method.
     */
    public function testReverse()
    {
        $reader = new Core\CacheReader($this->reader);
        iterator_to_array($reader);  // exhaust reader
        $reader->reverse();  // does not reset Iterator key values
        $reader->reverse();  // should be a no-op
        $records = array_values(iterator_to_array($reader));  // reset keys
        $this->assertEquals($this->records, $records);
        return;
    }

    /**
     * Test the reverse() method with a count argument.
     */
    public function testReverseCount()
    {
        $count = 1;
        $reader = new Core\CacheReader($this->reader);
        iterator_to_array($reader);  // exhaust reader
        $reader->reverse($count);  // does not reset Iterator key values
        $records = array_values(iterator_to_array($reader));  // reset keys
        $this->assertEquals(array_slice($this->records, -$count), $records);
        return;
    }

    /**
     * Test the reverse() method with a maxlen value.
     */
    public function testReverseMaxlen()
    {
        $maxlen = count($this->records) - 1;
        $reader = new Core\CacheReader($this->reader, $maxlen);
        iterator_to_array($reader);  // exhaust reader
        $reader->reverse($maxlen+1);  // attempt to reverse beyond cache
        $records = array_values(iterator_to_array($reader));  // reset keys
        $this->assertEquals(array_slice($this->records, -$maxlen), $records);
        return;
    }
}
