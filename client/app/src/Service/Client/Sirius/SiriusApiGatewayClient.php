<?php

declare(strict_types=1);

namespace App\Service\Client\Sirius;

use App\Model\Sirius\SiriusDocumentUpload;
use App\Service\AWS\RequestSigner;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class SiriusApiGatewayClient
{
    public const string SIRIUS_API_GATEWAY_VERSION = 'v2';
    public const string SIRIUS_REPORT_ENDPOINT = 'clients/%s/reports';
    public const string SIRIUS_SUPPORTING_DOCUMENTS_ENDPOINT = 'clients/%s/reports/%s/supportingdocuments';
    public const string SIRIUS_CHECKLIST_POST_ENDPOINT = 'clients/%s/reports/%s/checklists';
    public const string SIRIUS_CHECKLIST_PUT_ENDPOINT = 'clients/%s/reports/%s/checklists/%s';

    public function __construct(
        private readonly Client $httpClient,
        private readonly RequestSigner $requestSigner,
        private readonly string $baseUrl,
        private readonly Serializer $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $endpoint): ResponseInterface
    {
        $signedRequest = $this->buildSignedRequest($endpoint, 'GET');

        return $this->httpClient->send($signedRequest, ['connect_timeout' => 1, 'timeout' => 1.5]);
    }

    /**
     * @throws GuzzleException
     */
    public function sendReportPdfDocument(SiriusDocumentUpload $upload, string $caseRef): ResponseInterface
    {
        $reportJson = $this->serializer->serialize(['report' => ['data' => $upload]], 'json');

        $this->logger->warning('Syncing reportPDF document');
        $this->logger->warning($reportJson);

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
     * @throws GuzzleException
     */
    public function sendSupportingDocument(SiriusDocumentUpload $upload, string $submissionUuid, string $caseRef): ResponseInterface
    {
        $reportJson = $this->serializer->serialize(['supporting_document' => ['data' => $upload]], 'json');

        $this->logger->warning("Syncing supporting document ID with UUID: $submissionUuid");
        $this->logger->warning($reportJson);

        $signedRequest = $this->buildSignedRequest(
            sprintf(self::SIRIUS_SUPPORTING_DOCUMENTS_ENDPOINT, $caseRef, $submissionUuid),
            'POST',
            $reportJson,
            'application/vnd.opg-data.v1+json'
        );

        return $this->httpClient->send($signedRequest);
    }

    /**
     * @throws GuzzleException
     */
    public function postChecklistPdf(SiriusDocumentUpload $upload, string $submissionUuid, string $caseRef): ResponseInterface
    {
        $body = $this->serializer->serialize(['checklist' => ['data' => $upload]], 'json', ['json_encode_options' => JSON_FORCE_OBJECT]);

        $signedRequest = $this->buildSignedRequest(
            sprintf(self::SIRIUS_CHECKLIST_POST_ENDPOINT, $caseRef, $submissionUuid),
            'POST',
            $body,
            'application/vnd.opg-data.v1+json'
        );

        return $this->httpClient->send($signedRequest);
    }

    /**
     * @throws GuzzleException
     */
    public function putChecklistPdf(SiriusDocumentUpload $upload, string $submissionUuid, string $caseRef, string $checklistUuid): ResponseInterface
    {
        $body = $this->serializer->serialize(['checklist' => ['data' => $upload]], 'json', ['json_encode_options' => JSON_FORCE_OBJECT]);

        $signedRequest = $this->buildSignedRequest(
            sprintf(self::SIRIUS_CHECKLIST_PUT_ENDPOINT, $caseRef, $submissionUuid, $checklistUuid),
            'PUT',
            $body,
            'application/vnd.opg-data.v1+json'
        );

        return $this->httpClient->send($signedRequest);
    }

    private function buildSignedRequest(
        string $endpoint,
        string $method,
        string $body = '',
        string $accept = 'application/json'
    ): RequestInterface {
        $url = new Uri(sprintf('%s/%s/%s', $this->baseUrl, self::SIRIUS_API_GATEWAY_VERSION, $endpoint));

        $request = new Request($method, $url, [
            'Accept' => $accept,
            'Content-type' => 'application/json',
        ], $body);

        // Sign the request with an AWS Authorization header.
        return $this->requestSigner->signRequest($request, 'execute-api');
    }
}
