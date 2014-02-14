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
            'x' => array(array(0, 3), new Serial_Core_StringType('%3s')),
            'y' => array(array(3, 3), new Serial_Core_StringType('%3s')),
        );
        $fields = array(
            'int' => array(array(0, 3), new Serial_Core_IntType('%3d')),
            'arr' => array(array(3, null), new Serial_Core_ArrayType($array_fields)), 
        );
        $this->data = "123abcdef\n456ghijkl\n";
        parent::setUp();
        $this->reader = new Serial_Core_FixedWidthReader($this->stream, $fields);        
        return;
    }
}
