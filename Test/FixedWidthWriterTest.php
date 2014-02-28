<?php
/**
 * Unit testing for the FixedWidthWriter class.
 *
 */
class Test_FixedWidthWriterTest extends Test_TabularWriterTest
{   
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->fields = array(
            new Serial_Core_IntField('int', array(0, 4), '%4d'),
            new Serial_Core_ArrayField('arr', array(4, null), array(
                new Serial_Core_StringField('x', array(0, 4), '%4s'),
                new Serial_Core_StringField('y', array(4, 4), '%4s'),                
            )), 
        );
        parent::setUp();
        $this->writer = new Serial_Core_FixedWidthWriter($this->stream, 
                        $this->fields, self::ENDL);        
        $this->data = ' 123 abc defX 456 ghi jklX';
        return;
    }

    /**
     * Test the open() method.
     * 
     */
    public function testOpen()
    {
        // TODO: This only tests an open stream; also need to test with a file
        // path.
        $this->writer = Serial_Core_FixedWidthWriter::open($this->stream, 
                        $this->fields, self::ENDL);
        $this->testWrite();
        unset($this->writer);  // close $this->stream
        $this->assertFalse(is_resource($this->stream));
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function testFilter()
    {
        $this->writer->filter('Test_TabularWriterTest::rejectFilter', 
                              'Test_TabularWriterTest::modifyFilter');
        $this->data = ' 912 ghi jklX';
        $this->testDump();        
        return;
    }
}
