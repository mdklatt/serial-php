<?php
/**
 * Unit tests for filter.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */
require_once 'filter.php';

abstract class _FilterTest extends PHPUnit_Framework_TestCase
{
    protected $accept;
    protected $reject;
    
    protected function setUp()
    {
        $this->accept = array(array("test" => "abc"), array("test" => "def"));
        $this->reject = array(array("test" => "uvw"), array("test" => "xyz"));
        return;
    }
    
    public function test_accept()
    {
        $output = array_map($this->filter, $this->accept);
        $this->assertEquals($this->accept, $output);
        return;
    }

    public function test_reject()
    {
        $output = array_map($this->filter, $this->reject);
        $this->assertEquals(array(null, null), $output);
        return;
    }
}


class BlacklistFilterTest extends _FilterTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->filter = new BlacklistFilter('test', array('uvw', 'xyz'));
        return;
    }
}


class WhitelistFilterTest extends _FilterTest
{   
    protected function setUp()
    {
        parent::setUp();
        $this->filter = new WhitelistFilter('test', array('abc', 'def'));
        return;
    }
}
