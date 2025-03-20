<?php

namespace App\Transformer\ReportSubmission;

use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Service\DateTimeProvider;

class ReportSubmissionSummaryTransformer
{
    public function __construct(private readonly DateTimeProvider $dateTimeProvider)
    {
    }

    /**
     * @return array
     */
    public function transform(array $reportSubmissions)
    {
        $ret = [];

        foreach ($reportSubmissions as $reportSubmission) {
            $ret[] = $this->generateDataRow($reportSubmission);
        }

        return array_filter($ret);
    }

    /**
     * @return array|null
     */
    private function generateDataRow(ReportSubmission $reportSubmission)
    {
        if (null === $reportSubmission->getReport() && null === $reportSubmission->getNdr()) {
            return null;
        }

        $report = (null !== $reportSubmission->getReport()) ?
            $reportSubmission->getReport() :
            $reportSubmission->getNdr();

        $data = [];
        $data['id'] = $this->getId($reportSubmission);
        $data['case_number'] = $this->getCaseNumber($report);
        $data['date_received'] = $this->getDateReceived($reportSubmission);
        $data['scan_date'] = $this->getScanDate();
        $data['document_id'] = $this->getDocumentId($reportSubmission);
        $data['document_type'] = $this->getReportType();
        $data['form_type'] = $this->getFormType();

        return $data;
    }

    /**
     * @return int
     */
    private function getId(ReportSubmission $reportSubmission)
    {
        return $reportSubmission->getId();
    }

    /**
     * @return string
     */
    private function getCaseNumber(ReportInterface $report)
    {
        return $report->getClient()->getCaseNumber();
    }

    /**
     * @return string|null
     */
    private function getDateReceived(ReportSubmission $reportSubmission)
    {
        return $this->outputDate($reportSubmission->getCreatedOn());
    }

    /**
     * @return string|null
     */
    private function getScanDate()
    {
        return $this->outputDate($this->dateTimeProvider->getDateTime());
    }

    /**
     * @return string|null
     */
    private function getDocumentId(ReportSubmission $reportSubmission)
    {
        foreach ($reportSubmission->getDocuments() as $document) {
            if ($document->isReportPdf() && '.pdf' === substr($document->getFileName(), -4)) {
                return $document->getFileName();
            }
        }
    }

    /**
     * @return string
     */
    private function getReportType()
    {
        return 'Reports';
    }

    /**
     * @return string
     */
    private function getFormType()
    {
        return 'Reports General';
    }

    /**
     * @return string|null
     */
    private function outputDate($date)
    {
        return ($date instanceof \DateTime) ? $date->format('Y-m-d') : null;
    }
}
