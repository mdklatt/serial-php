<?php
/**
 * Base class for SortReader/Writer unit testing.
 *
 */
abstract class Test_SortTest extends PHPUnit_Framework_TestCase
{   
    protected $numSorted = array(
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
    protected $allRandom;
    protected $numRandom;

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
}
