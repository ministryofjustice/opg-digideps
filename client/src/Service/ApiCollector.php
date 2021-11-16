<?php

namespace App\Service;

use App\Service\Client\RestClient;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * @codeCoverageIgnore
 */
class ApiCollector extends DataCollector implements DataCollectorInterface
{
    public function __construct(public RestClient $restClient)
    {
    }

    public function collect(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response, \Throwable $throwable = null)
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

    public function reset()
    {
        $this->data = [
            'calls' => []
        ];
    }
}
