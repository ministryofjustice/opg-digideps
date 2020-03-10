<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Sirius;

use AppBundle\Service\AWS\RequestSigner;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Serializer\Serializer;

class SiriusApiGatewayClient
{
    /** @var Client */
    private $httpClient;

    /** @var RequestSigner */
    private $requestSigner;

    /** @var string */
    private $baseUrl;
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Client $httpClient
     * @param RequestSigner $requestSigner
     * @param string $baseUrl
     * @param Serializer $serializer
     */
    public function __construct(
        Client $httpClient,
        RequestSigner $requestSigner,
        string $baseUrl,
        Serializer $serializer
    )
    {
        $this->httpClient = $httpClient;
        $this->requestSigner = $requestSigner;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;
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

    public function post(string $endpoint, string $body)
    {
        $signedRequest = $this->buildSignedRequest($endpoint, 'POST', $body);
        return $this->httpClient->send($signedRequest);
    }

    public function sendDocument(SiriusDocumentUpload $upload)
    {
        $json = $this->serializer->serialize($upload, 'json');
        $this->post('documents', $json);
    }

    private function buildSignedRequest(string $endpoint, string $method, string $body='')
    {
        $url = new Uri(sprintf('%s/%s', $this->baseUrl, $endpoint));
        $request = new Request($method, $url, [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ], $body);

        // Sign the request with an AWS Authorization header.
        return $this->requestSigner->signRequest($request, 'execute-api');
    }
}
