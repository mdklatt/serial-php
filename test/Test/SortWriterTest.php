<?php
/**
 * Unit testing for the SortReader class.
 *
 */
class Test_SortWriterTest extends Test_SortTest
{   
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
        $writer = new Serial_Core_SortWriter($this->writer, 'num');
        foreach ($this->allRandom as $record) {
            $writer->write($record);
        }
        $writer->close();
        $writer->close();  // redundant calls should be a no-op
        $this->assertEquals($this->numSorted, $this->writer->output);
        return;
    }

    /**
     * Test the write() method for a multi-key sort.
     */
    public function testWriteMultiKey()
    {
        $writer = new Serial_Core_SortWriter($this->writer, array('mod', 'num'));
        foreach ($this->allRandom as $record) {
            $writer->write($record);
        }
        $writer->close();
        $this->assertEquals($this->modSorted, $this->writer->output);
        return;
    }

    /**
     * Test the write() method for a custom key sort.
     */
    public function testWriteCustomKey()
    {
        $keyfunc = 'Test_SortWriterTest::keyFunc';
        $writer = new Serial_Core_SortWriter($this->writer, $keyfunc);
        foreach ($this->allRandom as $record) {
            $writer->write($record);
        }
        $writer->close();
        $this->assertEquals($this->modSorted, $this->writer->output);
        return;
    }

    /**
     * Test the write() method with grouping.
     */
    public function testWriteGroup()
    {
        $writer = new Serial_Core_SortWriter($this->writer, 'num', 'mod');
        foreach ($this->numRandom as $record) {
            $writer->write($record);
        }
        $writer->close();
        $this->assertEquals($this->modSorted, $this->writer->output);
        return;
    }

    /**
     * Test the dump() method.
     */
    public function testDump()
    {
        $writer = new Serial_Core_SortWriter($this->writer, 'num');
        $writer->dump($this->allRandom);
        $this->assertEquals($this->numSorted, $this->writer->output);
        return;
    }
}
