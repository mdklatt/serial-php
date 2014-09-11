<?php
/**
 * Signal the end of input.
 *
 * This is used internally to signal EOF conditions, but it can also be used by
 * filters to halt input at any time.
 */
class Serial_Core_StopIteration extends Exception
{
    // This is inspired by the Python iterator protocol. It falls in the gray
    // area between an exceptional condition and flow control, but it's a more
    // elegant solution than code littered with EOF checks. For the intended
    // use case an exception is only thrown once per stream, so the performance
    // penalty should be minimal.
    
    /**
     * Initialize this object.
     */
    public function __construct()
    {
        parent::__construct('EOF');
        return;
    }
}
