<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Report\ReportSubmission;

class MissingDocument
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var ReportSubmission
     */
    private $reportSubmission;

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getReportSubmission(): ReportSubmission
    {
        return $this->reportSubmission;
    }

    public function setReportSubmission(ReportSubmission $reportSubmission): void
    {
        $this->reportSubmission = $reportSubmission;
    }
}
