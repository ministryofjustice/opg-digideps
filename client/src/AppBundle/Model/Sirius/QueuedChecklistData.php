<?php declare(strict_types=1);

namespace AppBundle\Model\Sirius;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;

class QueuedChecklistData
{
    /** @var Report */
    private $report;

    /**
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     * @return QueuedChecklistData
     */
    public function setReport(Report $report): QueuedChecklistData
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return ReportSubmission|null
     */
    public function getSyncedReportSubmission(): ?ReportSubmission
    {
        foreach ($this->report->getReportSubmissions() as $submission) {
            if ($submission->getUuid()) {
                return $submission;
            }
        }

        return null;
    }
}
