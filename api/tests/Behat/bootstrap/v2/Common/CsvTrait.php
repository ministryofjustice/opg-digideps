<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait CsvTrait
{
    public function transformCsvRowsToArray(string $csvFilePath): array
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$csvFilePath;
            if (is_file($fullPath)) {
                $csvFilePath = $fullPath;
            }
        }

        $csvRows = array_map('str_getcsv', file($csvFilePath));
        array_walk($csvRows, function (&$a) use ($csvRows) {
            $a = array_combine($csvRows[0], $a);
        });

        array_shift($csvRows); // remove column header

        return $csvRows;
    }
}
