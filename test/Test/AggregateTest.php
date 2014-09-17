<?php
/**
 * Base class for aggregate class unit testing.
 *
 */
abstract class Test_AggregateTest extends PHPUnit_Framework_TestCase
{   
    protected $records;

    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->records = array(
            array('str' => 'abc', 'int' => 1, 'float' =>  1.),
            array('str' => 'abc', 'int' => 1, 'float' =>  2.),
            array('str' => 'abc', 'int' => 3, 'float' =>  3.),
            array('str' => 'def', 'int' => 3, 'float' =>  4.),
        );
        return;
    }
    
    /**
     * Sample custom key function
     */
    public static function key($record)
    {
        return array('KEY' => strtoupper($record['str']));
    }
}
