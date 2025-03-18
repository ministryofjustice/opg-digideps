<?php

declare(strict_types=1);

namespace App\v2\CSV;

use League\Csv\Exception as CSVException;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class CSVChunkerFactory
{
    /**
     * Create a CSV read which reads records in a series of chunks.
     *
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @throws UnavailableStream
     * @throws CSVException
     */
    public function create(string $fileLocation, string $entityClass, int $chunkSize = 10000): CSVChunker
    {
        $reader = Reader::createFromPath($fileLocation);
        $reader->setHeaderOffset(0);
        $csvFile = $reader->getRecordsAsObject($entityClass);

        // because the header is in row 0, the first element in $csvFile has an index of 1, so
        // we have to move the pointer to that element in the iterator (feels like a bug in League\Csv to me...)
        $csvFile->rewind();

        return new CSVChunker($csvFile, $chunkSize);
    }
}
