<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Unit testing for the SubstrFilter class.
 *
 */
class SubstrFilterTest extends \PHPUnit_Framework_TestCase
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
        // Filters need to match the first two lines.
        $this->data = array("abc\n", "def\n", "ghi\n");
        $values = array('bc', 'ef');
        $pos = array(1, 2);
        $this->whitelist = new Core\SubstrFilter($pos, $values);
        $this->whitelist = array($this->whitelist, '__invoke');  // PHP 5.2
        $this->blacklist = new Core\SubstrFilter($pos, $values, true);
        $this->blacklist = array($this->blacklist, '__invoke');  // PHP 5.2
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
