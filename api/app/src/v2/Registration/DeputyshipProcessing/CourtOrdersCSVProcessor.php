<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use League\Csv\Reader;

class CSVProcessingResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
    ) {
    }
}

class CourtOrdersCSVProcessor
{
    /**
     * Process the CSV file through the reader $csvFile.
     */
    public function processCsv(Reader $csvFile): CSVProcessingResult
    {
        return new CSVProcessingResult(success: true, message: "CSV {$csvFile->getPathname()} processed");
    }
}
