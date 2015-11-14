<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the SortReader class.
 *
 */
class SortReaderTest extends SortTest
{   
    /**
     * Test the iterator interface.
     */
    public function testIter()
    {
        $reader = new \ArrayIterator($this->allRandom);
        $reader = new Core\SortReader($reader, 'num');
        $this->assertEquals($this->numSorted, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface for a multi-key sort.
     */
    public function testIterMultiKey()
    {
        $reader = new \ArrayIterator($this->allRandom);
        $reader = new Core\SortReader($reader, array('mod', 'num'));
        $this->assertEquals($this->modSorted, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface for a custom key sort.
     */
    public function testIterCustomKey()
    {
        $reader = new \ArrayIterator($this->allRandom);
        $keyfunc = __NAMESPACE__.'\SortReaderTest::keyFunc';
        $reader = new Core\SortReader($reader, $keyfunc);
        $this->assertEquals($this->modSorted, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface with grouping.
     */
    public function testIterGroup()
    {
        $reader = new \ArrayIterator($this->numRandom);
        $reader = new Core\SortReader($reader, 'num', 'mod');
        $this->assertEquals($this->modSorted, iterator_to_array($reader));
        return;
    }
}
