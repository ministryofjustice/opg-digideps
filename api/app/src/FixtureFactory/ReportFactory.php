<?php

namespace App\FixtureFactory;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\v2\Fixture\ReportSection;

class ReportFactory
{
    public function __construct(private readonly ReportSection $reportSection)
    {
    }

    /**
     * @throws \Exception
     */
    public function create(array $data, Client $client): Report
    {
        $type = '';

        if (User::TYPE_LAY === $data['deputyType']) {
            $type = $data['reportType'];
        } elseif (in_array($data['deputyType'], ['PA', 'PA_ADMIN', 'PA_TEAM_MEMBER'])) {
            $type = $data['reportType'].'-6';
        } elseif (in_array($data['deputyType'], ['PROF', 'PROF_ADMIN', 'PROF_TEAM_MEMBER'])) {
            $type = $data['reportType'].'-5';
        }

        $startDate = $client->getExpectedReportStartDate($client->getCourtDate()->format('Y'));
        $endDate = $client->getExpectedReportEndDate($client->getCourtDate()->format('Y'));

        $report = new Report($client, $type, $startDate, $endDate);

        if (isset($data['reportStatus']) && Report::STATUS_READY_TO_SUBMIT === $data['reportStatus']) {
            $this->reportSection->completeReport($report);
            $report->updateSectionsStatusCache($report->getAvailableSections());
        }

        return $report;
    }
}
