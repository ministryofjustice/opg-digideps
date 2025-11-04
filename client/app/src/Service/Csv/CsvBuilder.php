<?php

declare(strict_types=1);

namespace App\Service\Csv;

class CsvBuilder
{
    /** @var resource|false */
    private $filePointer;

    public function buildCsv(array $headers, array $rows): string
    {
        $this->initialiseFilePointer();
        $this->addHeaders($headers);
        $this->addRows($rows);

        rewind($this->filePointer);
        $csvContent = stream_get_contents($this->filePointer);
        fclose($this->filePointer);

        return $csvContent;
    }

    /**
     * @throws \Exception
     */
    private function initialiseFilePointer(): void
    {
        $this->filePointer = fopen('php://temp/maxmemory:1048576', 'w');

        if (false === $this->filePointer) {
            throw new \Exception('Failed to open temporary file');
        }
    }

    private function addHeaders(array $headers): void
    {
        if (!empty($headers) && $this->filePointer) {
            fputcsv(stream: $this->filePointer, fields: $headers, escape: '\\');
        }
    }

    private function addRows(array $rows): void
    {
        if ($this->filePointer) {
            foreach ($rows as $row) {
                fputcsv(stream: $this->filePointer, fields: $row, escape: '\\');
            }
        }
    }
}
