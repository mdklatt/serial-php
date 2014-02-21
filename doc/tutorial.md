# Tutorial #


## Reading Data ##

Consider the following snippet of data:

    340010 2012-02-01 00:00 UTC -999.00M -999.00S
    340010 2012-03-01 00:00 UTC   72.00     1.23A
    340010 2012-04-01 00:10 UTC   63.80Q    0.00

This is fixed-width data consisting of a station identifier, a date string, a
time string, and observations from a sensor array. Each observation has a
floating point value and an optional flag string.

This data stream can be read using a `FixedWidthReader`. The reader must be
initialized with an array of field definitions. Each field is defined by its
name (the array key), position, and data type. For a fixed-width field the 
position is given by its character positions as a beginning and a length, 
inclusive of any spaces between fields.

    require 'Serial/Core/autoload.php';

    $fields = array(
        "stid" => array(array(0, 6), new Serial_Core_StringType()),
        "date" => array(array(6, 11), new Serial_Core_StringType()),
        "time" => array(array(17, 6), new Serial_Core_StringType()),
        "data1" => array(array(27, 8), new Serial_Core_FloatType()),
        "flag1" => array(array(35, 1), new Serial_Core_StringType()),
        "data2" => array(array(36, 8), new Serial_Core_FloatType()),
        "flag2" => array(array(44, 1), new Serial_Core_StringType()),
    );

    $stream = fopen("data.txt", "r");
    $reader = new Serial_Core_FixedWidthReader($stream, $fields);
    foreach ($reader as $record) {
        echo $record['date'].PHP_EOL;    
    }


###  Array Fields ###

If there are a large number of sensor data fields, defining and working with
these fields individually can be tedkous. For the sample data the format of
each sensor field is the same, so they can all be treated as a single array.
Each array element will have a value and a flag.

An `ArrayType` field must be initalized with the field definitions to use for
each array element. The position of the array itself is relative to the entire
input line, but the positions of the element fields are relative to an
individual element.

    $data_fields = array(
        // Define each data array element. The first field has a leading space.
        'value' => array(array(0, 8), new Serial_Core_FloatType()),
        'flag' => array(aray(8, 1), new Serial_Core_StringType()),
    );

    $sample_fields = array(
        // Skipping over the time zone field.
        'stid' => array(array(0, 6), new Serial_Core_StringType()),
        'date' => array(array(6, 11), new Serial_Core_StringType()),
        'time' => array(array(17, 6), new Serial_Core_StringType()),
        'data' => array(array(27, 18), new Serial_Core_ArrayType($data_fields)),
    );

    $stream = fopen('data.txt', 'r');
    $reader = new Serial_Core_FixedWidthReader($stream, $fields);
    foreach ($reader as $record) {
        foreach ($record['data'] as $sensor) {
            echo $sensor['value'].' '.$sensor['flag'].PHP_EOL;
        }
    }


By using a variable-length array, the same format definition can be used if the
the number of sensors varies from file to file or even record to record. A
variable-length array is created by setting its end position to null.
*Variable-length arrays must be at the end of the record*.

    $sample_fields = array(
        'stid' => array(array(0, 6), new Serial_Core_StringType()),
        'date' => array(array(6, 11), new Serial_Core_StringType()),
        'time' => array(array(17, 6), new Serial_Core_StringType()),
        'data' => array(array(27, null), new Serial_Core_ArrayType($data_fields)),
    );

    ...

    foreach ($reader as $record) {
        echo 'total sensors: '.count($record['data']).PHP_EOL;
        foreach ($record['data'] as $sensor) {
            echo $sensor['value'].', '.$sensor['flag'].PHP_EOL;
        }
    }


### DateTime Fields ###

The `DateTimeType` can be used for converting data to a `DateTime` object. For
the sample data, the date and time fields can be treated as a single `DateTime`
single field. A `DatetimeType` must be initialized with a PHP 
[date format string][0]. Make sure the default [time zone][1] has been set.

    date_default_timezone_set('UTC');
    $sample_fields = array(
        // Ignoring time zone field.
        'stid' => array(array(0, 6), new Serial_Core_StringType()),
        'timestamp' => array(array(6, 17), new Serial_Core_DateTimeType('Y-m-d H:i')),
        'data' => array(array(27, null), new Serial_Core_ArrayType($data_fields)),
    );


### Default Values ###

During input all fields in a record are assigned a value. If a field is blank
it is given the default value assigned to that field (`null` by default). The 
default value should be appropriate to that type, *e.g.* an `IntType` field 
should not have a string as its default value.

    $data_fields = array(
        // Assign 'M' to all missing flag values.
        'value' => array(array(0, 8), new Serial_Core_FloatType()),
        'flag' => array(aray(8, 1), new Serial_Core_StringType('%s', '', 'M')),
    );


## Writing Data ##

Data is written to a stream using a Writer. Writers implement a `write()` 
method for writing individual records and a `dump()` method for writing a 
sequence of records. 

Writers use the same field definitions as Readers with some additional 
requirements. A data type can be initialized with a [format string][2]; this 
is ignored by Readers, but it is used by Writers to convert a PHP value to 
text. Each data type has a default format, but for `FixedWidthWriter` fields a 
format string with the appropriate field width (inclusive of spaces between 
fields) **must** be specified.

For the `FixedWidthReader` example the time zone field was ignored. However,
when defining fields for a `FixedWidthWriter`, every field must be defined,
even if it's blank. Also, fields must be listed in their correct order.

A Writer will output a value for each of its fields with every data record. If 
a field is missing from a data record the Writer will use the default value for 
that field (`null` is encoded as a blank file). For input a default field value
can be anything, but for output it should be type-compatible, e.g. ''M'' would
be output as ''0'' for an `IntType`, which is probably not what is wanted.
Fields in the record that do not have a field definition are ignored.

With some minor modifications the field definitions used for reading the sample
data can be used for writing it. In fact, the modified fields can still be used
for reading the data, so a Reader and a Writer can be defined for a given data
format using one set of field definitions.

    $data_fields = array(
        'value' => array(array(0, 8), new Serial_Core_FloatType('%8.2f')),
        'flag' => array(aray(8, 1), new Serial_Core_StringType('%1s')),
    );

    $sample_fields = array(
        'stid' => array(array(0, 7), new Serial_Core_StringType('%6s')),
        'timestamp' => array(array(7, 16), new Serial_Core_Serial_Core_DateTimeType('Y-m-d H:i')),
        'timezone' => aray(array(23, 4), new Serial_Core_StringType('%4s', '', 'UTC')),
        'data' => array(array(27, null), new Serial_Core_ArrayType($data_fields)),
    );

    // Copy 'data.txt' to 'copy.txt'.
    $reader = new Serial_Core_FixedWidthReader(fopen('data.txt', 'r'));
    $writer = new Serial_Core_FixedWidthWriter(fopen('copy.txt', 'w'));
    foreach ($reader as $record) {
        // Write each record individually.
        unset($record['timezone']);  // rely on default value
        $writer->write($record);
    }
    // Or, write all records in a single call:
    // $writer->dump($reader);
    

## Delimited Data ##

The `DelimitedReader` and `DelimitedWriter` classes can be used for reading and
writing delimited data, e.g. CSV values:

    340010,2012-02-01 00:00,UTC,-999.00,M,-999.00,S
    340010,2012-03-01 00:00,UTC,72.00,,1.23,A
    340010,2012-04-01 00:10,UTC,63.80,Q,0.00,

Delimited fields are defined in the same way as fixed-width fields except that
the field positions are given by field number (starting with 0) instead of 
character positions. Scalar field positions are a single number while array 
field positions are a beginning position and a length. The format string is 
optional for most field types because a width is not required.

    $data_fields = array(
        'value' => array(0, new Serial_Core_FloatType('%.2f')),  // precision only
        'flag' => array(1, new Serial_Core_StringType()),
    );

    $sample_fields = array(
        'stid' => array(0, new Serial_Core_StringType()),
        'timestamp' => array(1, new Serial_Core_DateTimeType('Y-m-d H:i')),  // need format
        'timezone' => aray(2, new Serial_Core_StringType('%s', '', 'UTC')),
        'data' => array(array(3, null), new Serial_Core_ArrayType($data_fields)),
    );

    ...
    
    $delim = ",";
    $reader = new Serial_Core_DelimitedReader($istream, $sample_fields, $delim);
    $writer = new Serial_Core_DelimitedWriter($ostream, $sample_fields, $delim);


### Filters ###

Filters are used to manipulate data records after they have been parsed by a
Reader or before they are written by a Writer. A filter is any function, class
method, or callable object that takes a data record as its only argument and 
returns a data record or `null`.

    function month_filter($record)
    {
        // Filter function to restrict data to records from March.
        $month = record['timestamp']->format('n');
        return $month == 3 ? $record : null;
    }

    ...

    $reader->filter('month_filter');
    foreach ($reader as $record) {
        echo $record['date'].PHP_EOL;  // read March records only
    }
    
    ...
    
    $writer->filter('month_filter');
    $writer->dump($records);  // write March records only
    


### Advanced Filtering ###

Using classes as filters allows for more complex filtering.

    class MonthFilter
    {
        // Restrict input to the specified month.
    
        private $month;
    
        public function __construct($month)
        {
            $this->month = $month;
            return;
        }
    
        public function __invoke($record)
        {
            $month = $record['timestamp']->format('n');
            return $month == $this->month ? $record : null;
        }    
    }

    ...

    $reader->filter(new MonthFilter(3))  // read Marh records only


### Altering Records ###

A filter can return a modified version of its input record or a different
record altogether.

    class LocalTime
    {
        // Convert from UTC to local time.
    
        private $offset;
    
        public function __construct($offset)
        {
            $this->offset = $offset.' minutes';
            return;
        }
    
        public function __invoke($record)
        {
            // Implement the filter.
            $record['timestamp']->modify($this->offset);
            return $record;
        }    
    }

    ...

    $reader->filter(new LocalTime(-360))  # convert from UTC to CST


### Stopping Iteration ###

Returning `null` from a filter will drop individual records, but input can be
stopped altogether by throwing a StopIteration exception. For example, when 
filtering data by time, if the data are in chronological order it doesn't make 
sense to continue reading from the stream once the desired time period has been 
passed:

    class MonthFilter
    {
        // Restrict input to the specified month.

        private $month;

        public function __construct($month)
        {
            $this->month = $month;
            return;
        }

        public function __invoke($record)
        {
            $month = $record['timestamp']->format('n');
            if ($month > $this->month) {
                // Data is for one year in chronological order so there are no
                // more records for the desired month.
                throw new StopIteration();
            }
            return $month == $this->month ? $record : null;
        }    
    }


### Multple Filters ###

Filters can be chained and are called in order for each record. If any filter
returns `null` the record is immediately dropped. For the best performance 
filters should be ordered from most restrictive (most likely to return `null`) 
to least.

    $reader->filter(new MonthFilter(3), new LocalTime(-360));  # March, CST
    // Or, filters can also be added individually. Calling filter() with no
    // arguments clears all filters.
    // $reader->filter(new MonthFilter(3));
    // $reader->filter(new LocalTime(-360));
    // $reader->filter();


### Predefined Filters ###

The library defines the `FieldFilter` class for use with Readers and Writers.

    // Drop all records where the color field is not crimson or cream.
    $whitelist = new FieldFilter('color', array('crimson', 'cream'));
    
    // Drop all records where the color field is orange.
    $blacklist = new FieldFilter('color', array('orange'), False);
    
     

### Stream Filters ###

Filters can be applied directly to streams to manipulate text before it is
parsed by a Reader or after it is written by a Writer. The 
`StreamFilterManager::attach()` static method attaches a text filter to a 
stream. A text filter works just like a record filter except that it operates 
on a line of text instead of a data record. Text filters can be chained.

    function comment_filter($line)
    {
        // Remove comment lines before the Reader tries to parse them.
        return $line[0] == '#' ? null : $line;
    }
    
    function prcp_filter($line)
    {
        // Limit input to PRCP data. Filtering at the stream level can increase
        // performance by reducing the amount of data that has to be parsed
        // by the Reader.
        return substr($line, 21, 4) == 'PRCP' ? $line : null;
    }
    
    $stream = fopen('data.txt', 'r');
    StreamFilterManager::attach($stream, 'comment_filter');
    StreamFilterManager::attach($stream, 'prcp_filter');
    $reader = new FixedWidthReader($stream, $fields);


## Extending Core Classes ##

All the field definitions and filters for a specific format can be encapsulated
in a class that inherits from the appropriate Reader or Writer, and these
classes can be bundled into a module for that format.

    /** 
     * Module for reading and writing the sample data format.
     *
     */
    require 'Serial/Core/autoload.php';

    $DATA_FIELDS = array(
        'value' => array(0, new Serial_Core_FloatType('%.2f')),
        'flag' => array(1, new Serial_Core_StringType()),
    );

    $SAMPLE_FIELDS = array(
        'stid' => array(0, new Serial_Core_StringType()),
        'timestamp' => array(1, new Serial_Core_DateTimeType('Y-m-d H:i')),
        'timezone' => aray(2, new Serial_Core_ConstType('UTC')),
        'data' => array(array(3, null), new Serial_Core_ArrayType($DATA_FIELDS)),
    );

    $DELIM = ',';

    /**
     * Sample data reader.
     *
     * Base class implements the Iterator interface for reading records.
     */
    class SampleReader extends Serial_Core_DelimitedReader
    {
        private $offset;
        
        public function __construct($offset=-360)
        {
            global $SAMPLE_FIELDS, $DELIM;
            parent::__construct($SAMPLE_FIELDS, $DELIM);
            $this->offset = $offset.' minutes';  // offset from UTC
            $this->filter($this->timestamp_filter);
            return;
        }
        
        private function timestamp_filter($record)
        {
            // Filter function for LST corrections.
            $record['timestamp']->modify($this->offset);  // UTC to LST
            $record['timezone'] = 'LST';
            return $record;
        }
    }

    /**
     * Sample data writer.
     *
     * Base class defines write() for writing an individual record and dump()
     * for writing all records in a sequence.
     */
    class SampleWriter extends Serial_Core_DelimitedWriter
    {
        private $offset;
        
        public function __construct($offset=-360)
        {
            global $SAMPLE_FIELDS, $DELIM;
            parent::__construct($_SAMPLE_FIELDS, $_DELIM);
            $this->offset = -$offset.' minutes';  // offset from LST
            $this->filter($this->timestamp_filter);
            return;
        }
        
        private function timestamp_filter($record)
        {
            // Filter function for UTC corrections.
            $record['timestamp']->modify($this->offset);  // LST to UTC
            $record['timezone'] = 'UTC';
            return $record;
        }   
    }


## Buffers ##

Like filters, Buffers allow for postprocessing of input from a Reader or 
preprocessing out output to a Wrtier. However, Buffers can operate on more than
one record at a time. Buffers can be used, for example, to split or merge
records before passing them on. Like Readers and Writers, Buffers support
filtering; records are filtered after they have passed through the Buffer.

### Input Buffering ###

An input Buffer is basically a Reader that reads records from another Reader
(including another input Buffer) instead of lines of text from a stream. An
input Buffer should derived from the ReaderBuffer base class. It must implement
a `queue()` method to process records being read from a its Reader, and it may
override the `uflow()` method to handle the end of input once the Reader has
been exhausted.

    /**
     * Combine daily input into monthly totals.
     *
     * The base class implements the Reader interface for iterating over input
     * and applying filters.
     */
    class MonthlyTotal extends Serial_Core_ReaderBuffer
    {
        private $buffer = array();
        
        public function __construct($reader)
        {
            parent::__construct($reader);
            return;
        }
        
        protected function queue($record)
        {
            // Process each incoming record. The incoming data is assumed to in
            // chronological order.
            $month = record['date']->format('Y-m-01');
            if ($this->buffer && $this->buffer['date'] == $month) {
                // Add this record to the current month.
                $this->buffer['value'] += $record['value'];
            }
            else {
                // Output the previous month and start a new month.
                $this->output[] = $this->buffer;  // FIFO queue
                $this->buffer = $record;
                $this->buffer['date'] = $month;
            }
            return;
        }
        
        protected function uflow()
        {
            // This is called if the output queue is empty and the input reader
            // has been exhausted. No more records are coming so finish the
            // current month.
            if ($this->buffer) {
                $this->output[] = $this->buffer;
                $this->buffer = null;  // next call should trigger EOF
            }
            else {
                // This function *must* throw StopIteration on EOF.
                throw new StopIteration();
            }
            return;
        }
    }
    
    ...
    
    $monthly_records = iterator_to_array(new MonthlyTotal($reader));
    

## Tips and Tricks ##

### Quoted Strings ###

The `StringType` data type can reade and write quoted string by initialiing
it with the quote character to use.

    new Serial_Core_StringType('%s', '"');  // double-quoted string


### Nonstandard Line Endings ###

By default, lines of text are assumed to end with the appropriate line ending
for the current platform, i.e. `PHP_EOL`. For output, a different line ending
can be supplied to appropriate Writer constructor.

    define('ENDL', "\r\n");  // force Windows line endings
    $writer = new Serial_Core_FixedWidthWriter($stream, $fields, ENDL);
    
Input is more complicated. Both `DelimitedReader` and `FixedWidthReader` use
`fgets()`, which uses `PHP_EOL` as the line ending no matter what. User-defined
Readers are free to use a different implementation that allows for different
line endings.
    

### Header Data ###

Header data is outside the scope of `serial-core`. Client code is responsible
for reading or writing header data from or to the stream before `next()` or
`write()` is called for the first time. For derived classes this is typically
done by the `__construct()` method.


### Mixed-Type Data ###

Mixed-type data fields must be defined as a `StringType` for stream input and
output, but filters can be used to convert to/from multiple Python types based
on the field value.

    function missing_filter($record)
    {
        // Input filter for numeric data that's "M" if missing. The input
        // reader defines this field as a StringType.
        if (($value = $record['value']) == 'M') {
            $record['value'] = null;
        }
        else {
            $record['data'] = floatval($value);
        }
        return $record;
    }


### Combined Fields ###

Filters can be used to map a single field in the input/output stream to/from
multiple fields in the data record, or *vice versa*.

    function timestamp_filter($record)
    {
        // Output filter for the "timestamp" field. The data format defines
        // separate "date" and "time" fields. The superflous "timestamp" field
        // will be ignored by the Writer, so deleting it is redundant.
        $record['date'] = record['timestamp']->format('Y-m-d');
        $record['time'] = record['timestamp']->format('H:i:s'); 
        return $record;
    }



<!-- REFERENCES -->
[0]: http://www.php.net/manual/en/function.date.php "date formats"
[1]: http://www.php.net/manual/en/function.date-default-timezone-set.php "set time zone"
[2]: http://www.php.net/manual/en/function.sprintf.php "format strings"