<?php declare(strict_types=1);


namespace AppBundle\Service;


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
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

    /**
     * @var SiriusApiGatewayClient
     */
    private $siriusApiGatewayClient;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        S3Storage $storage,
        SiriusApiGatewayClient $siriusApiGatewayClient,
        RestClient $restClient
    )
    {
        $this->storage = $storage;
        $this->siriusApiGatewayClient = $siriusApiGatewayClient;
        $this->restClient = $restClient;
    }

    /**
     * @param QueuedDocumentData $documentData
     * @return QueuedDocumentData|\Exception|mixed|Throwable|null
     */
    public function syncDocument(QueuedDocumentData $documentData)
    {
//        $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_IN_PROGRESS);
        if ($documentData->isReportPdf() && mimetype_from_filename($documentData->getFileName()) == 'application/pdf') {
            return $this->syncReportDocument($documentData);
        } else {
            if (!$documentData->supportingDocumentCanBeSynced()) {
                return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_QUEUED);
            }

            return $this->syncSupportingDocument($documentData);
        }
    }

    /**
     * @param QueuedDocumentData $document
     * @return QueuedDocumentData|null
     */
    public function syncReportDocument(QueuedDocumentData $document): ?QueuedDocumentData
    {
        try {
            $content = $this->retrieveDocumentContentFromS3($document);

            $siriusResponse = $this->handleSiriusSync($document, $content);

            $data = json_decode(strval($siriusResponse->getBody()), true);

            $this->handleReportSubmissionUpdate($document->getReportSubmission()->getId(), $data['data']['id']);

            return $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_SUCCESS);
        } catch (Throwable $e) {
            $this->handleSyncErrors($e, $document);
            return null;
        }
    }

    /**
     * @param QueuedDocumentData $documentData
     * @return QueuedDocumentData|null
     */
    public function syncSupportingDocument(QueuedDocumentData $documentData): ?QueuedDocumentData
    {
        try {
            $content = $this->retrieveDocumentContentFromS3($documentData);
            $this->handleSiriusSync($documentData, $content);
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
        $upload = $this->buildUpload($documentData, $content);
        $caseRef = $documentData->getCaseNumber();

        if($documentData->isReportPdf()) {
            return $this->siriusApiGatewayClient->sendReportPdfDocument($upload, $caseRef);
        } else {
            /** @var ReportSubmission $reportPdfSubmission */
            $reportPdfSubmission = $documentData->getSyncedReportSubmission();
            return $this->siriusApiGatewayClient->sendSupportingDocument($upload, $reportPdfSubmission->getUuid(), $caseRef);
        }
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

        $this->handleDocumentStatusUpdate($documentData, $syncStatus, $errorMessage);
    }
}
