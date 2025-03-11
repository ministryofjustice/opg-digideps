<?php

declare(strict_types=1);

namespace App\v2\CSV;

class CSVChunker
{
    public function __construct(
        private readonly \Iterator $csvFile,
        private readonly int $chunkSize,
    ) {
    }

    /**
     * Return an array of CSV rows cast to $entityClass.
     *
     * Returns null if there are no more records available.
     */
    public function getChunk(): ?array
    {
        if (!$this->csvFile->valid()) {
            return null;
        }

        $records = [];
        $count = 1;

        while ($count <= $this->chunkSize && $this->csvFile->valid()) {
            ++$count;
            $records[] = $this->csvFile->current();
            $this->csvFile->next();
        }

        return $records;
    }
}
