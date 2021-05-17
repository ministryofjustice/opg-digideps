<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Report\ReportSubmission;

class RetrievedDocument
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var ReportSubmission
     */
    private $reportSubmission;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

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
