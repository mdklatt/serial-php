<?php
/**
 * Unit tests for dtype.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';


abstract class _DataTypeTest extends PHPUnit_Framework_TestCase
{    
    protected $dtype;
    protected $token;
    protected $value;
    protected $default_dtype;
    protected $default_token;
    protected $defualt_value;
    
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
     * Test the encode method.
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


class IntTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $fmt = '%4d';
        $this->value = 123;
        $this->token = ' 123';
        $this->dtype = new IntType($fmt);
        $this->default_value = -999;
        $this->default_token = '-999';
        $this->default_dtype = new IntType($fmt, $this->default_value);
        return;
    }
}


class FloatTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $fmt = '%6.3f';
        $this->value = 1.23;
        $this->token = ' 1.230';
        $this->dtype = new FloatType($fmt);
        $this->default_value = -9.999;
        $this->default_token = '-9.999';
        $this->default_dtype = new FloatType($fmt, $this->default_value);
        return;
    }
}


class StringTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $fmt = '%4s';
        $this->value = 'abc';
        $this->token = ' abc';
        $this->dtype = new StringType($fmt);
        $this->default_value = 'xyz';
        $this->default_token = ' xyz';
        $this->default_dtype = new StringType($fmt, '', $this->default_value);
        $this->quote_token = '"abc"';
        $this->quote_dtype = new StringType('%s', '"');
        return;
    }
    
    public function testDecodeQuote()
    {
        $value = $this->quote_dtype->decode($this->quote_token);
        $this->assertEquals($this->value, $value);
        return;
    }

    public function testEncodeQuote()
    {
        $quote_token = $this->quote_dtype->encode($this->value);
        $this->assertSame($this->quote_token, $quote_token);
        return;
    }

    /**
     * Test the encode() method for null output.
     *
     */
    public function testEncodeNull()
    {
        $this->assertSame(str_repeat(' ', 4), $this->dtype->encode(null));
        return;
    }
}


class ConstTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->value = 9999;
        $this->token = ' 9999';
        $this->dtype = new ConstType($this->value, '%5d');
        $this->default_value = $this->value;
        $this->default_token = $this->token;
        $this->default_dtype = $this->dtype;
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


class DateTimeTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->value = DateTime::createFromFormat('Ymd', '20121231');
        $this->token = '20121231';
        $this->dtype = new DateTimeType('Ymd');
        $this->default_value = DateTime::createFromFormat('Ymd', '19010101');
        $this->default_token = '19010101';
        $this->default_dtype = new DateTimeType('Ymd', $this->default_value);
        return;
    }
}


class ArrayTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $fields = array(
            "str" => array(0, new StringType()),
            "int" => array(1, new IntType())
        );
        $this->value = array(
            array("str" => "abc", "int" => 123),
            array("str" => "def", "int" => 456),
        );
        $this->token = array("abc", "123", "def", "456");
        $this->dtype = new ArrayType($fields);
        $this->default_value = array(array("str" => "xyz", "int" => -999));
        $this->default_token = array("xyz", "-999");
        $this->default_dtype = new ArrayType($fields, $this->default_value);
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