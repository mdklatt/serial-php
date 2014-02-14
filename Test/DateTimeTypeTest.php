<?php
/**
 * Unit testing for the DateTimeType class.
 *
 */
class Test_DateTimeTypeTest extends Test_DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->value = DateTime::createFromFormat('Ymd', '20121231');
        $this->token = '20121231';
        $this->dtype = new Serial_Core_DateTimeType('Ymd');
        $this->default_value = DateTime::createFromFormat('Ymd', '19010101');
        $this->default_token = '19010101';
        $this->default_dtype = new Serial_Core_DateTimeType('Ymd', $this->default_value);
        return;
    }
}
