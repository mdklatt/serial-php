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
        $array_fields = array(
            'x' => array(array(0, 3), new Serial_Core_StringType('%3s')),
            'y' => array(array(3, 3), new Serial_Core_StringType('%3s')),
        );
        $fields = array(
            'int' => array(array(0, 3), new Serial_Core_IntType('%3d')),
            'arr' => array(array(3, null), new Serial_Core_ArrayType($array_fields)), 
        );
        parent::setUp();
        $this->writer = new Serial_Core_FixedWidthWriter($this->stream, $fields, 'X');        
        $this->data = '123abcdefX456ghijklX';
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function test_filter()
    {
        $this->writer->filter('Test_TabularWriterTest::reject_filter', 
                              'Test_TabularWriterTest::modify_filter');
        $this->data = '912ghijklX';
        $this->test_dump();        
        return;
    }
}
