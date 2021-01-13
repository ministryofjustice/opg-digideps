<?php declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Event\ReportSubmittedEvent;
use App\Event\ReportUnsubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\DisplayableException;
use App\Exception\ReportSubmittedException;
use App\Exception\RestClientException;
use App\Service\Client\RestClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportApi
{
    private const REPORT_ENDPOINT_BY_ID = 'report/%s';
    private const NDR_ENDPOINT_BY_ID = 'ndr/%s';

    /** @var RestClient */
    private $restClient;

    /** @var ObservableEventDispatcher */
    private $eventDispatcher;

    public function __construct(RestClient $restClient, ObservableEventDispatcher $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Client $client
     * @param array  $groups
     *
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, $groups = [])
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

    /**
     * @param int   $reportId
     * @param array $groups
     *
     * @return Report
     */
    public function getReport(int $reportId, array $groups = [])
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
            if ($e->getStatusCode() === 403 || $e->getStatusCode() === 404) {
                throw new NotFoundHttpException($e->getData()['message']);
            } else {
                throw $e;
            }
        }

        return $report;
    }

    /**
     * @param int   $reportId
     * @param array $groups
     *
     * @throws DisplayableException if report doesn't have specified section
     * @throws NotFoundHttpException if report is submitted
     *
     * @return Report
     */
    public function getReportIfNotSubmitted(int $reportId, array $groups = [])
    {
        $report = $this->getReport($reportId, $groups);

        $sectionId = $this->getSectionId();
        if ($sectionId && !$report->hasSection($sectionId)) {
            throw new DisplayableException('Section not accessible with this report type.');
        }

        if ($report->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        return $report;
    }

    /**
     * @param int   $ndrId
     * @param array $groups
     *
     * @return Ndr
     */
    public function getNdr(int $ndrId, array $groups)
    {
        $groups[] = 'ndr';
        $groups[] = 'ndr-client';
        $groups[] = 'client';
        $groups = array_unique($groups);

        return $this->restClient->get(sprintf(self::NDR_ENDPOINT_BY_ID, $ndrId), 'Ndr\Ndr', $groups);
    }

    /**
     * @param int $reportId
     *
     * @param array $groups
     * @return Ndr
     */
    public function getNdrIfNotSubmitted(int $reportId, array $groups = [])
    {
        $report = $this->getNdr($reportId, $groups);
        if ($report->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        return $report;
    }

    protected function getSectionId()
    {
        return null;
    }

    public function submit(Report $reportToSubmit, User $submittedBy)
    {
        $newYearReportId = $this->restClient->put(sprintf('report/%s/submit', $reportToSubmit->getId()), $reportToSubmit, ['submit']);

        $event = new ReportSubmittedEvent($reportToSubmit, $submittedBy, $newYearReportId);
        $this->eventDispatcher->dispatch(ReportSubmittedEvent::NAME, $event);
    }

    public function unsubmit(Report $report, User $user, string $trigger): void
    {
        $report->setUnSubmitDate(new \DateTime());

        $this->restClient->put('report/' . $report->getId() . '/unsubmit', $report, [
            'submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'startEndDates', 'report_due_date'
        ]);

        $event = new ReportUnsubmittedEvent(
            $report,
            $user,
            $trigger
        );

        $this->eventDispatcher->dispatch(ReportUnsubmittedEvent::NAME, $event);
    }
}
