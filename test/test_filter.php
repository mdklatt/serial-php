<?php
/**
 * Unit tests for filter classes.
 *
 * The tests can be executed using a PHPUnit test runner, e.g. the phpunit
 * command.
 */


/**
 * Unit testing for the FieldFilter class.
 *
 */
class FieldFilterTest extends PHPUnit_Framework_TestCase
{
    private $whitelist;
    private $blacklist;
    private $data;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $values = array('abc', 'def');
        $this->whitelist = new Serial_Core_FieldFilter('test', $values);
        $this->blacklist = new Serial_Core_FieldFilter('test', $values, false);
        $this->data = array();
        $values[] = 'ghi';
        foreach ($values as $value) {
            $this->data[] = array('test' => $value);
        }
        return;
    }
    
    /**
     * Test the filter for whitelisting.
     *
     */
    public function test_whitelist()
    {
        $filtered = array_slice($this->data, 0, 2);
        $filtered[] = null;
        $this->assertEquals($filtered, array_map($this->whitelist, $this->data));
        return;
    }

    /**
     * Test the filter for whitelisting with a missing field.
     *
     */
    public function test_whitelist_missing()
    {
        $this->data = array(array('not_test' => 'xyz'));
        $filtered = array(null);
        $this->assertEquals($filtered, array_map($this->whitelist, $this->data));
        return;
    }

    /**
     * Test the filter for blacklisting.
     *
     */
    public function test_blacklist()
    {
        $filtered = array(null, null);
        $filtered[] = $this->data[2];
        $this->assertEquals($filtered, array_map($this->blacklist, $this->data));
        return;
    }

    /**
     * Test the filter for blacklisting with a missing field.
     *
     */
    public function test_blacklist_missing()
    {
        $this->data = array(array('not_test' => 'xyz'));
        $filtered = $this->data;
        $this->assertEquals($filtered, array_map($this->blacklist, $this->data));
        return;
    }  
}


/**
 * Unit testing for the TextFilter class.
 *
 */
class TextFilterTest extends PHPUnit_Framework_TestCase
{
    private $whitelist;
    private $blacklist;
    private $data;
    
    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $regex = '/abc|def/';
        $this->whitelist = new Serial_Core_TextFilter($regex);
        $this->blacklist = new Serial_Core_TextFilter($regex, false);
        $this->data = array("abc\n", "def\n", "ghi\n");
        return;
    }
    
    /**
     * Test the filter for whitelisting.
     *
     */
    public function test_whitelist()
    {
        $filtered = array_slice($this->data, 0, 2);
        $filtered[] = null;
        $this->assertEquals($filtered, array_map($this->whitelist, $this->data));
        return;
    }

    /**
     * Test the filter for blacklisting.
     *
     */
    public function test_blacklist()
    {
        $filtered = array(null, null);
        $filtered[] = $this->data[2];
        $this->assertEquals($filtered, array_map($this->blacklist, $this->data));
        return;
    }
}
