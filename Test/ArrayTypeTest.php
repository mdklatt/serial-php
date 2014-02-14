<?php
/**
 * Unit testing for the ArrayType class.
 *
 */
class Test_ArrayTypeTest extends Test_DataTypeTest
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
            'str' => array(0, new Serial_Core_StringType()),
            'int' => array(1, new Serial_Core_IntType())
        );
        $this->value = array(
            array('str' => 'abc', 'int' => 123),
            array('str' => 'def', 'int' => 456),
        );
        $this->token = array('abc', '123', 'def', '456');
        $this->dtype = new Serial_Core_ArrayType($fields);
        $this->default_value = array(array('str' => 'xyz', 'int' => -999));
        $this->default_token = array('xyz', '-999');
        $this->default_dtype = new Serial_Core_ArrayType($fields, $this->default_value);
        return;
    }
    
    /**
     * Test the decode() method for null input.
     *
     */
    public function testDecodeNull()
    {
        $this->assertEquals(array(), $this->dtype->decode(array()));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testDecodeDefault()
    {
        $default_value = $this->default_dtype->decode(array());
        $this->assertEquals($this->default_value, $default_value);
        return;
    }

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $this->assertSame(array(), $this->dtype->encode(array()));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testEncodeDefault()
    {
        $default_token = $this->default_dtype->encode(array());
        $this->assertSame($this->default_token, $default_token);
        return;
    }    
}
