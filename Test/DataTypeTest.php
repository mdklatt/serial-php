<?php
/**
 * Base class for data type unit tests.
 *
 */
abstract class Test_DataTypeTest extends PHPUnit_Framework_TestCase
{    
    protected $dtype;
    protected $token;
    protected $value;
    protected $default_dtype;
    protected $default_token;
    protected $default_value;
    
    /**
     * Test the decode() method.
     *
     */
    public function testDecode()
    {
        $value = $this->dtype->decode($this->token);
        $this->assertEquals($this->value, $value);
        return;
    }
    
    /**
     * Test the decode() method for null input.
     *
     */
    public function testDecodeNull()
    {
        $this->assertEquals(null, $this->dtype->decode(' '));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testDecodeDefault()
    {
        $default_value = $this->default_dtype->decode(' ');
        $this->assertEquals($this->default_value, $default_value);
        return;
    }
    
    /**
     * Test the encode() method.
     *
     */
    public function testEncode()
    {
        $token = $this->dtype->encode($this->value);
        $this->assertSame($this->token, $token);
        return;
    }

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $this->assertSame('', $this->dtype->encode(null));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testEncodeDefault()
    {
        $default_token = $this->default_dtype->encode(null);
        $this->assertSame($this->default_token, $default_token);
        return;
    }
}
