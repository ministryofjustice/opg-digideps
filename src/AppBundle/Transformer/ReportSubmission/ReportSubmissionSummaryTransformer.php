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

        foreach ($reportSubmissions as $reportSubmission) {
            $ret[] = $this->generateDataRow($reportSubmission);
        }

        return array_filter($ret);
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
     * @param ReportSubmission $reportSubmission
     * @return int
     */
    private function getId(ReportSubmission $reportSubmission)
    {
        return $reportSubmission->getId();
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
     * @param ReportSubmission $reportSubmission
     * @return null|string
     */
    private function getDateReceived(ReportSubmission $reportSubmission)
    {
        return $this->outputDate($reportSubmission->getCreatedOn());
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
        return ($date instanceof \DateTime) ? $date->format('Y-m-d') : null;
    }
}
