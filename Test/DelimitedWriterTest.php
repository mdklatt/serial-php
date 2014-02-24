<?php
/**
 * Unit testing for the DelimitedWriter class.
 *
 */
class Test_DelimitedWriterTest extends Test_TabularWriterTest
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
        parent::setUp();
        $this->writer = new Serial_Core_DelimitedWriter($this->stream, $fields, ',', 'X');
        $this->data = '123,abc,defX456,ghi,jklX';
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
        $this->data = '912,ghi,jklX';
        $this->test_dump();
        return;
    }
}
