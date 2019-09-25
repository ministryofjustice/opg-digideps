<?php declare(strict_types=1);


namespace AppBundle\Model;


use AppBundle\Entity\Report\ReportSubmission;

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

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return ReportSubmission
     */
    public function getReportSubmission(): ReportSubmission
    {
        return $this->reportSubmission;
    }

    /**
     * @param ReportSubmission $reportSubmission
     */
    public function setReportSubmission(ReportSubmission $reportSubmission): void
    {
        $this->reportSubmission = $reportSubmission;
    }
}
