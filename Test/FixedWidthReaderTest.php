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
        $array_fields = array(
            new Serial_Core_StringField('x', array(0, 3), '%3s'),
            new Serial_Core_StringField('y', array(3, 3), '%3s'),
        );
        $fields = array(
            new Serial_Core_IntField('int', array(0, 3), '%3d'),
            new Serial_Core_ArrayField('arr', array(3, null), $array_fields), 
        );
        $this->data = "123abcdef\n456ghijkl\n";
        parent::setUp();
        $this->reader = new Serial_Core_FixedWidthReader($this->stream, $fields);        
        return;
    }
}
