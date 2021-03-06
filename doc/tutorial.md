# Overview #

The **Serial\Core** library can be used to read and write serial data
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
initialized with an array of field definitions. Each field is associated with
a data type and defined by its name and position. For a fixed-width field the
position array is a substring specifier (beg, len), inclusive of any spaces 
between fields.

    use Serial\Core;
    
    $fields = array(
        // Ignoring time zone field. 
        new StringField('stid', array(0, 6)),
        new StringField('date', array(6, 11)),
        new StringField('time', array(17, 6)),
        new FloatField('data1', array(27, 8))),
        new StringField('flag1', array(35, 1)),
        new FloatField('data2' array(36, 8)),
        new StringField('flag2', array(44, 1)),
    );

    $reader = FixedWidthReader::open('data.txt', $fields);
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
        new FloatField('value', array(0, 8)),
        new Serial_Core_Stringfield('flag', array(8, 1)),
    );

    $sample_fields = array(
        // Ignoring time zone field.
        new StringField('stid', array(0, 6)),
        new StringField('date', array(6, 11)),
        new StringField('time', arraay(17, 6)),
        new ArrayField('data', array(27, 18), $array_fields)),
    );

    $reader = FixedWidthReader::open('data.txt', $fields);
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
        new StringField('stid', array(0, 6)),
        new StringField('date', array(6, 11)),
        new StringField('time', arraay(17, 6)),
        new ArrayField('data', array(27, null), $array_fields)),
    );

    ...

    foreach ($reader as $record) {
        echo 'total sensors: '.count($record['data']).PHP_EOL;
        foreach ($record['data'] as $sensor) {
            echo $sensor['value'].', '.$sensor['flag'].PHP_EOL;
        }
    }

## DateTime Fields ##

A `DateTimeField` can be used for converting data to a [`DateTime`][1] object. 
For the sample data, the date and time fields can be treated as a combined 
`DateTime` field. Make sure the default [time zone][2] has been set before 
using any PHP date/time functions or classes.

A `DatetimeField` must be initialized with a PHP [date format string][3] that
will be used to format output. Due to PHP 5.2 limitations, this format is not 
used to parse input; *all date/time input must be compatible with the DateTime 
constructor*.

    date_default_timezone_set('UTC');
    $sample_fields = array(
        // Ignoring time zone field.
        new StringField('stid', array(0, 6)),
        new DateTimeField('timestamp', array(6, 17), 'Y-m-d H:i'),
        new ArrayField('data', array(27, 18), $array_fields)),
    );

## Default Values ##

During input all fields in a record are assigned a value. If a field is blank
it is given the default value assigned to that field (`null` by default). The 
default value should be appropriate to that type, *e.g.* an `IntField` field 
should not have a string as its default value.

    $array_fields = array(
        // Assign 'M' to all missing flag values.
        new FloatField('value', array(0, 8)),
        new Serial_Core_Stringfield('flag', array(8, 1), '%1s', '', 'M'),
    );


# Writing Data #

Data is written to a stream using a Writer. Writers implement a `write()` 
method for writing individual records and a `dump()` method for writing a 
sequence of records. Writers use the same field definitions as Readers with
some additional requirements.

With some minor modifications the field definitions for reading the sample data
can be used for writing it. In fact, the modified fields can still be used for
reading the data, so a Reader and a Writer can be defined for a given data
format using one set of field definitions.

    $array_fields = array(
        new FloatField('value', array(0, 8), '%8.2f'),
        new Serial_Core_Stringfield('flag', array(8, 1), '%1s'),
    );

    $sample_fields = array(
        new StringField('stid', array(0, 6), '%7s'),
        new DateTimeField('timestamp', array(6, 17), 'Y-m-d H:i'),
        new StringField('timezone', array(23, 4), '%4s'),
        new ArrayField('data', array(27, null), $array_fields)),
    );

    // Copy 'data.txt' to 'copy.txt'.
    $reader = FixedWidthReader::open('data.txt', $sample_fields);
    $writer = FixedWidthWriter::open('copy.txt', $sample_fields);
    foreach ($reader as $record) {
        // Write each record to the stream.
        $writer->write($record);
    }
    // Or, write all records in a single call: $writer->dump($reader)

## Output Formatting ##

Each field is formatted for output according to its [format string][4]. For
fixed-width output values are fit to the allotted fields widths by padding on
the left or trimming on the right. By using a format width, values can be
positioned within the field. Use a format width smaller than the field width to
specify a left margin and control spacing between field values

    $fields = array(
        new StringField('stid', array(0, 6)),
        new FloatField('value', array(6, 8), '%7.2f'),  // left margin
        ...
    );
    
## Default Values ##
 
For every output record a Writer will write a value for each defined field. If 
a field is missing from a record the Writer will use the default value for that
field (`null` is encoded as a blank field). Default output values should be 
type-compatible, e.g. ''M'' would be output as ''0'' for an `IntField`, which 
is probably not what is wanted.

    

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
        new FloatField('value', 0, '%.2f'),  // precision only
        new Serial_Core_Stringfield('flag', 1, '%1s'),
    );

    $sample_fields = array(
        new StringField('stid', 0),
        new DateTimeField('timestamp', 1, 'Y-m-d H:i'),  // format required
        new StringField('timezone', 2),
        new ArrayField('data', array(3, null), $array_fields)),
    );

    ...
    
    $delim = ',';
    $reader = DelimitedReader::open($istream, $sample_fields, $delim);
    $writer = DelimitedWriter::open($ostream, $sample_fields, $delim);


# Initializing Readers and Writers #

For most situations, calling the class's static `open()` method is the most 
convenient way to create a Reader or Writer. The stream associated with the new
obect will automatically be closed once the Reader or Writer becomes undefined.

    $reader = DelimitedReader::open('data.csv', $fields, ',');
    ...
    unset($reader);  // closes input file

If a string is passed to `open()` it is interpreted as a path to be opened as
a plain text file. If another type of stream is needed, open the stream 
explicitly and pass it to `open()`; this stream will be automatically closed.

    $stream = fopen('compress.zlib://data.csv.gz', 'r');
    $reader = neSerial_Core_DelimitedReader::open($stream, $fields, ',');
    ...
    unset($reader);  // closes $stream

Calling a Reader or Writer constructor directly provides the most control. The
client code is responsible for opening and closing the associated stream. The
constructor takes the same arguments as `open()`, except that the constructor
requires an open stream instead of an optional file path.
    
    $stream = fopen('compress.zlib://data.csv.gz', 'r');
    $reader = new DelimitedReader($stream, $fields, ',');
    ...
    unset($reader);  // $stream is still open
    fclose($stream);


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
                throw new StopIteration();
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
    $whitelist = new FieldFilter('color', array('crimson', 'cream'));
    
    // Drop all records where the color field is orange.
    $blacklist = new FieldFilter('color', array('orange'), true);

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
    $prcp_filter = new SubstrFilter(array(21, 4), array('PRCP'));
    
    $stream = fopen('data.txt', 'r');
    StreamFilterManager::attach($stream, 'comment_filter');
    StreamFilterManager::attach($stream, $prcp_filter);
    $reader = FixedWidthReader::open($stream, $fields);


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
class filters before any user filters, but Writers apply them after any user 
filters. Class filters are not affected by the `filter()` method; instead, 
add them using the `classFilter()` method.

    /** 
     * Module for reading and writing the sample data format.
     *
     */
    require 'Serial/Core.php';

    define('SAMPLE_DELIM' ',');

    $SAMPLE_FIELDS = array(
        new StringField('stid', 0),
        new DateTimeField('timestamp', 1, 'Y-m-d H:i'),
        new ConstField('timezone', 'UTC'),
        new ArrayField('data', array(3, null), array(
            new FloatField('value', 0, '%.2f'),
            new Serial_Core_Stringfield('flag', 1, '%1s'),
        )),
    );


    /**
     * Sample data reader.
     *
     * The base class implements the Iterator interface for reading records.
     * All times are converted from UTC to LST on input.
     */
    class SampleReader extends DelimitedReader
    {
        // Base class implements the Iterator interface for reading records.
        
        private $offset;
        
        public function __construct($offset=-360)
        {
            global $SAMPLE_FIELDS, $DELIM;
            parent::__construct($SAMPLE_FIELDS, SAMPLE_DELIM);
            $this->offset = "{$offset} minutes";  // offset from UTC
            $this->classFilter(array($this, 'timestampFilter')); 
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
    class SampleWriter extends DelimitedWriter
    {
        // Base class defines write() for writing an individual record and
        // dump() for writing all records in a sequence.
        
        private $offset;
        
        public function __construct($offset=-360)
        {
            global $SAMPLE_FIELDS, $DELIM;
            parent::__construct($SAMPLE_FIELDS, SAMPLE_DELIM);
            $this->offset = -$offset.' minutes';  // offset from LST to UTC
            $this->classFilter(array($this, 'timestampFilter')); 
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
records before passing them on. Because Buffers are Readers or Writers
themselves, they can be chained and filtered.

 

## Aggregate Input ##

Data aggregation refers to grouping data records and then applying reductions 
(e.g. sum or mean) to the grouped data. An `AggregateReader` is a Buffer that 
can be used to aggregate data from another Reader. Aggregation relies on a key 
function. Incoming records with the same key value are grouped together 
(records are assumed to be sorted such that all records in the same group are 
contiguous), then one or more reduction functions are applied to each group of 
records to yield a single aggregate record consisting of the key values and the
reduced values. 

A key can be a single field name, an array of names, or a function. In the 
first two cases a key function will be automatically generated. A key function 
takes a single record as its argument and returns the values of one or more key 
fields as an associative array. A custom key function is free to create key 
fields that are not in the incoming data.

A reduction function takes a sequence of records as an argument and returns 
an associative array of reduced values. The `CallbackReduction` class can be
used to create reductions from basic functions like the `array_sum()` built-in.
Reduction functions are free to create reduction fields that are not in the 
incoming data.

Reductions are chained in the order they are added to the Reader, and the 
results are merged with the key fields to create a single aggregate record. If 
more than one reduction returns the same field name the latter value will 
overwrite the existing value. Fields in the input data that do not have a 
reduction defined for them will not be in the aggregate record.

    // Aggregate input by site. Input should already be sorted by site 
    // identifier. Each aggregate record will have the sum of all 'data' values 
    // for a given site.
    $reader = new AggregateReader($reader, 'stid');  // auto key function
    $reader->reduce(new CallbackReduction('array_sum', 'data'));
    $summed = iterator_to_array($reader);

    
## Aggregate Output ##

An `AggregateWriter` writes data to another Writer, but otherwise functions
like an `AggregateReader`. The `close()` method must be called to ensure that
all records get written to the destination writer.

    // Write monthly records by site. The records being written should already
    // be sorted by date and site. Each aggregate record will have the mean of
    // all 'data' values for a given site and month.
    
    function key($record)
    {
        // Group records by site and month. This is an example of how a custom
        // key function should be implemented.
        month = $record['timestamp']->format('Ym');
        return array('month' => month, 'stid' => $record['stid']);
    }
    
    function mean($records)
    {
        // Calculate the mean value of the the 'data' field. This is an example
        // of how a custom reduction function should be implemented.
        if ($len = count($records)) {
            $sum = 0;
            foreach ($records as $record) {
                $sum += $record['data']
            }
            $mean = $sum / $len;
        }
        else {
            $mean = null;
        }
        return array('mean' => $mean);
    }
    
    $writer = new AggregateWriter($writer, $key);
    $writer->reduce('mean');
    $writer->dump($records);  // dump() calls close()


# Tips and Tricks #

## Quoted Strings ##

The `StringField` type can read and write quoted string by initializing it with
the quote character to use.

    StringField('name', 0, '%s', '"');  // double-quoted string

## Nonstandard Line Endings ##

By default, lines of text are assumed to end with the platform-specific line
ending, i.e. `PHP_EOL`. Readers expect that ending on each line of text from 
their input stream, and Writers append it to each line written to their output
stream.  If a Reader's input stream protocol uses a different line ending 
(as returned by `fgets()`), or Writer output is required to have a different 
ending, use the `endl` argument with the appropriate constructor.

    define('ENDL', "\r\r");  // Windows
    $writer = FixedWidthWriter::open('data.txt', $fields, ENDL);

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
[1]: http://www.php.net/manual/en/class.datetime.php "DateTime class"
[2]: http://www.php.net/manual/en/function.date-default-timezone-set.php "set time zone"
[3]: http://www.php.net/manual/en/function.date.php "date formats"
[4]: http://www.php.net/manual/en/function.sprintf.php "format strings"