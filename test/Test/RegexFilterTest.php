<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the RegexFilter class.
 *
 */
class RegexFilterTest extends \PHPUnit_Framework_TestCase
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
        $this->whitelist = new Core\RegexFilter($regex);
        $this->blacklist = new Core\RegexFilter($regex, true);
        $this->data = array("abc\n", "def\n", "ghi\n");
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
}
