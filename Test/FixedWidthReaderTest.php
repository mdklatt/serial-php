<?php
/**
 * Unit testing for the FixedWidthReader class.
 *
 */
class Test_FixedWidthReaderTest extends Test_TabularReaderTest
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
            new Serial_Core_IntField('int', array(0, 4), '%3d'),
            new Serial_Core_ArrayField('arr', array(4, null), array(
                new Serial_Core_StringField('x', array(0, 4), '%3s'),
                new Serial_Core_StringField('y', array(4, 4), '%3s'),                
            )), 
        );
        $this->data = " 123 abc def\n 456 ghi jkl\n";
        parent::setUp();
        $this->reader = new Serial_Core_FixedWidthReader($this->stream, 
                        $this->fields);        
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
        $this->reader = Serial_Core_FixedWidthReader::open($this->stream, 
                        $this->fields);
        $this->testIter();
        unset($this->reader);  // close $this->stream
        $this->assertFalse(is_resource($this->stream));
        return;
    }
}
