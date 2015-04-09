<?php

namespace AppBundle\Service\Availability;

use AppBundle\Service\ApiClient;

class ApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ApiClient $apiClient)
    {
        $content = $apiClient->get('/manage/availability')->getBody();
        $contentArray = json_decode($content, 1);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($contentArray['data']['healthy'])) {
            $this->isHealthy = false;
            $this->errors = 'Cannot read API status. ' . json_last_error_msg();
            
            return;
        }
        
        $this->isHealthy = $contentArray['data']['healthy'];
        $this->errors = $contentArray['data']['errors'];
    }
    
    public function getName()
    {
        return 'Api';
    }

}