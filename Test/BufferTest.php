<?php
/**
 * Base class for buffer class unit testing.
 *
 */
abstract class Test_BufferTest extends PHPUnit_Framework_TestCase
{   
    static public function reject_filter($record)
    {
        return $record['int'] != 123 ? $record : null;
    }
    
    protected $input;
    protected $output;
    protected $reader;
    protected $writer;

    /**
     * Set up the test fixture.
     *
     * This is called before each test is run so that they are isolated from 
     * any side effects.
     */
    protected function setUp()
    {
        $this->input = array(
            array('int' => 123, 'arr' => array(array('x' => 'abc', 'y' => 'def'))),
            array('int' => 456, 'arr' => array(array('x' => 'ghi', 'y' => 'jkl'))),
            array('int' => 789, 'arr' => array(array('x' => 'mno', 'y' => 'pqr'))),
        );
        $this->output = array(
            array('int' => 123, 'arr' => array(array('x' => 'ghi', 'y' => 'jkl'))),
            array('int' => 789, 'arr' => array(array('x' => 'mno', 'y' => 'pqr'))),
        );
        return;
    }
}
