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
            'x' => array(0, new Serial_Core_StringType()),
            'y' => array(1, new Serial_Core_StringType()),
        );
        $fields = array(
            'int' => array(0, new Serial_Core_IntType()),
            'arr' => array(array(1, null), new Serial_Core_ArrayType($array_fields)), 
        );
        $this->data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->reader = new Serial_Core_DelimitedReader($this->stream, $fields, ',');
        return;
    }
}
