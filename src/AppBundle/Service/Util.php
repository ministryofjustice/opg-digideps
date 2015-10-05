<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report;

class Util
{

    /**
     * @var RestClient
     */
    private $restClient;


    /**
     * 
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }


    /**
     * @return array $choices
     */
    public function getAllowedCourtOrderTypeChoiceOptions(array $filter = [], $sort = null)
    {
        $responseArray = $this->restClient->get('court-order-type/all', 'array');

        if (!empty($filter)) {
            foreach ($responseArray['court_order_types'] as $value) {
                if (in_array($value['id'], $filter)) {
                    $choices[$value['id']] = $value['name'];
                }
            }
        } else {
            foreach ($responseArray['court_order_types'] as $value) {
                $choices[$value['id']] = $value['name'];
            }
        }

        if ($sort != null) {
            $sort($choices);
        }
        return $choices;
    }


    /**
     * @param integer $clientId
     * @param integer $userId for secutity check (if present)
     * @param array $groups
     * 
     * @return Client
     */
    public function getClient($clientId)
    {
        return $this->restClient->get('client/' . $clientId, 'Client');
    }


    /**
     * @param integer $reportId
     * @param integer $userId for secutity checks (if present)
     * @param array $groups
     * 
     * @return Report
     */
    public function getReport($reportId, $userId, array $groups = [ "transactions", "basic"])
    {
        return $this->restClient->get("report/find-by-id/{$reportId}/{$userId}", 'Report', [ 'query' => [ 'groups' => $groups]]);
    }


    /**
     * @param integer $userId userId (remove at next refactor. not needed as securty is already in the class)
     * @param Client $client
     * 
     * @return Report[]
     */
    public function getReportsIndexedById($userId, Client $client, $groups)
    {
        $reportIds = $client->getReports();

        if (empty($reportIds)) {
            return [];
        }

        $ret = [];
        foreach ($reportIds as $id) {
            $ret[$id] = $this->getReport($id, $userId, $groups);
        }

        return $ret;
    }

}