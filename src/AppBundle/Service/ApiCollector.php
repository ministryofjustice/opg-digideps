<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @codeCoverageIgnore
 */
class ApiCollector extends DataCollector
{
    /**
     * @var RestClient
     */
    public $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function collect(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Exception $exception = null)
    {
        $this->data = [
            'calls' => $this->restClient->getHistory(),
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
