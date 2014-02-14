<?php
/**
 * Unit testing for the ConstType class.
 *
 */
class Test_ConstTypeTest extends Test_DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->value = 9999;
        $this->token = ' 9999';
        $this->dtype = new Serial_Core_ConstType($this->value, '%5d');
        $this->default_value = $this->value;
        $this->default_token = $this->token;
        $this->default_dtype = $this->dtype;
        return;
    }

    /**
     * Test the decode() method for null input.
     *
     */
    public function testDecodeNull()
    {
        $this->testDecodeDefault();
        return;
    }

    /**
     * Test the encode() method for null input.
     *
     */
    public function testEncodeNull()
    {
        $this->testEncodeDefault();
        return;
    }
}
