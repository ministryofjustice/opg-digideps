<?php

namespace OPG\Digideps\Backend\Service\DataImporter;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class CsvToArray
{
    public function __construct(
        private readonly array $expectedColumns = [],
        private readonly array $optionalColumns = [],
    ) {}

    private function keepExpectedAndOptionalColumns(array $record): array
    {
        $trimmedRecord = [];

        foreach ($record as $column => $value) {
            if (in_array($column, $this->expectedColumns) || in_array($column, $this->optionalColumns)) {
                $trimmedRecord[$column] = $value;
            }
        }

        return $trimmedRecord;
    }

    /**
     * Convert CSV into an array of associative arrays in format [fieldname => fieldvalue, ...].
     * If the CSV header is missing any expected column, an exception is thrown.
     * The returned arrays only contain expected and optional columns.
     */
    public function create(string $filename): array
    {
        return iterator_to_array($this->createAsIterator($filename));
    }

    /**
     * See the create method for further documentation.
     *
     * @return \Iterator<int, array>
     */
    public function createAsIterator(string $filename): \Iterator
    {
        try {
            /**
             * @var Reader<array> $reader
             */
            $reader = Reader::from($filename);
            $reader->setHeaderOffset(0);
        } catch (UnavailableStream) {
            throw new \RuntimeException("file {$filename} not found");
        }

        // check for all expected columns
        if (!empty(array_diff($this->expectedColumns, $reader->getHeader()))) {
            throw new \RuntimeException("CSV file $filename does not contain all expected columns in header");
        }

        try {
            $records = $reader->getRecords();
        } catch (Exception) {
            throw new \RuntimeException('Malformed row within file or invalid CSV');
        }

        foreach ($records as $record) {
            // only keep expected and optional columns
            yield $this->keepExpectedAndOptionalColumns($record);
        }
    }
}
