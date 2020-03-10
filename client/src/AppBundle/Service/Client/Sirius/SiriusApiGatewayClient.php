<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Sirius;

use AppBundle\Service\AWS\RequestSigner;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\MultipartStream;
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

    /** @var Serializer */
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

    public function sendDocument(SiriusDocumentUpload $upload, string $content, string $caseRef)
    {
        $reportJson = $this->serializer->serialize($upload, 'json');

        $multipart = new MultipartStream([
            [
                'name' => 'report',
                'contents' => $reportJson
            ],
            [
                'name' => 'report_file',
                'contents' => base64_encode($content)
            ],
        ]);

        $signedRequest = $this->buildSignedRequest(sprintf('clients/%s/reports', $caseRef), 'POST', $multipart);

        return $this->httpClient->send($signedRequest);
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param string|null|resource|StreamInterface $body
     * @return Request|\Psr\Http\Message\RequestInterface
     */
    private function buildSignedRequest(string $endpoint, string $method, $body='')
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
