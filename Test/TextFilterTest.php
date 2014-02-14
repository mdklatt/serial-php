<?php
/**
 * Unit testing for the TextFilter class.
 *
 */
class Test_TextFilterTest extends PHPUnit_Framework_TestCase
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
