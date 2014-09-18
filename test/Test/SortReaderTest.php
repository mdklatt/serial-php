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
     * Test the iterator interface for a custom key sort.
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
     * Test the iterator interface with grouping.
     */
    public function testIterGroup()
    {
        $reader = new ArrayIterator($this->numRandom);
        $reader = new Serial_Core_SortReader($reader, 'num', 'mod');
        $this->assertEquals($this->modSorted, iterator_to_array($reader));
        return;
    }
}
