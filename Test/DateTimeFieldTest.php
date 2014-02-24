<?php
/**
 * Unit testing for the DateTimeField class.
 *
 */
class Test_DateTimeFieldTest extends Test_FieldTest
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
        $this->field = new Serial_Core_DateTimeField('date', 0, 'Ymd');
        $this->default_value = DateTime::createFromFormat('Ymd', '19010101');
        $this->default_token = '19010101';
        $this->default_field = new Serial_Core_DateTimeField('date', 0, 'Ymd', $this->default_value);
        return;
    }
}
