<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the FixedWidthReader class.
 *
 */
class FixedWidthReaderTest extends TabularReaderTest
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
            new Core\IntField('int', array(0, 4), '%3d'),
            new Core\ArrayField('arr', array(4, null), array(
                new Core\StringField('x', array(0, 4), '%3s'),
                new Core\StringField('y', array(4, 4), '%3s'),                
            )), 
        );
        $this->data = " 123 abc def\n 456 ghi jkl\n";
        parent::setUp();
        $this->reader = new Core\FixedWidthReader($this->stream, $this->fields);        
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
        $this->reader = Core\FixedWidthReader::open($this->stream, 
                        $this->fields);
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
        Core\FixedWidthReader::open(null, $this->fields);
        return;
    }
}
