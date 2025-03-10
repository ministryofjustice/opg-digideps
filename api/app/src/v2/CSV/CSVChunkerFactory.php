<?php

declare(strict_types=1);

namespace App\v2\CSV;

use League\Csv\Exception as CSVException;
use League\Csv\Reader;
use League\Csv\UnavailableStream;

class CSVChunkerFactory
{
    /**
     * @throws UnavailableStream
     * @throws CSVException
     */
    public function create(string $fileLocation, string $entityClass, int $chunkSize = 10000): CSVChunker
    {
        $reader = Reader::createFromPath($fileLocation);
        $csvFile = $reader->getRecordsAsObject($entityClass);

        return new CSVChunker($csvFile, $chunkSize);
    }
}
