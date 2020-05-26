<?php declare(strict_types=1);


namespace AppBundle\Service;


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Model\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Model\Sirius\SiriusSupportingDocumentMetadata;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function GuzzleHttp\Psr7\mimetype_from_filename;

class DocumentSyncService
{
    const PERMANENT_ERRORS = [
        Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
        Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
    ];

    /** @var S3Storage */
    private $storage;

    /** @var SiriusApiGatewayClient */
    private $siriusApiGatewayClient;

    /** @var RestClient */
    private $restClient;

    /** @var int[] */
    private $syncErrorSubmissionIds;

    /** @var int */
    private $docsNotSyncedCount;

    public function __construct(
        S3Storage $storage,
        SiriusApiGatewayClient $siriusApiGatewayClient,
        RestClient $restClient
    )
    {
        $this->storage = $storage;
        $this->siriusApiGatewayClient = $siriusApiGatewayClient;
        $this->restClient = $restClient;
        $this->syncErrorSubmissionIds = [];
        $this->docsNotSyncedCount = 0;
    }

    /**
     * @return array|int[]
     */
    public function getSyncErrorSubmissionIds()
    {
        return $this->syncErrorSubmissionIds;
    }

    /**
     * @param int[] $syncErrorSubmissionIds
     */
    public function setSyncErrorSubmissionIds(array $syncErrorSubmissionIds): void
    {
        $this->syncErrorSubmissionIds = $syncErrorSubmissionIds;
    }

    /**
     * @param int $submissionId
     */
    public function addToSyncErrorSubmissionIds(int $submissionId)
    {
        $this->syncErrorSubmissionIds[] = $submissionId;
    }

    public function getDocsNotSyncedCount()
    {
        return $this->docsNotSyncedCount;
    }

    public function setDocsNotSyncedCount(int $count)
    {
        return $this->docsNotSyncedCount = $count;
    }

    /**
     * @param QueuedDocumentData $documentData
     * @return QueuedDocumentData|\Exception|mixed|Throwable|null
     */
    public function syncDocument(QueuedDocumentData $documentData)
    {
        if ($documentData->isReportPdf() && mimetype_from_filename($documentData->getFileName()) == 'application/pdf') {
            return $this->syncReportDocument($documentData);
        } else {
            if (!$documentData->supportingDocumentCanBeSynced()) {
                $this->docsNotSyncedCount++;
                return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_QUEUED);
            }

            return $this->syncSupportingDocument($documentData);
        }
    }

    /**
     * @param QueuedDocumentData $document
     * @return Document|null
     */
    public function syncReportDocument(QueuedDocumentData $documentData): ?Document
    {
        try {
            $siriusResponse = $this->handleSiriusSync($documentData, $this->retrieveDocumentContentFromS3($documentData));

            $data = json_decode(strval($siriusResponse->getBody()), true);

            $this->handleReportSubmissionUpdate($documentData->getReportSubmissionId(), $data['data']['id']);

            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (Throwable $e) {
            $this->handleSyncErrors($e, $documentData);
            return null;
        }
    }

    /**
     * @param QueuedDocumentData $documentData
     * @return Document|null
     */
    public function syncSupportingDocument(QueuedDocumentData $documentData): ?Document
    {
        try {
            $this->handleSiriusSync($documentData, $this->retrieveDocumentContentFromS3($documentData));
            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (Throwable $e) {
            $this->handleSyncErrors($e, $documentData);
            return null;
        }
    }

    private function buildUpload(QueuedDocumentData $documentData, string $content)
    {
        if ($documentData->isReportPdf()) {
            $siriusDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
                ->setReportingPeriodFrom($documentData->getReportStartDate())
                ->setReportingPeriodTo($this->determineEndDate($documentData))
                ->setYear((intval($documentData->getReportStartDate()->format('Y'))))
                ->setDateSubmitted($documentData->getReportSubmitDate())
                ->setType($this->determineReportType($documentData))
                ->setSubmissionId($documentData->getReportSubmissionId());

            $type = 'reports';
        } else {
            $siriusDocumentMetadata =
                (new SiriusSupportingDocumentMetadata())
                    ->setSubmissionId($documentData->getReportSubmissionId());

            $type = 'supportingdocuments';
        }

        $file = (new SiriusDocumentFile())
            ->setName($documentData->getFileName())
            ->setMimetype(mimetype_from_filename($documentData->getFileName()))
            ->setSource(base64_encode($content));

        return (new SiriusDocumentUpload())
            ->setType($type)
            ->setAttributes($siriusDocumentMetadata)
            ->setFile($file);
    }

    private function determineReportType(QueuedDocumentData $documentData)
    {
        if ($documentData->getNdrId()) {
            return 'NDR';
        } else if (in_array($documentData->getReportType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
            return 'HW';
        } else {
            return 'PF';
        }
    }

    public function determineEndDate(QueuedDocumentData $documentData)
    {
        return $documentData->getNdrId() ? $documentData->getReportStartDate() : $documentData->getReportEndDate();
    }

    /**
     * @param QueuedDocumentData $documentData
     * @return string
     */
    private function retrieveDocumentContentFromS3(QueuedDocumentData $documentData)
    {
        return (string) $this->storage->retrieve($documentData->getStorageReference());
    }

    /**
     * @param QueuedDocumentData $documentData
     * @param string $content
     * @return mixed|ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleSiriusSync(QueuedDocumentData $documentData, string $content)
    {
        if($documentData->isReportPdf()) {
            return $this->siriusApiGatewayClient->sendReportPdfDocument($this->buildUpload($documentData, $content), $documentData->getCaseNumber());
        } else {
            return $this->siriusApiGatewayClient->sendSupportingDocument(
                $this->buildUpload($documentData, $content),
                $documentData->getSyncedReportSubmission()->getUuid(),
                $documentData->getCaseNumber()
            );
        }
    }

    public function setSubmissionsDocumentsToPermanentError()
    {
        $this->restClient->apiCall(
            'put',
            'document/update-related-statuses',
            json_encode(['submissionIds' => $this->getSyncErrorSubmissionIds(), 'errorMessage' => 'Report PDF failed to sync']),
            'raw',
            [],
            false
        );
    }

    /**
     * @param QueuedDocumentData $documentData
     * @param string $status
     * @param string|null $errorMessage
     * @return \Exception|mixed|Throwable
     */
    private function handleDocumentStatusUpdate(QueuedDocumentData $documentData, string $status, ?string $errorMessage=null)
    {
        $data = ['syncStatus' => $status];

        if (!is_null($errorMessage)) {
            $errorMessage = json_decode($errorMessage, true) ? json_decode($errorMessage, true) : $errorMessage;
            $data['syncError'] = $errorMessage;
        }

        try {
            return $this->restClient->apiCall(
                'put',
                sprintf('document/%s', $documentData->getDocumentId()),
                json_encode($data),
                'Report\\Document',
                [],
                false
            );
        } catch (Throwable $exception) {
            return $exception;
        }
    }

    /**
     * @param int $reportSubmissionId
     * @param string $uuid
     * @return mixed
     */
    private function handleReportSubmissionUpdate(int $reportSubmissionId, string $uuid)
    {
        return $this->restClient->apiCall(
            'put',
            sprintf('report-submission/%s/update-uuid', $reportSubmissionId),
            json_encode(['uuid' => $uuid]),
            'raw',
            [],
            false
        );
    }

    /**
     * @param Throwable $e
     * @param QueuedDocumentData $documentData
     */
    private function handleSyncErrors(Throwable $e, QueuedDocumentData $documentData)
    {
        if ($e instanceof S3Exception) {
            $syncStatus = in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES) ?
                Document::SYNC_STATUS_PERMANENT_ERROR : Document::SYNC_STATUS_TEMPORARY_ERROR;

            $errorMessage = sprintf('S3 error while syncing document: %s', $e->getMessage());
        } else {
            $errorMessage = (method_exists($e, 'getResponse') && method_exists($e->getResponse(), 'getBody')) ?
                (string) $e->getResponse()->getBody() : (string) $e->getMessage();

            $syncStatus = Document::SYNC_STATUS_PERMANENT_ERROR;
        }

        if ($syncStatus === Document::SYNC_STATUS_PERMANENT_ERROR) {
            if ($documentData->isReportPdf()) {
                $this->addToSyncErrorSubmissionIds($documentData->getReportSubmissionId());
            }

            $this->docsNotSyncedCount++;
        }

        $this->handleDocumentStatusUpdate($documentData, $syncStatus, $errorMessage);
    }

    public function translateApiError(?string $apiErrorCode)
    {
        $translations = [
            'OPGDATA-API-FORBIDDEN' => 'Credentials used for integration lack correct permissions',
            'OPGDATA-API-API_CONFIGURATION_ERROR' => 'Integration API internal error',
            'OPGDATA-API-AUTHORIZER_CONFIGURATION_ERROR' => 'Integration API internal error',
            'OPGDATA-API-AUTHORIZER_FAILURE' => 'Integration API internal error',
            'OPGDATA-API-INVALIDREQUEST' => 'The body of the request is not valid',
            'OPGDATA-API-BAD_REQUEST_PARAMETERS' => 'The parameters of the request are not valid',
            'OPGDATA-API-SERVERERROR' => 'Integration API server error',
            'OPGDATA-API-EXPIRED_TOKEN' => 'Auth token has expired',
            'OPGDATA-API-INTEGRATION_FAILURE' => 'There was a problem syncing from the integration to Sirius',
            'OPGDATA-API-INTEGRATION_TIMEOUT' => 'The sync process timed out while communicating with Sirius',
            'OPGDATA-API-INVALID_API_KEY' => 'The API key used in the request is not valid',
            'OPGDATA-API-INVALID_SIGNATURE' => 'The signature of the request is not valid',
            'OPGDATA-API-MISSING_AUTHENTICATION_TOKEN' => 'Authentication token is missing from the request',
            'OPGDATA-API-QUOTA_EXCEEDED' => 'API quota has been exceeded',
            'OPGDATA-API-FILESIZELIMIT' => 'The size of the file exceeded the file size limit (6MB)',
            'OPGDATA-API-NOTFOUND' => 'Invalid URL used during integration or the resource no longer exists',
            'OPGDATA-API-THROTTLED' => 'Too many requests made - throttling in action',
            'OPGDATA-API-UNAUTHORISED' => 'No user/auth provided during requests',
            'OPGDATA-API-MEDIA' => 'Media type of the file is not supported',
            'OPGDATA-API-WAF_FILTERED' => 'AWS WAF filtered this request and it was not sent to Sirius'
        ];

        if (is_null($apiErrorCode) || is_null($translations[$apiErrorCode])) {
            return 'UNEXPECTED ERROR CODE: An unknown error occurred during document sync';
        } else {
            return sprintf('%s: %s', $apiErrorCode, $translations[$apiErrorCode]);
        }

    }
}
