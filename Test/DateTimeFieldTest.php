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
        $fmt = 'Y-m-d\TH:i:s';
        $this->token = '2104-02-28T15:28:47';  // needs to be DateTime parsable
        $this->value = new DateTime($this->token);
        $this->field = new Serial_Core_DateTimeField('date', 0, $fmt);
        $this->default_token = '1801-01-01T00:00:00';  // check "old" dates
        $this->default_value = new DateTime($this->default_token);
        $this->default_field = new Serial_Core_DateTimeField('date', 0, $fmt, 
                                   $this->default_value);
        return;
    }
}
