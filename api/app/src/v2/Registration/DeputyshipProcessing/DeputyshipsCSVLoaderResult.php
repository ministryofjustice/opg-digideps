<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipsCSVLoaderResult
{
    public function __construct(
        public string $fileLocation,
        public bool $loadedOk,
        public int $numRecords = 0,
    ) {
    }
}
