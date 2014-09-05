<?php
/**
 * Unit testing for the ArrayField class.
 *
 */
class Test_ArrayFieldTest extends Test_FieldTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $fields = array(
            new Serial_Core_StringField('str', 0),
            new Serial_Core_IntField('int', 1),
        );
        $this->value = array(
            array('str' => 'abc', 'int' => 123),
            array('str' => 'def', 'int' => 456),
        );
        $this->token = array('abc', '123', 'def', '456');
        $this->field = new Serial_Core_ArrayField('array', array(0, 4), $fields);
        $this->default_value = array(array('str' => 'xyz', 'int' => -999));
        $this->default_token = array('xyz', '-999');
        $this->default_field = new Serial_Core_ArrayField('array', array(0, 4), $fields, $this->default_value);
        return;
    }
    
    /**
     * Test the decode() method for null input.
     *
     */
    public function testDecodeNull()
    {
        $this->assertEquals(array(), $this->field->decode(array()));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testDecodeDefault()
    {
        $default_value = $this->default_field->decode(array());
        $this->assertEquals($this->default_value, $default_value);
        return;
    }

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $this->assertSame(array(), $this->field->encode(array()));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testEncodeDefault()
    {
        $default_token = $this->default_field->encode(array());
        $this->assertSame($this->default_token, $default_token);
        return;
    }    
}
