<?php
/**
 * Unit testing for the DelimitedReader class.
 *
 */
class Test_DelimitedReaderTest extends Test_TabularReaderTest
{
    const DELIM = ',';
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->fields = array(
            new Serial_Core_IntField('int', 0),
            new Serial_Core_ArrayField('arr', array(1, null), array(
                new Serial_Core_StringField('x', 0),
                new Serial_Core_StringField('y', 1),
            )), 
        );
        $this->data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->reader = new Serial_Core_DelimitedReader($this->stream, 
                            $this->fields, self::DELIM);
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
        $this->reader = Serial_Core_DelimitedReader::open($this->stream, 
                        $this->fields, self::DELIM);
        $this->testIter();
        unset($this->reader);  // close $this->stream
        $this->assertFalse(is_resource($this->stream));
        return;
    }

    /**
     * Test the open() method for an invalid stream or path.
     * 
     */
    public function testOpenFail()
    {
        $this->setExpectedException('RuntimeException');
        Serial_Core_DelimitedReader::open(null, $this->fields, self::DELIM);
        return;
    }
}
