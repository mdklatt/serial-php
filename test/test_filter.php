<?php
/**
 * Unit tests for filter.php.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */


/**
 * Unit testing for filter classes (private).
 *
 */
abstract class FilterTest extends PHPUnit_Framework_TestCase
{
    protected $accept;
    protected $reject;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->accept = array(array('test' => 'abc'), array('test' => 'def'));
        $this->reject = array(array('test' => 'uvw'), array('test' => 'xyz'));
        return;
    }
    
    /** 
     * Test for accepted records.
     *
     */
    public function test_accept()
    {
        $output = array_map($this->filter, $this->accept);
        $this->assertEquals($this->accept, $output);
        return;
    }

    /** 
     * Test for rejected records.
     *
     */
    public function test_reject()
    {
        $output = array_map($this->filter, $this->reject);
        $this->assertEquals(array(null, null), $output);
        return;
    }
}

/**
 * Unit testing for the BlacklistFilter class.
 *
 */
class BlacklistFilterTest extends FilterTest
{
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filter = new Serial_BlacklistFilter('test', array('uvw', 'xyz'));
        return;
    }
}


/**
 * Unit testing for the WhitelistFilter class.
 *
 */
class WhitelistFilterTest extends FilterTest
{   
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filter = new Serial_WhitelistFilter('test', array('abc', 'def'));
        return;
    }
}
