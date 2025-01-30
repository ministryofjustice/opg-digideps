<?php

namespace App\Service\DataImporter;

use RuntimeException;

class CsvToArray
{
    const DELIMITER = ',';
    const ENCLOSURE = '"';
    const ESCAPE = '\\';
    const CHAR_LIMIT_PER_ROW = 2000; // current average is around the 300-400 chars

    public function __construct(private readonly array $expectedColumns = []) 
    {
    }

    /**
     * @return array|false|null returns false when EOF
     */
    private function getRow($handle): array|false|null
    {
        return fgetcsv($handle, self::CHAR_LIMIT_PER_ROW, self::DELIMITER, self::ENCLOSURE, self::ESCAPE);
    }

    /**
     * Returns.
     *
     * @return array
     */
    public function create(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("file $filename not found");
        }

        $handle = fopen($filename, 'r');

        // parse header
        $header = $this->getRow($handle);
        
        if (!$header) {
            $this->closeFile($handle);
            throw new RuntimeException('Empty or corrupted file, cannot parse CSV header');
        }

        $ret = [];
        while (($row = $this->getRow($handle)) !== false) {
            $rowArray = [];
            foreach ($row as $i => $data) {
                $index = $header[$i];
                if (!array_key_exists($index, $this->expectedColumns)) {
                    continue;
                }

                $rowArray[$index] = $data;
            }

            $ret[] = $rowArray;
        }

        $this->closeFile($handle);
        return $ret;
    }

    private function closeFile($handle)
    {
        if (false !== $handle) {
            fclose($handle);
        }
    }
}
