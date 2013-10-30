<?php
/**
 * Base class for tabular data readers.
 *
 * Tabular data is organized into named fields such that each field occupies
 * the same position in each input record. One line of text corresponds to one
 * complete record.
 */
abstract class Serial_Core_TabularReader extends Serial_Core_Reader
{
    protected $stream;
    protected $fields;
    
    /**
     * Initialize this object.
     *
     * The input stream must be an instance of a Serial_Core_IStreamAdaptor or 
     * a regular PHP stream that works with fgets().
     */
    public function __construct($stream, $fields, $endl="\n")
    {
        $this->stream = $stream;
        foreach ($fields as $name => $field) {
            list($pos, $dtype) = $field;
            $this->fields[$name] = new Serial_Core_Field($pos, $dtype);
        }
        $this->endl = $endl;
        return;
    }
    
    /**
     * Split a line of text into tokens.
     *
     */
    abstract protected function split($line);

    /**
     * Retrieve the next parsed data record from the stream.
     * 
     */
    protected function get()
    {
        if (($line = fgets($this->stream)) === false) {
            throw new Serial_Core_EofException();
        }
        $tokens = $this->split(rtrim($line), $this->endl);
        $record = array();
        $pos = 0;
        foreach ($this->fields as $name => $field) {
            $record[$name] = $field->dtype->decode($tokens[$pos++]);
        }
        return $record;
    }    
}
