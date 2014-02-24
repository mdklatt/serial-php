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
            new Serial_Core_StringField('x', array(0, 3), '%3s'),
            new Serial_Core_StringField('y', array(3, 3), '%3s'),
        );
        $fields = array(
            new Serial_Core_IntField('int', array(0, 3), '%3d'),
            new Serial_Core_ArrayField('arr', array(3, null), $array_fields), 
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
