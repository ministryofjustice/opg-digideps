<?php

namespace OPG\Digideps\Backend\Transformer\ReportSubmission;

use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\DateTimeProvider;

class ReportSubmissionSummaryTransformer
{
    public function __construct(private readonly DateTimeProvider $dateTimeProvider) {}

    public function transform(array $reportSubmissions): array
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

    private function getId(ReportSubmission $reportSubmission): int
    {
        return $reportSubmission->getId();
    }

    private function getCaseNumber(?Report $report): string
    {
        return $report?->getClient()->getCaseNumber() ?? '';
    }

    private function getDateReceived(ReportSubmission $reportSubmission): ?string
    {
        return $this->outputDate($reportSubmission->getCreatedOn());
    }

    private function getScanDate(): ?string
    {
        return $this->outputDate($this->dateTimeProvider->getDateTime());
    }

    private function getDocumentId(ReportSubmission $reportSubmission): ?string
    {
        foreach ($reportSubmission->getDocuments() as $document) {
            if ($document->isReportPdf() && substr($document->getFileName(), -4) === '.pdf') {
                return $document->getFileName();
            }
        }
        return null;
    }

    private function getReportType(): string
    {
        return 'Reports';
    }

    private function getFormType(): string
    {
        return 'Reports General';
    }

    private function outputDate(?\DateTime $date): ?string
    {
        return ($date instanceof \DateTime) ? $date->format('Y-m-d') : null;
    }
}
