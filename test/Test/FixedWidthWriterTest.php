<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the FixedWidthWriter class.
 *
 */
class FixedWidthWriterTest extends TabularWriterTest
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
            new Core\IntField('int', array(0, 4), '%4d'),
            new Core\ArrayField('arr', array(4, null), array(
                new Core\StringField('x', array(0, 4), '%4s'),
                new Core\StringField('y', array(4, 4), '%4s'),                
            )), 
        );
        parent::setUp();
        $this->writer = new Core\FixedWidthWriter($this->stream, 
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
        $this->writer = Core\FixedWidthWriter::open($this->stream, 
                        $this->fields, self::ENDL);
        $this->testWrite();
        unset($this->writer);  // close $this->stream
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
        Core\FixedWidthWriter::open(null, $this->fields);
        return;
    }

    /**
     * Test the filter() method.
     *
     */
    public function testFilter()
    {
        $this->writer->filter(
            __NAMESPACE__.'\TabularWriterTest::rejectFilter', 
            __NAMESPACE__.'\TabularWriterTest::modifyFilter');
        $this->data = ' 912 ghi jklX';
        $this->testDump();        
        return;
    }
}
