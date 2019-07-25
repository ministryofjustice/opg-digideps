<?php

namespace AppBundle\Transformer\ReportSubmission;

use AppBundle\Entity\Report\ReportSubmissionSummary;

class ReportSubmissionBurFixedWidthTransformer
{
    /**
     * @param array $reportSubmissionSummaries
     * @return array|string
     */
    public function transform(array $reportSubmissionSummaries)
    {
        $transformed = [];
        foreach ($reportSubmissionSummaries as $reportSubmissionSummary) {
            if (!$reportSubmissionSummary instanceof ReportSubmissionSummary) {
                continue;
            }

            $transformed[] = $this->transformItem($reportSubmissionSummary);
        }

        return $this->buildFileString($transformed);
    }

    /**
     * @param $reportSubmissionSummary
     * @return array
     */
    private function transformItem($reportSubmissionSummary)
    {
        return [
            "courtReference" => $this->fixLineLength($reportSubmissionSummary->getCaseNumber(), 8),
            "senderCo" => $this->fixLineLength('', 40),
            "senderForename" => $this->fixLineLength('', 25),
            "senderSurname" => $this->fixLineLength('', 40),
            "receivedDate" => $this->fixLineLength($reportSubmissionSummary->getDateReceived()->format('dmY'), 8),
            "formType" => $this->fixLineLength($reportSubmissionSummary->getFormType(), 34),
            "scanDate" => $this->fixLineLength($reportSubmissionSummary->getScanDate()->format('dmY'), 8),
            "nodeID" => $this->fixLineLength('', 10),
            "docType" => $this->fixLineLength($reportSubmissionSummary->getDocumentType(), 32),
            "docId" => $this->fixLineLength($reportSubmissionSummary->getDocumentId(), 170)
        ];
    }

    /**
     * @param null $string
     * @param int $length
     * @return string
     */
    private function fixLineLength($string, $length)
    {
        return str_pad($string, $length);
    }

    /**
     * @param array $data
     * @return string
     */
    private function buildFileString(array $data)
    {
        $fileContents = [];
        $fileContents[] = "00000000\r\n";

        foreach ($data as $dataLine) {
            $fileContents[] = implode("", $dataLine) . "\r\n";
        }

        $fileContents[] = "99999999\r\n";

        return implode("", $fileContents);
    }
}
