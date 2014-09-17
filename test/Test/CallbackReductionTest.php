<?php
/**
 * Unit testing for the CallbackReduction class.
 *
 */
class Test_CallbackReductionTest extends PHPUnit_Framework_TestCase
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
            array('int' => 1, 'float' =>  1.1),
            array('int' => 2, 'float' =>  2.2),
        );
        return;
    }
    
    /**
     * Test a single-field callback.
     */
    public function testSingleField()
    {
        $callback = new Serial_Core_CallbackReduction('array_sum', 'int', 'sum');
        $reduction = $callback->__invoke($this->records);
        $this->assertEquals(array('sum' => 3), $reduction);
        return;
    }

    /**
     * Test a multi-field callback.
     */
    public function testMultiField()
    {
        $callback = new Serial_Core_CallbackReduction(
            'Test_CallbackReductionTest::sum', array('int', 'float'), 'sum');
        $reduction = $callback->__invoke($this->records);
        $this->assertEquals(array('sum' => 6.3), $reduction);
        return;        
    }
    
    /**
     * Sample aggregate function.
     */
    public static function sum($args)
    {
        // Add a sequence of argument pairs.
        $sum = 0;
        foreach ($args as $pair) {
            $sum += array_sum($pair);
        }
        return $sum;
     }
}
