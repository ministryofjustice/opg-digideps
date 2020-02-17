<?php

namespace AppBundle\Service\Client\Sirius;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class SiriusApiGatewayClient
{
    /** @var Client */
    private $httpClient;

    /** @var string */
    private $baseUrl;

    /**
     * @param Client $httpClient
     * @param string $baseUrl
     */
    public function __construct(Client $httpClient, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $endpoint
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $endpoint)
    {
        $url = new Uri(sprintf('%s/%s', $this->baseUrl, $endpoint));
        $request = new Request('GET', $url, $headers = [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ]);

        $provider = CredentialProvider::env();
        $signer = new SignatureV4('execute-api', 'eu-west-1');

        // Sign the request with an AWS Authorization header.
        $signedRequest = $signer->signRequest($request, $provider()->wait());
        return $this->httpClient->send($signedRequest);
    }
}
