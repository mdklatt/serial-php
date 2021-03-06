<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the AggregateReader class.
 *
 */
class AggregateReaderTest extends AggregateTest
{
    private $reader;
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->reader = new \ArrayIterator($this->records);
        return;
    }
    
    /**
     * Test the iterator interface.
     */
    public function testIter()
    {
        $reduced = array(
            array('str' => 'abc', 'int' => 5, 'float' => 3.),
            array('str' => 'def', 'int' => 3, 'float' => 4.),
        );
        $reader = new Core\AggregateReader($this->reader, 'str');
        $reader->reduce(
            array(new Core\CallbackReduction('array_sum', 'int'), '__invoke'),
            array(new Core\CallbackReduction('max', 'float'), '__invoke')
        );
        $this->assertEquals($reduced, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface with multi-key grouping.
     */
    public function testIterMultiKey()
    {
        $reduced = array(
            array('str' => 'abc', 'int' => 1, 'float' => 2.),
            array('str' => 'abc', 'int' => 3, 'float' => 3.),
            array('str' => 'def', 'int' => 3, 'float' => 4.),
        );
        $reader = new Core\AggregateReader($this->reader, array('str', 'int'));
        $reader->reduce(
            array(new Core\CallbackReduction('max', 'float'), '__invoke')
        );
        $this->assertEquals($reduced, iterator_to_array($reader));
        return;
    }

    /**
     * Test the iterator interface with a custom key function.
     */
    public function testIterCustomKey()
    {
        $reduced = array(
            array('KEY' => 'ABC', 'float' => 3.),
            array('KEY' => 'DEF', 'float' => 4.),
        );
        $keyfunc = __NAMESPACE__.'\AggregateReaderTest::key';
        $reader = new Core\AggregateReader($this->reader, $keyfunc);
        $reader->reduce(
            array(new Core\CallbackReduction('max', 'float'), '__invoke')
        );
        $this->assertEquals($reduced, iterator_to_array($reader));
        return;
    }
}
