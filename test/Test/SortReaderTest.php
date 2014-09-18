<?php
/**
 * Unit testing for the SortReader class.
 *
 */
class Test_SortReaderTest extends Test_SortTest
{   
    /**
     * Test the iterator interface.
     */
    public function testIter()
    {
        $reader = new ArrayIterator($this->allRandom);
        $reader = new Serial_Core_SortReader($reader, 'num');
        $this->assertEquals($this->numSorted, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface for a multi-key sort.
     */
    public function testIterMultiKey()
    {
        $reader = new ArrayIterator($this->allRandom);
        $reader = new Serial_Core_SortReader($reader, array('mod', 'num'));
        $this->assertEquals($this->modSorted, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface for a custom key function.
     */
    public function testIterCustomKey()
    {
        $reader = new ArrayIterator($this->allRandom);
        $keyfunc = 'Test_SortReaderTest::keyFunc';
        $reader = new Serial_Core_SortReader($reader, $keyfunc);
        $this->assertEquals($this->modSorted, iterator_to_array($reader));
        return;
    }
    
    /**
     * Sample key function for testing.
     */
    public static function keyFunc($record)
    {
        $keyvals = array();
        foreach (array('mod', 'num') as $key) {
            $keyvals[$key] = $record[$key];
        }
        return $keyvals;
    } 
}
