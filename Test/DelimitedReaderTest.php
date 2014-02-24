<?php
/**
 * Unit testing for the DelimitedReader class.
 *
 */
class Test_DelimitedReaderTest extends Test_TabularReaderTest
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
            new Serial_Core_StringField('x', 0),
            new Serial_Core_StringField('y', 1),
        );
        $fields = array(
            new Serial_Core_IntField('int', 0),
            new Serial_Core_ArrayField('arr', array(1, null), $array_fields), 
        );
        $this->data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->reader = new Serial_Core_DelimitedReader($this->stream, $fields, ',');
        return;
    }
}
