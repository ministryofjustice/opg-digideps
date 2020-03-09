<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Sirius;

use AppBundle\Service\AWS\RequestSigner;
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

    /** @var RequestSigner */
    private $requestSigner;

    /**
     * @param Client $httpClient
     * @param RequestSigner $requestSigner
     * @param string $baseUrl
     */
    public function __construct(
        Client $httpClient,
        RequestSigner $requestSigner,
        string $baseUrl
    )
    {
        $this->httpClient = $httpClient;
        $this->requestSigner = $requestSigner;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $endpoint
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $endpoint)
    {
        $signedRequest = $this->buildSignedRequest($endpoint, 'GET');
        return $this->httpClient->send($signedRequest);
    }

    public function post(string $endpoint)
    {
        $signedRequest = $this->buildSignedRequest($endpoint, 'POST');
        return $this->httpClient->send($signedRequest);
    }

    private function buildSignedRequest(string $endpoint, string $method)
    {
        $url = new Uri(sprintf('%s/%s', $this->baseUrl, $endpoint));
        $request = new Request($method, $url, $headers = [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ]);

        // Sign the request with an AWS Authorization header.
        return $this->requestSigner->signRequest($request, 'execute-api');
    }
}
