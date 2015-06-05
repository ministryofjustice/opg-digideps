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
        $response = $this->apiClient->get('get_all_court_order_type');
       
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
    
    public function getClient($clientId, array $groups = [])
    {
        return $this->apiClient->getEntity('Client','find_client_by_id', [ 'parameters' => [ 'id' => $clientId ], 'query' => ['groups' => $groups] ]);
    }
    
    public function getReport($reportId,$groups = [ "transactions", "basic"])
    {
        return $this->apiClient->getEntity('Report', 'find_report_by_id', [ 'parameters' => [ 'id' => $reportId ], 'query' => [ 'groups' => $groups ]]);
    }
    
    /**
     * @param Client $client
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
            $ret[$id] = $this->getReport($id,$groups);
        }
        
        return $ret;
    }
}