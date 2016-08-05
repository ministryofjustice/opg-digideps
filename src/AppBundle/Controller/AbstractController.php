<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report;
use AppBundle\Service\Client\RestClient;

class AbstractController extends Controller
{
    /**
     * @return RestClient
     */
    protected function getRestClient()
    {
        return $this->get('restClient');
    }

    /**
     * @return array $choices
     */
    protected function getAllowedCourtOrderTypeChoiceOptions()
    {
        $responseArray = $this->getRestClient()->get('court-order-type', 'array');
            foreach ($responseArray['court_order_types'] as $value) {
                $choices[$value['id']] = $value['name'];
            }

        arsort($choices);

        return $choices;
    }

    /**
     * @param int   $reportId
     * @param int   $userId   for secutity checks (if present)
     * @param array $groups
     * 
     * @return Report
     */
    public function getReport($reportId, array $groups/* = [ 'transactions', 'basic']*/)
    {
        return $this->getRestClient()->get("report/{$reportId}", 'Report\\Report', ['query' => ['groups' => $groups]]);
    }

    /**
     * @param Client $client
     * 
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, $groups)
    {
        $reportIds = $client->getReports();

        if (empty($reportIds)) {
            return [];
        }

        $ret = [];
        foreach ($reportIds as $id) {
            $ret[$id] = $this->getReport($id, $groups);
        }

        return $ret;
    }

    /**
     * @param int $reportId
     *
     * @return \AppBundle\Entity\Report
     *
     * @throws \RuntimeException if report is submitted
     */
    protected function getReportIfReportNotSubmitted($reportId, array $groups)
    {
        $report = $this->getReport($reportId, $groups);
        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        return $report;
    }
    
    /**
     * @return \AppBundle\Service\Mailer\MailFactory
     */
    protected function getMailFactory()
    {
        return $this->get('mailFactory');
    }

    /**
     * @return \AppBundle\Service\Mailer\MailSender
     */
    protected function getMailSender()
    {
        return $this->get('mailSender');
    }
}
