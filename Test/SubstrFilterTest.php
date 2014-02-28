<?php
/**
 * Unit testing for the SubstrFilter class.
 *
 */
class Test_SubstrFilterTest extends PHPUnit_Framework_TestCase
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
        $this->whitelist = new Serial_Core_SubstrFilter($pos, $values);
        $this->blacklist = new Serial_Core_SubstrFilter($pos, $values, false);
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
