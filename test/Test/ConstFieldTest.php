<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the ConstField class.
 *
 */
class ConstFieldTest extends FieldTest
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
        $this->field = new Core\ConstField('const', array(0, 5), $this->value, '%4d');
        $this->default_value = $this->value;
        $this->default_token = $this->token;
        $this->default_field = $this->field;
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
