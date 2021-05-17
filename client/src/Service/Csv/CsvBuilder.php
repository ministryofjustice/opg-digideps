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
        if (!empty($headers)) {
            fputcsv($this->filePointer, $headers);
        }
    }

    private function addRows(array $rows): void
    {
        foreach ($rows as $row) {
            fputcsv($this->filePointer, $row);
        }
    }
}
