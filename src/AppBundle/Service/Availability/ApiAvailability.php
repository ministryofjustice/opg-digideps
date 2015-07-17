<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        try {
            $content = $container->get('apiclient')->get('/manage/availability')->getBody();
            $contentArray = json_decode($content, 1);
            // API not healtyh
            if (json_last_error() !== JSON_ERROR_NONE || !isset($contentArray['data']['healthy'])) {
                $this->isHealthy = false;
                $this->errors = 'Cannot read API status. ' . json_last_error_msg();
                return;
            } 
            
            // API healthy
            $this->isHealthy = $contentArray['data']['healthy'];
            $this->errors = $contentArray['data']['errors'];
            
        } catch (\Exception $e) {
            $this->isHealthy = false;
            $this->errors = 'Error when using ApiClient to connect to API . ' . $e->getMessage();
        }
        
    }
    
    public function getName()
    {
        return 'Api';
    }

}