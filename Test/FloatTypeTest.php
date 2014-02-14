<?php
/**
 * Unit testing for the FloatType class.
 *
 */
class Test_FloatTypeTest extends Test_DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $fmt = '%6.3f';
        $this->value = 1.23;
        $this->token = ' 1.230';
        $this->dtype = new Serial_Core_FloatType($fmt);
        $this->default_value = -9.999;
        $this->default_token = '-9.999';
        $this->default_dtype = new Serial_Core_FloatType($fmt, $this->default_value);
        return;
    }

    /**
     * Test the decode() method for zero.
     *
     * Zeros are false-y, so need to make sure they aren't decoded as a null.
     */
    public function testDecodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame(0., $this->dtype->decode('0'));
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
        $this->assertSame(' 0.000', $this->dtype->encode(0));
        return;
    }

    /**
     * Test the decode() method for NaN.
     *
     */
    public function testDecodeNan()
    {
        foreach (array('NaN', 'nan', 'NAN') as $nan) {
            $this->assertTrue(is_nan($this->dtype->decode($nan)));            
        }
        return;
    }

    /**
     * Test the encode() method for NaN.
     *
     */
    public function testEncodeNan()
    {
        $this->assertSame('NaN', $this->dtype->encode(NAN));
        return;
    }
}
