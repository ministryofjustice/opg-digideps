<?php

declare(strict_types=1);

namespace App\Service\Client;

use GuzzleHttp\Psr7\Response;

class RestClientMock implements RestClientInterface
{
    /** @var Response[] */
    private $responses = [];

    /**
     * @param string $expectedResponseType
     *
     * @return Response|mixed
     */
    public function post($endpoint, $data, array $jmsGroups = [], $expectedResponseType = 'array')
    {
        return $this->returnQueuedOrSuccessResponse();
    }

    /**
     * @return int|bool
     */
    private function getLoggedUserId()
    {
        return 1;
    }

    /**
     * @return Response|mixed
     */
    private function returnQueuedOrSuccessResponse()
    {
        return !empty($this->responses) ? array_shift($this->responses) : new Response();
    }

    public function appendResponse(Response $response)
    {
        $this->responses[] = $response;
    }
}
