<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the DateTimeField class.
 *
 */
class DateTimeFieldTest extends FieldTest
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
        $pos = array(0, 19);
        $this->token = '2104-02-28T15:28:47';  // needs to be DateTime parsable
        $this->value = new \DateTime($this->token);
        $this->field = new Core\DateTimeField('date', $pos, $fmt);
        $this->default_token = '1801-01-01T00:00:00';  // check "old" dates
        $this->default_value = new \DateTime($this->default_token);
        $this->default_field = new Core\DateTimeField('date', $pos, $fmt, 
                                   $this->default_value);
        return;
    }
}
