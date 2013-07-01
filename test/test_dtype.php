<?php
/**
 * Unit tests for dtype.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'dtype.php';


/**
 * Unit testing for data type classes.
 *
 */
abstract class _DataTypeTest extends PHPUnit_Framework_TestCase
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


/**
 * Unit testing for the IntType class.
 *
 */
class IntTypeTest extends _DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
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

    /**
     * Test decoding for zero.
     *
     * Zeroes are false-y, so need to make sure they aren't decoded as a null.
     */
    public function testDecodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame(0, $this->dtype->decode('0'));
        return;
    }

    /**
     * Test encoding for zero.
     *
     * Zeros are false-y, so need to make sure they aren't encoded as a null.
     */
    public function testEncodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame('   0', $this->dtype->encode(0));
        return;
    }
}


/**
 * Unit testing for the FloatType class.
 *
 */
class FloatTypeTest extends _DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
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

    /**
     * Test decoding for zero.
     *
     * Zeros are false-y, so need to make sure they aren't decoded as a null.
     */
    public function testDecodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame(0., $this->dtype->decode('0'));
        return;
    }

    /**
     * Test encoding for zero.
     *
     * Zeros are false-y, so need to make sure they aren't encoded as a null.
     */
    public function testEncodeZero()
    {
        // Make sure to use assertSame() so that === is used for the test.
        $this->assertSame(' 0.000', $this->dtype->encode(0));
        return;
    }
}


/**
 * Unit testing for the StringType class.
 *
 */
class StringTypeTest extends _DataTypeTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
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
    
    /**
     * Test the decode() method for an quoted string.
     *
     */
    public function testDecodeQuote()
    {
        $value = $this->quote_dtype->decode($this->quote_token);
        $this->assertEquals($this->value, $value);
        return;
    }

    /**
     * Test the encode() method for a quoted string.
     *
     */
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


/**
 * Unit testing for the ConstType class.
 *
 */
class ConstTypeTest extends _DataTypeTest
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


/**
 * Unit testing for the DateTimeType class.
 *
 */
class DateTimeTypeTest extends _DataTypeTest
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
        $this->dtype = new DateTimeType('Ymd');
        $this->default_value = DateTime::createFromFormat('Ymd', '19010101');
        $this->default_token = '19010101';
        $this->default_dtype = new DateTimeType('Ymd', $this->default_value);
        return;
    }
}


/**
 * Unit testing for the ArrayType class.
 *
 */
class ArrayTypeTest extends _DataTypeTest
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
            'str' => array(0, new StringType()),
            'int' => array(1, new IntType())
        );
        $this->value = array(
            array('str' => 'abc', 'int' => 123),
            array('str' => 'def', 'int' => 456),
        );
        $this->token = array('abc', '123', 'def', '456');
        $this->dtype = new ArrayType($fields);
        $this->default_value = array(array('str' => 'xyz', 'int' => -999));
        $this->default_token = array('xyz', '-999');
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