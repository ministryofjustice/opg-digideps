<?php

declare(strict_types=1);

namespace App\v2\CSV;

class CSVChunker
{
    private \Iterator $csvFile;
    private int $chunkSize;

    public function __construct(
        \Iterator $csvFile,
        int $chunkSize,
    ) {
        $this->csvFile = $csvFile;
        $this->chunkSize = $chunkSize;
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
        $count = 0;

        while ($count <= $this->chunkSize && $this->csvFile->valid()) {
            ++$count;
            $records[] = $this->csvFile->current();
            $this->csvFile->next();
        }

        return $records;
    }
}
