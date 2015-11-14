<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the DelimitedWriter class.
 *
 */
class DelimitedWriterTest extends TabularWriterTest
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
            new Core\IntField('int', 0),
            new Core\ArrayField('arr', array(1, null), array(
                new Core\StringField('x', 0),
                new Core\StringField('y', 1),                
            )),
        );
        parent::setUp();
        $this->writer = new Core\DelimitedWriter($this->stream, 
                        $this->fields, self::DELIM, self::ENDL);
        $this->data = '123,abc,defX456,ghi,jklX';
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
        $this->writer = Core\DelimitedWriter::open($this->stream, 
                        $this->fields, self::DELIM, self::ENDL);
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
        Core\DelimitedWriter::open(null, $this->fields, self::DELIM);
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
        $this->data = '912,ghi,jklX';
        $this->testDump();
        return;
    }
}
