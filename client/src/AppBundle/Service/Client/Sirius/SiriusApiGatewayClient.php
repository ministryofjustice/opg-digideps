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

    public function sendReportPdfDocument(SiriusDocumentUpload $upload, string $content, string $caseRef)
    {
        $reportJson = $this->serializer->serialize(['data' => $upload], 'json');

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

        $signedRequest = $this->buildSignedRequest(
            sprintf('clients/%s/reports', $caseRef),
            'POST',
            $multipart,
            'application/vnd.opg-data.v1+json',
            'multipart/form-data'
        );

        return $this->httpClient->send($signedRequest);
    }

    /** @TODO check with final swagger doc on endpoint for multipart naming conventions once its ready */
    public function sendSupportingDocument(SiriusDocumentUpload $upload, string $content, string $submissionUuid)
    {
        $reportJson = $this->serializer->serialize($upload, 'json');

        $multipart = new MultipartStream([
            [
                'name' => 'supporting_document',
                'contents' => $reportJson
            ],
            [
                'name' => 'supporting_document_file',
                'contents' => base64_encode($content)
            ],
        ]);

        $signedRequest = $this->buildSignedRequest(
            sprintf('reports/%s/supportingdocuments',
                $submissionUuid),
            'POST',
            $multipart,
            'application/vnd.opg-data.v1+json',
            'multipart/form-data'
        );

        return $this->httpClient->send($signedRequest);
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param string|null|resource|StreamInterface $body
     * @param string $accept
     * @param string $contentType
     * @return Request|\Psr\Http\Message\RequestInterface
     */
    private function buildSignedRequest(
        string $endpoint,
        string $method,
        $body='',
        string $accept='application/json',
        string $contentType='application/json'
    )
    {
        $url = new Uri(sprintf('%s/%s', $this->baseUrl, $endpoint));

        $request = new Request($method, $url, [
            'Accept' => $accept,
            'Content-type' => $contentType
        ], $body);

        // Sign the request with an AWS Authorization header.
        $signedRequest = $this->requestSigner->signRequest($request, 'execute-api');
        return $signedRequest;
    }
}
