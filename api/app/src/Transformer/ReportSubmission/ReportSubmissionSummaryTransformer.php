<?php

namespace OPG\Digideps\Backend\Transformer\ReportSubmission;

use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\DateTimeProvider;

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

    private function generateDataRow(ReportSubmission $reportSubmission): array
    {
        return [
            'id' => $this->getId($reportSubmission),
            'case_number' => $this->getCaseNumber($reportSubmission->getReport()),
            'date_received' => $this->getDateReceived($reportSubmission),
            'scan_date' => $this->getScanDate(),
            'document_id' => $this->getDocumentId($reportSubmission),
            'document_type' => $this->getReportType(),
            'form_type' => $this->getFormType(),
        ];
    }

    /**
     * @return int
     */
    private function getId(ReportSubmission $reportSubmission)
    {
        return $reportSubmission->getId();
    }

    private function getCaseNumber(?Report $report): string
    {
        return $report?->getClient()->getCaseNumber() ?? '';
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
