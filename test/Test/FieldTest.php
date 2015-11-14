<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Base class for Field unit tests.
 *
 */
abstract class FieldTest extends \PHPUnit_Framework_TestCase
{   
    protected $field;
    protected $token;
    protected $value;
    protected $default_field;
    protected $default_token;
    protected $default_value;
    
    /**
     * Test the decode() method.
     *
     */
    public function testDecode()
    {
        $value = $this->field->decode($this->token);
        $this->assertEquals($this->value, $value);
        return;
    }
    
    /**
     * Test the decode() method for null input.
     *
     */
    public function testDecodeNull()
    {
        $this->assertEquals(null, $this->field->decode(' '));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testDecodeDefault()
    {
        $default_value = $this->default_field->decode(' ');
        $this->assertEquals($this->default_value, $default_value);
        return;
    }
    
    /**
     * Test the encode() method.
     *
     */
    public function testEncode()
    {
        $token = $this->field->encode($this->value);
        $this->assertSame($this->token, $token);
        return;
    }

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $token = str_repeat(' ', $this->field->width);
        $this->assertSame($token, $this->field->encode(null));
        return;
    }

    /**
     * Test the decode() method for a default value.
     *
     */
    public function testEncodeDefault()
    {
        $default_token = $this->default_field->encode(null);
        $this->assertSame($this->default_token, $default_token);
        return;
    }
}
