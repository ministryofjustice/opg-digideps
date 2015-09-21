<?php
namespace AppBundle\Service;

use AppBundle\Service\ApiClient;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report;

class Util
{
    /**
     * @var \AppBundle\Service\ApiClient $apiClient
     */
    private $apiClient;
    
    /**
     * 
     * @param \AppBundle\Service\ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }
    
    /**
     * @return array $choices
     */
    public function getAllowedCourtOrderTypeChoiceOptions(array $filter = [], $sort = null)
    {
        $response = $this->apiClient->get('court-order-type/all');
       
        if($response->getStatusCode() == 200){
            $arrayData = $response->json();
            
            if(!empty($filter)){
                foreach($arrayData['data']['court_order_types'] as $value){
                    if(in_array($value['id'], $filter)){
                        $choices[$value['id']] = $value['name'];
                    }
                }
            }else{
                foreach($arrayData['data']['court_order_types'] as $value){
                    $choices[$value['id']] = $value['name'];
                }
            }

            if ($sort != null)
            {
                $sort($choices);
            }
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
    public function getClient($clientId, $userId)
    {
        return $this->apiClient->getEntity('Client', 'client/find-by-id/' . $clientId . '/' . $userId);
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
        return $this->apiClient->getEntity('Report', "report/find-by-id/{$reportId}/{$userId}", [ 'query' => [ 'groups' => $groups ]]);
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
        
        if(empty($reportIds)){
            return [];
        }
        
        $ret = [];
        foreach($reportIds as $id){
            $ret[$id] = $this->getReport($id, $userId, $groups);
        }
        
        return $ret;
    }
}