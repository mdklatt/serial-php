<?php
/**
 * Unit testing for the IntType class.
 *
 */
class Test_IntTypeTest extends Test_DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $fmt = '%4d';
        $this->value = 123;
        $this->token = ' 123';
        $this->dtype = new Serial_Core_IntType($fmt);
        $this->default_value = -999;
        $this->default_token = '-999';
        $this->default_dtype = new Serial_Core_IntType($fmt, $this->default_value);
        return;
    }

    /**
     * Test the decode() method for zero.
     *
     * Zeroes are false-y, so need to make sure they aren't decoded as a null.
     */
    public function testDecodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame(0, $this->dtype->decode('0'));
        return;
    }

    /**
     * Test the encode() method for zero.
     *
     * Zeros are false-y, so need to make sure they aren't encoded as a null.
     */
    public function testEncodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame('   0', $this->dtype->encode(0));
        return;
    }
}
