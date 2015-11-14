<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the FieldFilter class.
 *
 */
class FieldFilterTest extends \PHPUnit_Framework_TestCase
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
        $this->whitelist = new Core\FieldFilter('test', $values);
        $this->blacklist = new Core\FieldFilter('test', $values, true);
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
    public function testWhitelist()
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
    public function testWhitelistMissing()
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
    public function testBlacklist()
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
    public function testBlacklistMissing()
    {
        $this->data = array(array('not_test' => 'xyz'));
        $filtered = $this->data;
        $this->assertEquals($filtered, array_map($this->blacklist, $this->data));
        return;
    }  
}
