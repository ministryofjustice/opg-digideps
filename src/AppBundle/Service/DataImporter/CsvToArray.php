<?php

namespace AppBundle\Service\DataImporter;

class CsvToArray
{
    const DELIMITER = ',';
    const ENCLOSURE = '"';
    const ESCAPE = '\\';
    const CHAR_LIMIT_PER_ROW = 2000; // current average is around the 300-400 chars

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var array
     */
    private $expectedColumns = [];


    /**
     * Columns that we definitely dont expect.
     * (those that are present that would indicate the wrong CSV is being used)
     *
     * @var array
     */
    private $unexpectedColumns = [];

    /**
     * @var array
     */
    private $optionalColumns = [];

    /**
     * @var bool
     */
    private $normaliseNewLines;

    private $firstRow = [];

    /**
     * CsvToArray constructor.
     *
     * @param $file
     * @param $normaliseNewLines
     * @param bool $autoDetectLineEndings - setup to maintain compatibility with other code that uses this class
     *
     * @throws \RuntimeException
     */
    public function __construct($file, $normaliseNewLines, $autoDetectLineEndings = false)
    {
        $this->normaliseNewLines = $normaliseNewLines;

        if (!file_exists($file)) {
            throw new \RuntimeException("file $file not found");
        }

        // if line endings need to be normalised, the stream is replaced with a string stream with the content replaced
        if ($this->normaliseNewLines) {
            $content = str_replace(["\r\n", "\r"], ["\n", "\n"], file_get_contents($file));
            $this->handle = fopen('data://text/plain,' . $content, 'r');
        } else {
            ini_set('auto_detect_line_endings', true);
            $openMode = $autoDetectLineEndings ? 'rb' : 'r';
            $this->handle = fopen($file, $openMode);
        }
    }

    public function setExpectedColumns(array $expectedColumns)
    {
        $this->expectedColumns = $expectedColumns;

        return $this;
    }

    public function setUnexpectedColumns(array $unexpectedColumns)
    {
        $this->unexpectedColumns = $unexpectedColumns;

        return $this;
    }

    public function setOptionalColumns(array $optionalColumns)
    {
        $this->optionalColumns = $optionalColumns;

        return $this;
    }

    /**
     * @return array or false when EOF
     */
    private function getRow()
    {
        return fgetcsv($this->handle, self::CHAR_LIMIT_PER_ROW, self::DELIMITER, self::ENCLOSURE, self::ESCAPE);
    }

    /**
     * @return array
     */
    public function getFirstRow()
    {
        if (empty($this->firstRow)) {
            $this->firstRow = $this->getRow();
        }

        return $this->firstRow;
    }

    /**
     * Returns.
     *
     * @return array
     */
    public function getData()
    {
        $ret = [];

        // parse header
        $header = $this->getFirstRow();
        if (!$header) {
            throw new \RuntimeException('Empty or corrupted file, cannot parse CSV header');
        }
        $missingColumns = array_diff($this->expectedColumns, $header);
        if ($missingColumns) {
            throw new \RuntimeException('Invalid file. Cannot find expected header columns: ' . implode(', ', $missingColumns));
        }

        $rogueColumns = array_intersect($header, $this->unexpectedColumns);
        if (!empty($rogueColumns)) {
            throw new \RuntimeException('Invalid file. File contains unexpected header columns: ' . implode(', ', $rogueColumns));
        }

        // read rows
        $rowNumber = 1;
        while (($row = $this->getRow()) !== false) {
            $rowNumber++;
            $rowArray = [];
            foreach ($this->expectedColumns as $expectedColumn) {
                $index = array_search($expectedColumn, $header);
                if ($index !== false) {
                    if (!array_key_exists($index, $row)) {
                        throw new \RuntimeException("Can't find $expectedColumn column in line $rowNumber");
                    }
                    $rowArray[$expectedColumn] = $row[$index];
                }
            }
            foreach ($this->optionalColumns as $optionalColumn) {
                $index = array_search($optionalColumn, $header);
                if ($index !== false) {
                    // fix for CSV with last two columns being empty and not having commas
                    if (!isset($row[$index])) {
                        $row[$index] = '';
                    }
                    $rowArray[$optionalColumn] = $row[$index];
                }
            }
            $ret[] = $rowArray;
        }


        return $ret;
    }

    public function __destruct()
    {
        fclose($this->handle);

        if ($this->normaliseNewLines) {
            ini_set('auto_detect_line_endings', false);
        }
    }
}
