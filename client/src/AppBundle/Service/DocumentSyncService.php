<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\Client\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\Client\Sirius\SiriusSupportingDocumentMetadata;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DocumentSyncService
{
    const PERMANENT_ERRORS = [
        Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
        Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
    ];

    /** @var S3Storage */
    private $storage;

    /**
     * @var SiriusApiGatewayClient
     */
    private $siriusApiGateWayClient;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        S3Storage $storage,
        SiriusApiGatewayClient $siriusApiGateWayClient,
        RestClient $restClient
    )
    {
        $this->storage = $storage;
        $this->client = new Client([
            'base_uri' => 'http://pact-mock'
        ]);
        $this->siriusApiGateWayClient = $siriusApiGateWayClient;
        $this->restClient = $restClient;
    }

    /**
     * @param Document $document
     * @return string
     */
    public function syncDocument(Document $document)
    {
        if ($document->isReportPdf()) {
            return $this->syncReportDocument($document);
        } else {
            return $this->syncSupportingDocument($document);
        }
    }

    public function syncReportDocument(Document $document): ?string
    {
        /** @var Report $report */
        $report = $document->getReport();

        $s3Response = $this->retrieveDocumentContentFromS3($document);

        if ($s3Response instanceof S3Exception) {
            $syncStatus = in_array($s3Response->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES) ?
                Document::SYNC_STATUS_PERMANENT_ERROR : Document::SYNC_STATUS_TEMPORARY_ERROR;

            $errorMessage = sprintf('S3 error: %s', $s3Response->getMessage());
            return $this->handleDocumentStatusUpdate($document, $syncStatus, $errorMessage);
        }

        $siriusResponse = $this->handleSiriusSync($document, $s3Response);

        if ($siriusResponse instanceof Throwable) {
            $body = $siriusResponse->getResponse() ?
                (string) $siriusResponse->getResponse()->getBody() : (string) $siriusResponse->getMessage();

            return $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_PERMANENT_ERROR,$body);
        }

        $data = json_decode(strval($siriusResponse->getBody()), true);
        $relevantSubmission = $report->getReportSubmissionByDocument($document);

        $apiSubmissionResponse = $this->handleReportSubmissionUpdate($relevantSubmission->getId(), $data['data']['id']);

        if ($apiSubmissionResponse instanceof Throwable) {
            return null;
        }

        $apiDocumentResponse = $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_SUCCESS);

        if ($apiDocumentResponse instanceof Throwable) {
            return null;
        }

        return $apiDocumentResponse;

//        try {
//            $content = $this->storage->retrieve($document->getStorageReference());
//        } catch (S3Exception $e) {
//            $syncStatus = in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES) ?
//                Document::SYNC_STATUS_PERMANENT_ERROR : Document::SYNC_STATUS_TEMPORARY_ERROR;
//
//            return $this->restClient->put(
//                sprintf('document/%s', $document->getId()),
//                json_encode(['data' => ['syncStatus' => $syncStatus, 'syncError' => 'S3 error: ' . $e->getMessage()]])
//            );
//        }

//        try {
//            $upload = $this->buildUpload($document);
//            $apiGatewayResponse = $this->siriusApiGateWayClient->sendReportPdfDocument($upload, $content, $report->getClient()->getCaseNumber());
//
//            $data = json_decode(strval($apiGatewayResponse->getBody()), true);
//
//            /** @var ReportSubmission $latestSubmission */
//            $latestSubmission = $report->getReportSubmissions()[0];
//
//            $this->restClient->put(
//                sprintf('report-submission/%s', $latestSubmission->getId()),
//                json_encode(['data' => ['uuid' => $data['data']['id']]])
//            );
//
//            return $this->restClient->put(
//                sprintf('document/%s', $document->getId()),
//                json_encode(['data' =>
//                    ['syncStatus' => Document::SYNC_STATUS_SUCCESS]
//                ])
//            );
//        } catch (RequestException $exception) {
//            $body = $exception->getResponse() ? (string) $exception->getResponse()->getBody() : (string) $exception->getMessage();
//
//            return $this->restClient->put(
//                sprintf('document/%s', $document->getId()),
//                json_encode(['data' =>
//                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => json_decode($body)]
//                ])
//            );
//        }
    }

    /**
     * @param Document $document
     * @return string|null
     */
    public function syncSupportingDocument(Document $document): ?string
    {
        if (!$document->supportingDocumentCanBeSynced()) {
            return $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_QUEUED);
        }

        $content = $this->retrieveDocumentContentFromS3($document);

        if ($content instanceof Throwable) {
            return null;
        }

        $response = $this->handleSiriusSync($document, $content);

        if ($response instanceof Throwable) {
            return null;
        }

        $response = $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_SUCCESS);

        if ($response instanceof Throwable) {
            return null;
        }

        return $response;
    }

    private function buildUpload(Document $document)
    {
        $report = $document->getReport();

        if ($document->isReportPdf()) {
            $siriusDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
                ->setReportingPeriodFrom($report->getStartDate())
                ->setReportingPeriodTo($this->determineEndDate($report))
                ->setYear($report->getStartDate()->format('Y'))
                ->setDateSubmitted($report->getSubmitDate())
                ->setOrderType($this->determineReportType($report));

            $type = 'reports';
        } else {
            $siriusDocumentMetadata =
                (new SiriusSupportingDocumentMetadata())
                    ->setSubmissionId($report->getReportSubmissionByDocument($document)->getId());

            $type = 'supportingdocument';
        }

        return (new SiriusDocumentUpload())
            ->setType($type)
            ->setAttributes($siriusDocumentMetadata);
    }

    private function determineReportType(Report $report)
    {
        if ($report instanceof Ndr) {
            return 'NDR';
        } else if (in_array($report->getType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
            return 'HW';
        } else {
            return 'PF';
        }
    }

    public function determineEndDate(Report $report)
    {
        return $report instanceof Ndr ? $report->getStartDate() : $report->getEndDate();
    }

    /**
     * @param Document $document
     * @return string|S3Exception
     */
    private function retrieveDocumentContentFromS3(Document $document)
    {
        try {
            return $this->storage->retrieve($document->getStorageReference());
        } catch (S3Exception $e) {
            return $e;
        }
    }

    private function handleSiriusSync(Document $document, string $content)
    {
        try {
            /** @var ReportSubmission $latestSubmission */
            $relevantSubmission = $document->getReport()->getReportSubmissionByDocument($document);
            $upload = $this->buildUpload($document);

            if($document->isReportPdf()) {
               return $this->siriusApiGateWayClient->sendReportPdfDocument($upload, $content, $document->getReport()->getClient()->getCaseNumber());
            } else {
                return $this->siriusApiGateWayClient->sendSupportingDocument($upload, $content, $relevantSubmission->getUuid());
            }
        } catch (RequestException $exception) {
            return $exception;
        }
    }

    /**
     * @param Document $document
     * @return string|Throwable
     */
    private function handleDocumentStatusUpdate(Document $document, string $status, ?string $errorMessage=null)
    {
        $errorMessage = json_decode($errorMessage) ? json_decode($errorMessage) : $errorMessage;

        $data = is_null($errorMessage) ?
            ['syncStatus' => $status] : ['syncStatus' => $status, 'syncError' => $errorMessage];

        try {
            return $this->restClient->put(
                sprintf('document/%s', $document->getId()),
               json_encode(['data' => $data])
            );
        } catch (Throwable $exception) {
            return $exception;
        }
    }

    private function handleReportSubmissionUpdate(int $reportSubmissionId, string $uuid)
    {
        try {
            return $this->restClient->put(
                sprintf('report-submission/%s', $reportSubmissionId),
                json_encode(['data' => ['uuid' => $uuid]])
            );
        } catch (Throwable $exception) {
            return $exception;
        }
    }
}
