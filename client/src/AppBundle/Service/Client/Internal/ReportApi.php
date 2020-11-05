<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\ReportSubmittedException;
use AppBundle\Exception\RestClientException;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportApi {
    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        RestClient $restClient
    ) {
        $this->restClient = $restClient;
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
            $report = $this->restClient->get("report/{$reportId}", 'Report\\Report', $groups);
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

        return $this->restClient->get("ndr/{$ndrId}", 'Ndr\Ndr', $groups);
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
}
