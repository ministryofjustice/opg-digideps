<?php declare(strict_types=1);


namespace AppBundle\Service\Client;

use GuzzleHttp\Psr7\Response;

class RestClientMock implements RestClientInterface
{
    /** @var Response[] */
    private $responses = [];

    public function post($endpoint, $mixed, array $jmsGroups = [], $expectedResponseType = 'array')
    {
        return !empty($this->responses) ? array_shift($this->responses) : new Response();
    }

    public function appendResponse(Response $response)
    {
        $this->responses[] = $response;
    }
}
