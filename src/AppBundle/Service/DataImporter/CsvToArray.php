<?php

namespace AppBundle\Service\DataImporter;

class CsvToArray
{
    const DELIMITER = ',';
    const ENCLOSURE = '"';
    const ESCAPE = "\\";

    private $handle;
    private $expectedColumns = [];

    /**
     * @param string $file path to file
     * @param array $expectedColumns e.g. ['Case','Surname', 'Deputy No', 'Dep Surname', 'Dep Postcode']
     * @throws \RuntimeException
     */
    public function __construct($file)
    {
        ini_set('auto_detect_line_endings', true);

        if (!file_exists($file)) {
            throw new \RuntimeException("file $file not found");
        }
        $this->handle = fopen($file, 'r');
    }
    
    public function setExpectedColumns(array $expectedColumns)
    {
        $this->expectedColumns = $expectedColumns;
        
        return $this;
    }

    
    /**
     * @return array or false when EOF
     */
    private function getRow()
    {
        return fgetcsv($this->handle, 2000, self::DELIMITER, self::ENCLOSURE, self::ESCAPE);
    }


    /**
     * Returns 
     * 
     * @return array
     */
    public function getData()
    {
        $ret = [];

        // parse header
        $header = $this->getRow();
        if (!$header) {
            throw new \RuntimeException('Empty or corrupted file, cannot parse CSV header');
        }
        $missingColumns = array_diff($this->expectedColumns, $header);
        if ($missingColumns) {
            throw new \RuntimeException('Missing Header columns: ' . implode(', ', $missingColumns));
        }

        // read rows
        while (($row = $this->getRow()) !== false) {
            $ret[] = array_combine($header, $row);
        }

        return $ret;
    }


    public function __destruct()
    {
        fclose($this->handle);
        ini_set('auto_detect_line_endings', false);
    }

}