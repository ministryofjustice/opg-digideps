<?php

namespace OPG\Digideps\Frontend\Service;

use OPG\Digideps\Frontend\Service\Client\RestClient;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class ApiCollector extends DataCollector implements DataCollectorInterface
{
    /**
     * @var RestClient
     */
    public $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function collect(Request $request, Response $response, ?\Throwable $throwable = null)
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
            'calls' => [],
        ];
    }
}
