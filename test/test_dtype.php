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
    protected $_dtype;
    protected $_token;
    protected $_value;
    
    /**
     * Test the decode method.
     *
     */
    public function testDecode()
    {
        $value = $this->_dtype->decode($this->_token);
        $this->assertEquals($this->_value, $value);
        return;
    }

    /**
     * Test the encode method.
     *
     */
    public function testEncode()
    {
        $token = $this->_dtype->encode($this->_value);
        $this->assertEquals($this->_token, $token);
        return;
    }
}


class IntTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->_dtype = new IntType();
        $this->_token = '123';
        $this->_value = 123;
        return;
    }
}


class FloatTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->_dtype = new FloatType();
        $this->_token = '1.23';
        $this->_value = 1.23;
        return;
    }
}


class StringTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->_dtype = new StringType();
        $this->_token = 'abc';
        $this->_value = 'abc';
        return;
    }
    
    private function _setUpQuote()
    {
        $this->_dtype = new StringType('%s', '"');
        $this->_token = '"abc"';
        $this->_value = 'abc';
        return;
    }

    public function testDecodeQuote()
    {
        $this->_setUpQuote();
        $this->testDecode();
        return;
    }

    public function testEncodeQuote()
    {
        $this->_setUpQuote();
        $this->testEncode();
        return;
    }
}


class ConstTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->_dtype = new ConstType(9999);
        $this->_token = '9999';
        $this->_value = 9999;
        return;
    }

    /**
     * Test the decode method.
     *
     */
    public function testDecode()
    {
        // Test for constant value regardless of input.
        $value = $this->_dtype->decode('');
        $this->assertEquals($this->_value, $value);
        return;
    }

    /**
     * Test the encode method.
     *
     */
    public function testEncode()
    {
        // Test for constant value regardless of input.
        $token = $this->_dtype->encode(null);
        $this->assertEquals($this->_token, $token);
        return;
    }
}


class DateTimeTypeTest extends _DataTypeTest
{
    protected function setUp()
    {
        $this->_dtype = new DateTimeType('Ymd');
        $this->_token = '20130602';
        $this->_value = DateTime::createFromFormat('Ymd', '20130602');
        return;
    }
}