<?php
namespace Serial\Core\Test;
use Serial\Core as Core;

/**
 * Base class for SortReader/Writer unit testing.
 *
 */
abstract class SortTest extends \PHPUnit_Framework_TestCase
{ 
    // These arrays could be created on the fly using range(), shuffle() and 
    // sort(), but the mod-sorted arrays don't come out right due to the lack
    // of stable sorting.
      
    protected $numSorted = array(
        // Sorted by the 'num', then 'mod'.
        array('num' => 0, 'mod' => 0),
        array('num' => 1, 'mod' => 1),
        array('num' => 2, 'mod' => 0),
        array('num' => 3, 'mod' => 1),
        array('num' => 4, 'mod' => 0),
        array('num' => 5, 'mod' => 1),
        array('num' => 6, 'mod' => 0),
        array('num' => 7, 'mod' => 1),
        array('num' => 8, 'mod' => 0),
        array('num' => 9, 'mod' => 1),
    );
    protected $modSorted = array(
        // Sorted by 'mod' then 'num'.
        array('num' => 0, 'mod' => 0),
        array('num' => 2, 'mod' => 0),
        array('num' => 4, 'mod' => 0),
        array('num' => 6, 'mod' => 0),
        array('num' => 8, 'mod' => 0),
        array('num' => 1, 'mod' => 1),
        array('num' => 3, 'mod' => 1),
        array('num' => 5, 'mod' => 1),
        array('num' => 7, 'mod' => 1),
        array('num' => 9, 'mod' => 1),
    );
    protected $numRandom = array(
        // Sorted by 'mod', 'num' is random.
        array('num' => 4, 'mod' => 0),
        array('num' => 8, 'mod' => 0),
        array('num' => 0, 'mod' => 0),
        array('num' => 2, 'mod' => 0),
        array('num' => 6, 'mod' => 0),
        array('num' => 5, 'mod' => 1),
        array('num' => 1, 'mod' => 1),
        array('num' => 7, 'mod' => 1),
        array('num' => 9, 'mod' => 1),
        array('num' => 3, 'mod' => 1),
    );

    protected $allRandom = array(
        // No sorting.
        array('num' => 9, 'mod' => 1),
        array('num' => 2, 'mod' => 0),
        array('num' => 8, 'mod' => 0),
        array('num' => 0, 'mod' => 0),
        array('num' => 6, 'mod' => 0),
        array('num' => 3, 'mod' => 1),
        array('num' => 1, 'mod' => 1),
        array('num' => 4, 'mod' => 0),
        array('num' => 7, 'mod' => 1),
        array('num' => 5, 'mod' => 1),
    );


    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->allRandom = $this->numSorted;
        shuffle($this->allRandom);
        return;
    }
    
    /**
     * Sample key function for testing.
     */
    public static function keyFunc($record)
    {
        $keyvals = array();
        foreach (array('mod', 'num') as $key) {
            $keyvals[$key] = $record[$key];
        }
        return $keyvals;
    } 
    
}
