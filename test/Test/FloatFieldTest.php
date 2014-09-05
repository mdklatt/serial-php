<?php
/**
 * Unit testing for the FloatField class.
 *
 */
class Test_FloatFieldTest extends Test_FieldTest
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
        $pos = array(0, 7);
        $this->value = 1.23;
        $this->token = '  1.230';
        $this->field = new Serial_Core_FloatField('float', $pos, $fmt);
        $this->default_value = -9.999;
        $this->default_token = ' -9.999';
        $this->default_field = new Serial_Core_FloatField('float', $pos, $fmt,
                               $this->default_value);
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
        $this->assertSame(0., $this->field->decode('0'));
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
        $this->assertSame('  0.000', $this->field->encode(0));
        return;
    }

    /**
     * Test the decode() method for NaN.
     *
     */
    public function testDecodeNan()
    {
        foreach (array('NaN', 'nan', 'NAN') as $nan) {
            $this->assertTrue(is_nan($this->field->decode($nan)));            
        }
        return;
    }

    /**
     * Test the encode() method for NaN.
     *
     */
    public function testEncodeNan()
    {
        $this->assertSame('NaN', $this->field->encode(NAN));
        return;
    }
}
