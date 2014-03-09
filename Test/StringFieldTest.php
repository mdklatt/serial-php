<?php
/**
 * Unit testing for the StringField class.
 *
 */
class Test_StringFieldTest extends Test_FieldTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $fmt = '%5s';
        $this->value = 'abc';
        $this->token = '  abc';
        $this->field = new Serial_Core_StringField('str', 0, $fmt);
        $this->default_value = 'xyz';
        $this->default_token = '  xyz';
        $this->default_field = new Serial_Core_StringField('str', 0, $fmt, '', $this->default_value);
        $this->quote_token = '"abc"';
        $this->quote_field = new Serial_Core_StringField('str', 0, '%s', '"');
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

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $this->assertSame(str_repeat(' ', 5), $this->field->encode(null));
        return;
    }
}
