<?php

namespace App\Factory;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Service\BankHolidaysAPIService;
use App\Service\CarbonBusinessDaysService;
use App\Service\ReportStatusService;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;

class ReportEntityFactory
{
    private \DateTime $endDate;
    private Report $report;

    public function __construct(
        private CarbonBusinessDaysService $carbonBusinessDaysService,
        private BankHolidaysAPIService $bankHolidaysAPIService
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function create(Client $client, string $type, \DateTime $startDate, \DateTime $endDate, $dateChecks = true): Report
    {
        $this->endDate = $endDate;

        $this->report = new Report($client, $type, $startDate, $this->endDate, $dateChecks);
        $this->updateDueDateBasedOnEndDate();
        $this->updateSectionsForNewReports();

        return $this->report;
    }

    /**
     * set Due date to +21 days after end date (Lay reports) if end date before 13/11/19 otherwise +56 days.
     *
     * @throws GuzzleException
     */
    public function updateDueDateBasedOnEndDate(Report $report = null)
    {
        // due date set to 8 weeks (40 business days) after the end date unless lay reports where end date is beyond
        // 13/11/19. Then it is 15 days (DDLS-208)

        $reportEntity = $report ?? $this->report;

        $this->carbonBusinessDaysService->load();

        if ($reportEntity->isLayReport() && $reportEntity->getEndDate()->format('Ymd') >= '20191113') {
            $dueDateSet = Carbon::parse($reportEntity->getEndDate())->addBusinessDays('15')->format('Y-m-d H:i:s');

            // convert date time string into a date time object
            $reportEntity->setDueDate(\DateTime::createFromFormat('Y-m-d H:i:s', $dueDateSet));
        } else {
            $dueDateSet = Carbon::parse($reportEntity->getEndDate())->addBusinessDays('40')->format('Y-m-d H:i:s');

            // convert date time string into a date time object
            $reportEntity->setDueDate(\DateTime::createFromFormat('Y-m-d H:i:s', $dueDateSet));
        }
    }

    private function updateSectionsForNewReports()
    {
        // set sections as notStarted when a new report is created
        $statusCached = [];
        foreach ($this->report->getAvailableSections() as $sectionId) {
            $statusCached[$sectionId] = ['state' => ReportStatusService::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        $this->report->setSectionStatusesCached($statusCached);
        $this->report->reportStatusCached = Report::STATUS_NOT_STARTED;
    }
}
