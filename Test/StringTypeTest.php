<?php
/**
 * Unit testing for the StringType class.
 *
 */
class Test_StringTypeTest extends Test_DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $fmt = '%4s';
        $this->value = 'abc';
        $this->token = ' abc';
        $this->dtype = new Serial_Core_StringType($fmt);
        $this->default_value = 'xyz';
        $this->default_token = ' xyz';
        $this->default_dtype = new Serial_Core_StringType($fmt, '', $this->default_value);
        $this->quote_token = '"abc"';
        $this->quote_dtype = new Serial_Core_StringType('%s', '"');
        return;
    }
    
    /**
     * Test the decode() method for an quoted string.
     *
     */
    public function testDecodeQuote()
    {
        $value = $this->quote_dtype->decode($this->quote_token);
        $this->assertEquals($this->value, $value);
        return;
    }

    /**
     * Test the encode() method for a quoted string.
     *
     */
    public function testEncodeQuote()
    {
        $quote_token = $this->quote_dtype->encode($this->value);
        $this->assertSame($this->quote_token, $quote_token);
        return;
    }

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $this->assertSame(str_repeat(' ', 4), $this->dtype->encode(null));
        return;
    }
}
