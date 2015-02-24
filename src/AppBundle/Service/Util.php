<?php
namespace AppBundle\Service;

use AppBundle\Service\ApiClient;

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
    public function getAllowedCourtOrderTypeChoiceOptions(array $filter = [])
    {
        $choices = [];
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
        }
        return $choices;
    }
}