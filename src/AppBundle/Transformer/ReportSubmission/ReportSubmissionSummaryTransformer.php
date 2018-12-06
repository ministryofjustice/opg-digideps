<?php

namespace AppBundle\Transformer\ReportSubmission;

use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\DateTimeProvider;

class ReportSubmissionSummaryTransformer
{
    /** @var DateTimeProvider  */
    private $dateTimeProvider;

    /**
     * @param DateTimeProvider $dateTimeProvider
     */
    public function __construct(DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @param array $reportSubmissions
     * @return array
     */
    public function transform(array $reportSubmissions)
    {
        $ret = [];
        $ret[] = $this->getHeaderLine();

        foreach ($reportSubmissions as $reportSubmission) {
            $ret[] = $this->generateDataRow($reportSubmission);
        }

        return array_filter($ret);
    }

    /**
     * @return array
     */
    private function getHeaderLine()
    {
        return ['case_number', 'date_received', 'scan_date', 'document_id', 'document_type', 'form_type'];
    }

    /**
     * @param ReportSubmission $reportSubmission
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
        $data[] = $this->getCaseNumber($report);
        $data[] = $this->getDateReceived($report);
        $data[] = $this->getScanDate();
        $data[] = $this->getDocumentId($reportSubmission);
        $data[] = $this->getReportType();
        $data[] = $this->getFormType();

        return $data;
    }

    /**
     * @param ReportInterface $report
     * @return string
     */
    private function getCaseNumber(ReportInterface $report)
    {
        return $report->getClient()->getCaseNumber();
    }

    /**
     * @param ReportInterface $report
     * @return null|string
     */
    private function getDateReceived(ReportInterface $report)
    {
        return $this->outputDate($report->getSubmitDate());
    }

    /**
     * @return null|string
     */
    private function getScanDate()
    {
        return $this->outputDate($this->dateTimeProvider->getDateTime());
    }

    /**
     * @param ReportSubmission $reportSubmission
     * @return null|string
     */
    private function getDocumentId(ReportSubmission $reportSubmission)
    {
        foreach ($reportSubmission->getDocuments() as $document) {
            if ($document->isReportPdf() && substr($document->getFileName(), -4) === '.pdf') {
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
     * @param $date
     * @return null|string
     */
    private function outputDate($date)
    {
        return ($date instanceof \DateTime) ? $date->format('d/m/Y') : null;
    }
}
