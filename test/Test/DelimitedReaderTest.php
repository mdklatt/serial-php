<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the DelimitedReader class.
 *
 */
class DelimitedReaderTest extends TabularReaderTest
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
        $this->data = "123, abc, def\n456, ghi, jkl\n";
        parent::setUp();
        $this->reader = new Core\DelimitedReader($this->stream, $this->fields, self::DELIM);
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
        $this->reader = Core\DelimitedReader::open($this->stream, $this->fields, self::DELIM);
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
        Core\DelimitedReader::open(null, $this->fields, self::DELIM);
        return;
    }
}
