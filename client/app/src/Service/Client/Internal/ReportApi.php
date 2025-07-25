<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Event\ReportSubmittedEvent;
use App\Event\ReportUnsubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\ReportSubmittedException;
use App\Exception\RestClientException;
use App\Service\Client\RestClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportApi
{
    private const REPORT_ENDPOINT_BY_ID = 'report/%s';
    private const REPORT_SUBMIT_ENDPOINT = 'report/%s/submit';
    private const REPORT_UNSUBMIT_ENDPOINT = 'report/%s/unsubmit';
    private const REPORT_REFRESH_CACHE_ENDPOINT = 'report/%s/refresh-cache';
    private const REPORT_GET_ALL_WITH_QUEUED_CHECKLISTS_ENDPOINT = 'report/all-with-queued-checklists';
    private const NDR_ENDPOINT_BY_ID = 'ndr/%s';

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ObservableEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @param string[] $groups
     *
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, array $groups = []): array
    {
        $reports = $client->getReports();

        if (empty($reports)) {
            return [];
        }

        $ret = [];
        foreach ($reports as $report) {
            $id = $report->getId();
            $ret[$id] = $this->getReport($id, $groups);
        }

        return $ret;
    }

    public function getReport(int $reportId, array $groups = []): Report
    {
        $groups[] = 'report';
        $groups[] = 'report-client';
        $groups[] = 'client';
        $groups = array_unique($groups);
        sort($groups); // helps HTTP caching

        try {
            $report = $this->restClient->get(
                sprintf(self::REPORT_ENDPOINT_BY_ID, $reportId),
                'Report\\Report',
                $groups
            );
        } catch (RestClientException $e) {
            if (403 === $e->getStatusCode() || 404 === $e->getStatusCode()) {
                throw new NotFoundHttpException($e->getData()['message']);
            } else {
                throw $e;
            }
        }

        return $report;
    }

    /**
     * @throws NotFoundHttpException if report is submitted
     */
    public function getReportIfNotSubmitted(int $reportId, array $groups = []): Report
    {
        $report = $this->getReport($reportId, $groups);

        if ($report->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        return $report;
    }

    public function getNdr(int $ndrId, array $groups): Ndr
    {
        $groups[] = 'ndr';
        $groups[] = 'ndr-client';
        $groups[] = 'client';
        $groups = array_unique($groups);

        return $this->restClient->get(sprintf(self::NDR_ENDPOINT_BY_ID, $ndrId), 'Ndr\Ndr', $groups);
    }

    public function getNdrIfNotSubmitted(int $reportId, array $groups = []): Ndr
    {
        $report = $this->getNdr($reportId, $groups);
        if ($report->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        return $report;
    }

    public function submit(Report $reportToSubmit, User $submittedBy): void
    {
        $uri = sprintf(self::REPORT_SUBMIT_ENDPOINT, $reportToSubmit->getId());
        $newYearReportId = $this->restClient->put($uri, $reportToSubmit, ['submit']);

        $event = new ReportSubmittedEvent($reportToSubmit, $submittedBy, $newYearReportId);
        $this->eventDispatcher->dispatch($event, ReportSubmittedEvent::NAME);
    }

    public function unsubmit(Report $report, User $user, string $trigger): void
    {
        $report->setUnSubmitDate(new \DateTime());
        $uri = sprintf(self::REPORT_UNSUBMIT_ENDPOINT, $report->getId());

        $this->restClient->put($uri, $report, [
            'submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'startEndDates', 'report_due_date',
        ]);

        $event = new ReportUnsubmittedEvent(
            $report,
            $user,
            $trigger
        );

        $this->eventDispatcher->dispatch($event, ReportUnsubmittedEvent::NAME);
    }

    public function refreshReportStatusCache(string $reportId, array $sectionIds, array $jmsGroups = []): Report
    {
        $jmsGroups = array_merge($jmsGroups, ['report', 'report-client', 'client']);

        $uri = sprintf(self::REPORT_REFRESH_CACHE_ENDPOINT, $reportId);

        /** @var Report $report */
        $report = $this->restClient->post(
            $uri,
            ['sectionIds' => $sectionIds],
            $jmsGroups,
            'Report\\Report',
            ['query' => ['groups' => $jmsGroups]]
        );

        return $report;
    }

    /**
     * @return Report[]
     */
    public function getReportsWithQueuedChecklists(string $rowLimit): array
    {
        return $this->restClient->apiCall(
            'get',
            self::REPORT_GET_ALL_WITH_QUEUED_CHECKLISTS_ENDPOINT,
            ['row_limit' => $rowLimit],
            'Report\Report[]',
            [],
            false
        );
    }
}
