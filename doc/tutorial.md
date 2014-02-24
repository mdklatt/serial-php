# Overview #

The **serial-core** library can be used to read and write serial data
consisting of sequential records of typed fields. Data is read into or written
from associative arrays that are keyed by field name. Load the library by
including `Serial/Core/autoload.php`.


# Reading Data #

This is fixed-width data consisting of a station identifier, a date string, a
time string, and observations from a sensor array. Each observation has a
floating point value and an optional flag string.

    340010 2012-02-01 00:00 UTC -999.00M -999.00S
    340010 2012-03-01 00:00 UTC   72.00     1.23A
    340010 2012-04-01 00:10 UTC   63.80Q    0.00

This data stream can be read using a `FixedWidthReader`. The reader must be
initialized with an array of field definitions. Each field is defined by its
name, position, and data type. For a fixed-width field the position array is a
substring specifier (beg, len), inclusive of any spaces between fields.

    require 'Serial/Core/autoload.php';

    $fields = array(
        // Ignoring time zone field. 
        new Serial_Core_StringField('stid', array(0, 6)),
        new Serial_Core_StringField('date', array(6, 11)),
        new Serial_Core_StringField('time', arraay(17, 6)),
        new Serial_Core_FloatField('data1', array(27, 8))),
        new Serial_Core_StringField('flag1', array(35, 1)),
        new Serial_Core_FloatField('data2' array(36, 8)),
        new Serial_Core_StringField('flag2', array(44, 1)),
    );

    $stream = fopen("data.txt", "r");
    $reader = new Serial_Core_FixedWidthReader($stream, $fields);
    foreach ($reader as $record) {
        echo $record['date'].PHP_EOL;    
    }

##  Array Fields ##

If there are a large number of sensor data fields, defining and working with
these fields individually can be tedious. For the sample data the format of
each sensor field is the same, so they can all be treated as a single array.
Each array element will have a value and a flag.

An `ArrayField` must be initalized with the field definitions to use for each
array element. The position of the array itself is relative to the entire input
line, but the positions of the element fields are relative to an individual
element.

    $array_fields = array(
        // Define each data array element. The first field has a leading space.
        new Serial_Core_FloatField('value', array(0, 8)),
        new Serial_Core_Stringfield('flag', array(8, 1)),
    );

    $sample_fields = array(
        // Ignoring time zone field.
        new Serial_Core_StringField('stid', array(0, 6)),
        new Serial_Core_StringField('date', array(6, 11)),
        new Serial_Core_StringField('time', arraay(17, 6)),
        new Serial_Core_ArrayField('data', array(27, 18), $array_fields)),
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
variable-length array is created by setting its length to `null`.
*Variable-length arrays must be at the end of the record*.

    $sample_fields = array(
        new Serial_Core_StringField('stid', array(0, 6)),
        new Serial_Core_StringField('date', array(6, 11)),
        new Serial_Core_StringField('time', arraay(17, 6)),
        new Serial_Core_ArrayField('data', array(27, null), $array_fields)),
    );

    ...

    foreach ($reader as $record) {
        echo 'total sensors: '.count($record['data']).PHP_EOL;
        foreach ($record['data'] as $sensor) {
            echo $sensor['value'].', '.$sensor['flag'].PHP_EOL;
        }
    }

## DateTime Fields ##

A `DateTimeField` can be used for converting data to a `DateTime` object. For
the sample data, the date and time fields can be treated as a single `DateTime`
single field. A `DatetimeField` must be initialized with a PHP 
[date format string][1]. Make sure the default [time zone][2] has been set.

    date_default_timezone_set('UTC');
    $sample_fields = array(
        // Ignoring time zone field.
        new Serial_Core_StringField('stid', array(0, 6)),
        new Serial_Core_DateTimeField('timestamp', array(6, 17), 'Y-m-d H:i'),
        new Serial_Core_ArrayField('data', array(27, 18), $array_fields)),
    );

## Default Values ##

During input all fields in a record are assigned a value. If a field is blank
it is given the default value assigned to that field (`null` by default). The 
default value should be appropriate to that type, *e.g.* an `IntField` field 
should not have a string as its default value.

    $array_fields = array(
        // Assign 'M' to all missing flag values.
        new Serial_Core_FloatField('value', array(0, 8)),
        new Serial_Core_Stringfield('flag', array(8, 1), '%1s', '', 'M'),
    );


# Writing Data #

Data is written to a stream using a Writer. Writers implement a `write()` 
method for writing individual records and a `dump()` method for writing a 
sequence of records. 

Writers use the same field definitions as Readers with some additional 
requirements. A data type can be initialized with a [format string][3]; this 
is ignored by Readers, but it is used by Writers to convert a PHP value to 
text. Each data type has a default format, but for `FixedWidthWriter` fields a 
format string with the appropriate field width (inclusive of spaces between 
fields) **must** be specified.

For the `FixedWidthReader` example the time zone field was ignored. However,
when defining fields for a `FixedWidthWriter`, every field must be defined,
even if it's blank. Also, fields must be listed in their correct order.

A Writer will output a value for each of its fields with every data record. If 
a field is missing from a data record the Writer will use the default value for 
that field (`null` is encoded as a blank field). For input a default value
can be anything, but for output it should be type-compatible, e.g. ''M'' would
be output as ''0'' for an `IntField`, which is probably not what is wanted.
Fields in the record that do not have a field definition are ignored.

With some minor modifications the field definitions used for reading the sample
data can be used for writing it. In fact, the modified fields can still be used
for reading the data, so a Reader and a Writer can be defined for a given data
format using one set of field definitions.

    $array_fields = array(
        new Serial_Core_FloatField('value', array(0, 8), '%8.2f'),
        new Serial_Core_Stringfield('flag', array(8, 1), '%1s'),
    );

    $sample_fields = array(
        new Serial_Core_StringField('stid', array(0, 7), '%7s'),
        new Serial_Core_DateTimeField('timestamp', array(7, 16), 'Y-m-d H:i'),
        new Serial_Core_StringField('timezone', array(23, 4), '%4s'),
        new Serial_Core_ArrayField('data', array(27, null), $array_fields)),
    );

    // Copy 'data.txt' to 'copy.txt'.
    $reader = new Serial_Core_FixedWidthReader(fopen('data.txt', 'r'), $sample_fields);
    $writer = new Serial_Core_FixedWidthWriter(fopen('copy.txt', 'w'), $sample_fields);
    foreach ($reader as $record) {
        // Write each record to the stream. Or, write all records in a single 
        // call: $writer->dump($reader);
        $writer->write($record);
    }
    

# Delimited Data #

The `DelimitedReader` and `DelimitedWriter` classes can be used for reading and
writing delimited data, e.g. a CSV file.

    340010,2012-02-01 00:00,UTC,-999.00,M,-999.00,S
    340010,2012-03-01 00:00,UTC,72.00,,1.23,A
    340010,2012-04-01 00:10,UTC,63.80,Q,0.00,

Delimited fields are defined in the same way as fixed-width fields except that
scalar field positions are given by field number (starting at 0). Array fields
still use a (beg, len) pair. The format string is optional for most field 
types because a width is not required.

    $array_fields = array(
        new Serial_Core_FloatField('value', 0, '%.2f'),  // precision only
        new Serial_Core_Stringfield('flag', 1, '%1s'),
    );

    $sample_fields = array(
        new Serial_Core_StringField('stid', 0),
        new Serial_Core_DateTimeField('timestamp', 1, 'Y-m-d H:i'),  // format required
        new Serial_Core_StringField('timezone', 2),
        new Serial_Core_ArrayField('data', array(3, null), $array_fields)),
    );

    ...
    
    $delim = ',';
    $reader = new Serial_Core_DelimitedReader($istream, $sample_fields, $delim);
    $writer = new Serial_Core_DelimitedWriter($ostream, $sample_fields, $delim);


# Filters #

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
    $records = iterator_to_array($reader);  // read March records only
    
    ...
    
    $writer->filter('month_filter');
    $writer->dump($records);  // write March records only
    

## Advanced Filtering ##

Any callable object can be a filter, including a class that defines an
`__invoke()` method. This allows for the creation of more complex filters.

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
            // The filter function. 
            $month = $record['timestamp']->format('n');
            return $month == $this->month ? $record : null;
        }    
    }

    ...

    $reader->filter(new MonthFilter(3))  // read March records only

## Altering Records ##

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
            // The filter function.
            $record['timestamp']->modify($this->offset);
            return $record;
        }    
    }

    ...

    $reader->filter(new LocalTime(-360))  # convert from UTC to CST

## Stopping Iteration ##

Returning `null` from a filter will drop individual records, but if the filter 
can determine that there will be no more valid input it can throw a 
`StopIteration` exception to stop input altogether.

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
                // Data are known to be for one year in chronological order so 
                // there are no more records for the desired month.
                throw new Serial_Core_StopIteration();
            }
            return $month == $this->month ? $record : null;
        }    
    }

## Multple Filters ##

Filters can be chained and are called in order for each record. If any filter
returns `null` the record is immediately dropped. For the best performance 
filters should be ordered from most restrictive (most likely to return `null`) 
to least.

    $reader->filter(new MonthFilter(3));
    $reader->filter(new LocalTime(-360));
    $reader->filter();  // clear all filters
    $reader->filter(new MonthFilter(3), new LocalTime(-360));

## Predefined Filters ##

The library defines the `FieldFilter` class for use with Readers and Writers.

    // Drop all records where the color field is not crimson or cream.
    $whitelist = new Serial_Core_FieldFilter('color', array('crimson', 'cream'));
    
    // Drop all records where the color field is orange.
    $blacklist = new Serial_Core_FieldFilter('color', array('orange'), false);

## Stream Filters ##

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
    
    // Limit input to PRCP data. Filtering at the stream level can increase
    // performance by reducing the amount of data that has to be parsed
    // by the Reader.
    $prcp_filter = new Serial_Core_SubstrFilter(array(21, 4), array('PRCP'));
    
    $stream = fopen('data.txt', 'r');
    Serial_Core_StreamFilterManager::attach($stream, 'comment_filter');
    Serial_Core_StreamFilterManager::attach($stream, $prcp_filter);
    $reader = new Serial_Core_FixedWidthReader($stream, $fields);


# Custom Data Formats #

The intent of the `Serial/Core` library is to provide a framework for dealing
with a wide variety of data formats. The data field definitions are precribed
by the the format, but filters can be used to build any convenient data model 
on top of that format. Philosophically, reading and writing should be inverse
operations. A Reader and Writer should operate on the same data model such
that the input from a Reader could be passed to a Writer to recreate the input
file.

All the field definitions and filters for a specific format can be encapsulated 
in classes that inherit from the appropriate Reader or Writer, and these
classes can be bundled into a module for that format. There are two categories
of filters, class filters and user filters. Class filters are part of the data
model, while user filters are optionally applied by client code. Readers apply 
class filters before any user filters, and Writers apply them after any user 
filters. Class filters are not affected by the `filter()` method; instead, 
access them directly using the `classFilters` attribute.

    /** 
     * Module for reading and writing the sample data format.
     *
     */
    require 'Serial/Core/autoload.php';

    define('SAMPLE_DELIM' ',');

    $SAMPLE_ARRAY_FIELDS = array(
        new Serial_Core_FloatField('value', 0, '%.2f'),
        new Serial_Core_Stringfield('flag', 1, '%1s'),
    );

    $SAMPLE_FIELDS = array(
        new Serial_Core_StringField('stid', 0),
        new Serial_Core_DateTimeField('timestamp', 1, 'Y-m-d H:i'),
        new Serial_Core_ConstField('timezone', 'UTC'),
        new Serial_Core_ArrayField('data', array(3, null), $SAMPLE_ARRAY_FIELDS)),
    );


    /**
     * Sample data reader.
     *
     * The base class implements the Iterator interface for reading records.
     * All times are converted from UTC to LST on input.
     */
    class SampleReader extends Serial_Core_DelimitedReader
    {
        // Base class implements the Iterator interface for reading records.
        
        private $offset;
        
        public function __construct($offset=-360)
        {
            global $SAMPLE_FIELDS, $DELIM;
            parent::__construct($SAMPLE_FIELDS, SAMPLE_DELIM);
            $this->offset = "{$offset} minutes";  // offset from UTC
            $this->class_filters[] = $this->timestampFilter;  // applied first
            return;
        }
        
        private function timestampFilter($record)
        {
            // Filter function for timestamp corrections.
            $record['timestamp']->modify($this->offset);  // UTC to LST
            $record['timezone'] = 'LST';
            return $record;
        }
    }
    
    
    /**
     * Sample data writer.
     *
     * The base class defines write() and dump() for writing records. All times
     * are converted from LST to UTC on output.
     */ 
    class SampleWriter extends Serial_Core_DelimitedWriter
    {
        // Base class defines write() for writing an individual record and
        // dump() for writing all records in a sequence.
        
        private $offset;
        
        public function __construct($offset=-360)
        {
            global $SAMPLE_FIELDS, $DELIM;
            parent::__construct($SAMPLE_FIELDS, SAMPLE_DELIM);
            $this->offset = -$offset.' minutes';  // offset from LST to UTC
            $this->class_filters[] = $this->timestampFilter;  // applied last
            return;
        }
        
        private function timestampFilter($record)
        {
            // Filter function for timestamp corrections.
            $record['timestamp']->modify($this->offset);  // LST to UTC
            $record['timezone'] = 'UTC';
            return $record;
        }   
    }


# Buffers #

Like filters, Buffers allow for postprocessing of input from a Reader or 
preprocessing out output to a Wrtier. However, Buffers can operate on more than
one record at a time. Buffers can be used, for example, to split or merge
records before passing them on. Like Readers and Writers, Buffers support
filtering; records are filtered after they have passed through the Buffer.

## Input Buffering ##

An input Buffer is basically a Reader that reads records from another Reader
(including another input Buffer) instead of lines of text from a stream. An
input Buffer should derive from the `ReaderBuffer` base class. It must 
implement a `queue()` method to process each incoming record, and it may 
override the `uflow()` method to supply records once the input Reader has been
exhausted.

    class MonthlyTotal extends Serial_Core_ReaderBuffer
    {
        // Combine daily input into monthly totals.
         
        private $buffer = array();
        
        public function __construct($reader)
        {
            parent::__construct($reader);
            return;
        }
        
        protected function queue($record)
        {
            // Process each incoming record. The incoming data is assumed to be
            // sorted in chronological order.
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
                $this->buffer = null;  // next call will trigger EOF
            }
            else {
                // This function *must* throw StopIteration on EOF.
                throw new Serial_Core_StopIteration();
            }
            return;
        }
    }
    
    ...
    
    $monthly_records = iterator_to_array(new MonthlyTotal($reader));
    
## Output Buffering ##

An output Buffer is basically a Writer that writes records to another Writer
(including another output Buffer) instead of lines of text to a stream. An 
output buffer should derive from the `WriterBuffer` base class. It must 
implement a `queue()` method to process records being written to it, and it may
override the `flush()` method to finalize processing.
            
    class DataExpander extends Serial_Core_WriterBuffer
    {
        // Output individual elements of an array field.

        // In addition to the normal Writer interface, the base class defines
        // the close() method to be called by the client code to signal that no 
        // more records will be written. If multiple buffers are chained 
        // together their close() methods must be called in the correct order 
        // (outermost buffer first).

        public function __construct($writer)
        {
            parent::__construct($writer);
            return;
        }

        protected function queue($record)
        {
            // Process each outgoing record. 
            foreach ($record['data'] as $item) {
                // Each item in the record's array field will be output as an 
                // individual record.
                $item['stid'] = $record['stid'];
                $item['timestamp'] = $record['timestamp'];
                $this->output[] = $item;  // FIFO queue
            }
            return;
        }
            
        // WriterBuffer has a flush() method that can be overriden to finalize
        // output; is is called when close() is called on the buffer. For this 
        // example, flush() does not need to do anything.
    }

    ...
   
    $buffer = new DataExpander($writer);
    $buffer->dump($reader);  // dump() calls close()


# Tips and Tricks #

## Quoted Strings ##

The `StringField` type can read and write quoted string by initializing it with
the quote character to use.

    Serial_Core_StringField('name', 0, '%s', '"');  // double-quoted string

## Nonstandard Line Endings ##

By default, lines of text are assumed to end with the platform-specific line
ending, i.e. `PHP_EOL`. Readers expect that ending on each line of text from 
their input stream, and Writers append it to each line written to their output
stream.  If a Reader's input stream protocol uses a different line ending 
(as returned by `fgets()`), or Writer output is required to have a different 
ending, use the `endl` argument with the appropriate constructor.

    define("ENDL", "\r\r");  // Windows
    $writer = new Serial_Core_FixedWidthWriter($stream, $fields, ENDL);

## Header Data ##

Header data is outside the scope of `serial-core`. Client code is responsible
for reading or writing header data from or to the stream before the first
line is read or written. For derived classes this is typically done by the 
`__construct()` method.

## Mixed-Type Data ##

Mixed-type data fields must be defined as a `StringField` for stream input and
output, but filters can be used to convert to/from multiple PHP types based on
the field value.

    function missing_filter($record)
    {
        // Input filter for numeric data that's "M" if missing. The input
        // reader defines this field as a StringField.
        if (($value = $record['value']) == 'M') {
            $record['value'] = null;
        }
        else {
            $record['data'] = floatval($value);
        }
        return $record;
    }

## Combined Fields ##

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
[1]: http://www.php.net/manual/en/function.date.php "date formats"
[2]: http://www.php.net/manual/en/function.date-default-timezone-set.php "set time zone"
[3]: http://www.php.net/manual/en/function.sprintf.php "format strings"