<?php
/**
 * Unit testing for the AggregateWriter class.
 *
 */
class Test_AggregateWriterTest extends Test_AggregateTest
{
    private $writer;

    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->writer = new Test_MockWriter;
        return;
    }
    
    /**
     * Test the write() and close() methods.
     */
    public function testWrite()
    {
        $reduced = array(
            array('str' => 'abc', 'int' => 5, 'float' => 3.),
            array('str' => 'def', 'int' => 3, 'float' => 4.),
        );
        $writer = new Serial_Core_AggregateWriter($this->writer, 'str');
        $writer->reduce(
            array(new Serial_Core_CallbackReduction('array_sum', 'int'), '__invoke'),
            array(new Serial_Core_CallbackReduction('max', 'float'), '__invoke')
        );
        foreach ($this->records as $record) {
            $writer->write($record);
        }
        $writer->close();
        $writer->close();  // test that multiple calls are a no-op
        $this->assertEquals($reduced, $this->writer->output);
        return;
    }

    /**
     * Test the write() method with multi-key grouping.
     */
    public function testWriteMultiKey()
    {
        $reduced = array(
            array('str' => 'abc', 'int' => 1, 'float' => 2.),
            array('str' => 'abc', 'int' => 3, 'float' => 3.),
            array('str' => 'def', 'int' => 3, 'float' => 4.),
        );
        $writer = new Serial_Core_AggregateWriter($this->writer, array('str', 'int'));
        $writer->reduce(
            array(new Serial_Core_CallbackReduction('max', 'float'), '__invoke')
        );
        $writer->dump($this->records);  // dump() calls close()
        $this->assertEquals($reduced, $this->writer->output);
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
        $keyfunc = 'Test_AggregateWriterTest::key';
        $writer = new Serial_Core_AggregateWriter($this->writer, $keyfunc);
        $writer->reduce(
            array(new Serial_Core_CallbackReduction('max', 'float'), '__invoke')
        );
        $writer->dump($this->records);  // dump() calls close()
        $this->assertEquals($reduced, $this->writer->output);
        return;
    }
}
