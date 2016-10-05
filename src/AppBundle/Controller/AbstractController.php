<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\User;
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
     * @param array $jmsGroups
     *
     * @return User|null
     */
    protected function getUserWithData(array $jmsGroups)
    {
        $jmsGroups[] = 'user';
        $jmsGroups = array_unique($jmsGroups);
        sort($jmsGroups);

        return $this->getRestClient()->get('user/'.$this->getUser()->getId(), 'User', $jmsGroups);
    }

    /**
     * @return Client|null
     */
    protected function getFirstClient($groups = ['user', 'client'])
    {
        $user = $this->getRestClient()->get('user/'.$this->getUser()->getId(), 'User', $groups); /* @var $user EntityDir\User*/
        $clients = $user->getClients();

        return !empty($clients) ? $clients[0] : null;
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
     * @param array $groups
     * 
     * @return Report\Report
     */
    public function getReport($reportId, array $groups = [])
    {
        $groups[] = 'report';
        $groups[] = 'client';
        $groups = array_unique($groups);
        sort($groups); // helps HTTP caching
        return $this->getRestClient()->get("report/{$reportId}", 'Report\\Report', $groups);
    }

    /**
     * @param int   $odrId
     * @param array $groups
     *
     * @return Odr
     */
    public function getOdr($odrId, array $groups/* = ['basic']*/)
    {
        return $this->getRestClient()->get("odr/{$odrId}", 'Odr\Odr', $groups);
    }

    /**
     * @param Client $client
     * @param array  $groups
     *
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, $groups = [])
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
     * @return Report\Report
     *
     * @throws \RuntimeException if report is submitted
     */
    protected function getReportIfReportNotSubmitted($reportId, array $groups = [])
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
