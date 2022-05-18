<?php

namespace App\Message\Command;

class UploadCsv
{
    public function __construct(private string $csvType)
    {
    }

    public function getCsvType(): string
    {
        return $this->csvType;
    }

    public function setCsvType(string $csvType): void
    {
        $this->csvType = $csvType;
    }
}
