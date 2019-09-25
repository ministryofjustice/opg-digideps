<?php declare(strict_types=1);


namespace AppBundle\Model;


use AppBundle\Entity\Report\ReportSubmission;

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

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

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
