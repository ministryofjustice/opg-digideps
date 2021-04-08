<?php declare(strict_types=1);


namespace App\Service;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Model\Sirius\QueuedDocumentData;
use App\Model\Sirius\SiriusApiError;
use App\Model\Sirius\SiriusDocumentFile;
use App\Model\Sirius\SiriusDocumentUpload;
use App\Model\Sirius\SiriusReportPdfDocumentMetadata;
use App\Model\Sirius\SiriusSupportingDocumentMetadata;
use App\Service\Client\RestClient;
use App\Service\Client\Sirius\SiriusApiGatewayClient;
use App\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
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

    /** @var SiriusApiErrorTranslator */
    private $errorTranslator;

    /** @var int[] */
    private $syncErrorSubmissionIds;

    /** @var int */
    private $docsNotSyncedCount;

    public function __construct(
        S3Storage $storage,
        SiriusApiGatewayClient $siriusApiGatewayClient,
        RestClient $restClient,
        SiriusApiErrorTranslator $errorTranslator
    ) {
        $this->storage = $storage;
        $this->siriusApiGatewayClient = $siriusApiGatewayClient;
        $this->restClient = $restClient;
        $this->syncErrorSubmissionIds = [];
        $this->docsNotSyncedCount = 0;
        $this->errorTranslator = $errorTranslator;
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

    public function getDocsNotSyncedCount(): int
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
            $siriusResponse = $this->handleSiriusSync($documentData);

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
            $this->handleSiriusSync($documentData);
            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (Throwable $e) {
            $this->handleSyncErrors($e, $documentData);
            return null;
        }
    }

    private function buildUpload(QueuedDocumentData $documentData)
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
            ->setS3Reference($documentData->getStorageReference());

        return (new SiriusDocumentUpload())
            ->setType($type)
            ->setAttributes($siriusDocumentMetadata)
            ->setFile($file);
    }

    private function determineReportType(QueuedDocumentData $documentData)
    {
        if ($documentData->getNdrId()) {
            return 'NDR';
        } elseif (in_array($documentData->getReportType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
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
     * @return mixed|ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleSiriusSync(QueuedDocumentData $documentData)
    {
        if ($documentData->isReportPdf()) {
            return $this->siriusApiGatewayClient->sendReportPdfDocument(
                $this->buildUpload($documentData),
                strtoupper($documentData->getCaseNumber())
            );
        } else {
            return $this->siriusApiGatewayClient->sendSupportingDocument(
                $this->buildUpload($documentData),
                $documentData->getReportSubmissionUuid(),
                strtoupper($documentData->getCaseNumber())
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
        if (method_exists($e, 'getResponse') && method_exists($e->getResponse(), 'getBody')) {
            $errorMessage = $this->errorTranslator->translateApiError((string) $e->getResponse()->getBody());
        } else {
            $errorMessage = (string) $e->getMessage();
        }

        if (method_exists($e, 'getCode')) {
            $syncStatus = $e->getCode() > 399 && $e->getCode() < 500 ?
                Document::SYNC_STATUS_PERMANENT_ERROR : Document::SYNC_STATUS_TEMPORARY_ERROR;
        } else {
            $syncStatus = Document::SYNC_STATUS_PERMANENT_ERROR;
        }

        if ($documentData->getDocumentSyncAttempts() >= 3) {
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
}
