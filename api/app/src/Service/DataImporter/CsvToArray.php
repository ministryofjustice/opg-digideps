<?php

namespace App\Service\DataImporter;

use RuntimeException;

class CsvToArray
{
    const DELIMITER = ',';
    const ENCLOSURE = '"';
    const ESCAPE = '\\';
    const CHAR_LIMIT_PER_ROW = 2000; // current average is around the 300-400 chars

    /**
     * @var resource|false
     */
    private $handle;

    /**
     * @var array
     */
    private array $expectedColumns = [];

    /**
     * Columns that we definitely dont expect.
     * (those that are present that would indicate the wrong CSV is being used).
     */
    private array $unexpectedColumns = [];

    private array $optionalColumns = [];

    private array $firstRow = [];

    public function __construct(string $file, bool $normaliseNewLines)
    {
        $this->normaliseNewLines = $normaliseNewLines;

        if (!file_exists($file)) {
            throw new RuntimeException("file $file not found");
        }

        $this->handle = fopen($file, 'r');
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
     * @return array|false|null returns false when EOF
     */
    private function getRow(): array|false|null
    {
        if (!empty($this->handle)) {
            return fgetcsv($this->handle, self::CHAR_LIMIT_PER_ROW, self::DELIMITER, self::ENCLOSURE, self::ESCAPE);
        }

        throw new RuntimeException('Resource handle empty');
    }

    /**
     * @return array
     */
    public function getFirstRow(): array
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
    public function getData(): array
    {
        $ret = [];

        // parse header
        $header = $this->getFirstRow();
        if (!$header) {
            throw new RuntimeException('Empty or corrupted file, cannot parse CSV header');
        }
       $missingColumns = array_diff($this->expectedColumns, $header);
       if ($missingColumns) {
           throw new RuntimeException('Invalid file. Cannot find expected header columns: '.implode(', ', $missingColumns));
       }

        $rogueColumns = array_intersect($header, $this->unexpectedColumns);
        if (!empty($rogueColumns)) {
            throw new RuntimeException('Invalid file. File contains unexpected header columns: '.implode(', ', $rogueColumns));
        }

        // read rows
        $rowNumber = 1;
        while (($row = $this->getRow()) !== false) {
            ++$rowNumber;
            $rowArray = [];
            foreach ($this->expectedColumns as $expectedColumn) {
                if (empty($header)) {
                    throw new RuntimeException('Empty header in CSV file');
                }
                $index = array_search($expectedColumn, $header);
                if (false !== $index && !empty($row)) {
                    if (!array_key_exists($index, $row)) {
                        throw new RuntimeException("Can't find $expectedColumn column in line $rowNumber");
                    }
                    $rowArray[$expectedColumn] = $row[$index];
                }
            }
            foreach ($this->optionalColumns as $optionalColumn) {
                $index = array_search($optionalColumn, $header);
                if (false !== $index) {
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
        if (false !== $this->handle) {
            fclose($this->handle);
        }
    }
}
