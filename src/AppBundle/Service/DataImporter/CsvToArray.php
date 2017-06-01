<?php

namespace AppBundle\Service\DataImporter;

class CsvToArray
{
    const DELIMITER = ',';
    const ENCLOSURE = '"';
    const ESCAPE = '\\';

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var array
     */
    private $expectedColumns = [];

    /**
     * @var array
     */
    private $optionalColumns = [];

    /**
     * @var bool
     */
    private $normaliseNewLines;

    /**
     * @param string $file              path to file
     * @param array  $expectedColumns   e.g. ['Case','Surname', 'Deputy No' ...]
     * @param bool   $normaliseNewLines
     *
     * @throws \RuntimeException
     */
    public function __construct($file, $normaliseNewLines)
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
            $this->handle = fopen($file, 'r');
        }
    }

    public function setExpectedColumns(array $expectedColumns)
    {
        $this->expectedColumns = $expectedColumns;

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
        return fgetcsv($this->handle, 2000, self::DELIMITER, self::ENCLOSURE, self::ESCAPE);
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
        $header = $this->getRow();
        if (!$header) {
            throw new \RuntimeException('Empty or corrupted file, cannot parse CSV header');
        }
        $missingColumns = array_diff($this->expectedColumns, $header);
        if ($missingColumns) {
            throw new \RuntimeException('Invalid file. Cannot find header columns ' . implode(', ', $missingColumns));
        }

        // read rows
        $rowNumber = 0;
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
