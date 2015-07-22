<?php 
namespace AppBundle\Service;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use AppBundle\Service\ApiClient;

class ApiCollector extends DataCollector
{
    /**
     * @var ApiClient
     */
    public $apiClient;
    
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }
    
    public function collect(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Exception $exception = null)
    {
        $this->data = [
            'calls' => $this->apiClient->getHistory()
        ];
    }

    public function getName()
    {
        return 'api-collector';
    }
    
    public function getCalls()
    {
        return $this->data['calls'];
    }

}