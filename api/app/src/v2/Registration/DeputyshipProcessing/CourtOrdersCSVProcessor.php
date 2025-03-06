<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

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
    public function __construct(
    ) {
    }

    /**
     * Process the CSV file at $fileLocation.
     */
    public function processFile(string $fileLocation): CSVProcessingResult
    {
        return new CSVProcessingResult(success: true, message: "CSV $fileLocation processed");
    }
}
