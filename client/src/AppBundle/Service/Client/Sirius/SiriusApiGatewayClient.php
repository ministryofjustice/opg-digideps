<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Sirius;

use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Service\AWS\RequestSigner;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class SiriusApiGatewayClient
{
    const SIRIUS_API_GATEWAY_VERSION = 'v1';
    const SIRIUS_REPORT_ENDPOINT = 'clients/%s/reports';
    const SIRIUS_SUPPORTING_DOCUMENTS_ENDPOINT = 'clients/%s/reports/%s/supportingdocuments';

    /** @var Client */
    private $httpClient;

    /** @var RequestSigner */
    private $requestSigner;

    /** @var string */
    private $baseUrl;

    /** @var Serializer */
    private $serializer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Client $httpClient
     * @param RequestSigner $requestSigner
     * @param string $baseUrl
     * @param Serializer $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $httpClient,
        RequestSigner $requestSigner,
        string $baseUrl,
        Serializer $serializer,
        LoggerInterface $logger
    )
    {
        $this->httpClient = $httpClient;
        $this->requestSigner = $requestSigner;
        $this->baseUrl = $baseUrl;
        $this->serializer = $serializer;

        $this->logger = $logger;
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

    /**
     * @param SiriusDocumentUpload $upload
     * @param string $caseRef
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendReportPdfDocument(SiriusDocumentUpload $upload, string $caseRef)
    {
        $reportJson = $this->serializer->serialize(['report' => ['data' => $upload]], 'json');

        $signedRequest = $this->buildSignedRequest(
            sprintf(self::SIRIUS_REPORT_ENDPOINT, $caseRef),
            'POST',
            $reportJson,
            'application/vnd.opg-data.v1+json'
        );

        // Add second argument ['debug' => true] to see requests in action
        return $this->httpClient->send($signedRequest);
    }

    /**
     * @param SiriusDocumentUpload $upload
     * @param string $content
     * @param string $submissionUuid
     * @param string $caseRef
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSupportingDocument(SiriusDocumentUpload $upload, string $submissionUuid, string $caseRef)
    {
        $reportJson = $this->serializer->serialize(['supporting_document' => ['data' => $upload]], 'json');

        $this->logger->warning("Syncing supporting document ID with UUID: $submissionUuid");

        $signedRequest = $this->buildSignedRequest(
            sprintf(self::SIRIUS_SUPPORTING_DOCUMENTS_ENDPOINT, $caseRef, $submissionUuid),
            'POST',
            $reportJson,
            'application/vnd.opg-data.v1+json'
        );

        return $this->httpClient->send($signedRequest);
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param string $body
     * @param string $accept
     * @param string $contentType
     * @return Request|\Psr\Http\Message\RequestInterface
     */
    private function buildSignedRequest(
        string $endpoint,
        string $method,
        string $body='',
        string $accept='application/json',
        string $contentType='application/json'
    )
    {
        $url = new Uri(sprintf('%s/%s/%s', $this->baseUrl, self::SIRIUS_API_GATEWAY_VERSION, $endpoint));

        $request = new Request($method, $url, [
            'Accept' => $accept,
            'Content-type' => $contentType
        ], $body);

        // Sign the request with an AWS Authorization header.
        $signedRequest = $this->requestSigner->signRequest($request, 'execute-api');
        return $signedRequest;
    }
}
