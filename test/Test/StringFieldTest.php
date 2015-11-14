<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the StringField class.
 *
 */
class StringFieldTest extends FieldTest
{
    private $quote_token;
    private $quote_field;

    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $fmt = '%5s';
        $pos = array(0, 5);
        $this->value = 'abc';
        $this->token = '  abc';
        $this->field = new Core\StringField('str', $pos, $fmt);
        $this->default_value = 'xyz';
        $this->default_token = '  xyz';
        $this->default_field = new Core\StringField('str', $pos, $fmt, 
                                   '', $this->default_value);
        $this->quote_token = '"abc"';
        $this->quote_field = new Core\StringField('str', $pos, '%s', 
                                 '"');
        return;
    }
    
    /**
     * Test the decode() method for an quoted string.
     *
     */
    public function testDecodeQuote()
    {
        $value = $this->quote_field->decode($this->quote_token);
        $this->assertEquals($this->value, $value);
        return;
    }

    /**
     * Test the encode() method for a quoted string.
     *
     */
    public function testEncodeQuote()
    {
        $quote_token = $this->quote_field->encode($this->value);
        $this->assertSame($this->quote_token, $quote_token);
        return;
    }
}
