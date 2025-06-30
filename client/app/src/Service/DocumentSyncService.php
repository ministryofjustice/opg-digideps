<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Model\Sirius\QueuedDocumentData;
use App\Model\Sirius\SiriusDocumentFile;
use App\Model\Sirius\SiriusDocumentUpload;
use App\Model\Sirius\SiriusReportPdfDocumentMetadata;
use App\Model\Sirius\SiriusSupportingDocumentMetadata;
use App\Service\Client\RestClient;
use App\Service\Client\Sirius\SiriusApiGatewayClient;
use App\Service\File\FileNameFixer;
use GuzzleHttp\Psr7\MimeType;
use Psr\Http\Message\ResponseInterface;

class DocumentSyncService
{
    public const MISSING_FILE_EXTENSION_ERROR =
        'File extension is missing from filename. This file will need to be manually synced with Sirius';

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

    private FileNameFixer $fileNameFixer;

    public function __construct(
        SiriusApiGatewayClient $siriusApiGatewayClient,
        RestClient $restClient,
        SiriusApiErrorTranslator $errorTranslator,
        FileNameFixer $fileNameFixer,
    ) {
        $this->siriusApiGatewayClient = $siriusApiGatewayClient;
        $this->restClient = $restClient;
        $this->syncErrorSubmissionIds = [];
        $this->docsNotSyncedCount = 0;
        $this->errorTranslator = $errorTranslator;
        $this->fileNameFixer = $fileNameFixer;
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
     * @return QueuedDocumentData|\Exception|mixed|\Throwable|null
     */
    public function syncDocument(QueuedDocumentData $documentData)
    {
        if ($documentData->isReportPdf() && 'application/pdf' == MimeType::fromFilename($documentData->getFileName())) {
            return $this->syncReportDocument($documentData);
        } else {
            if (!$documentData->supportingDocumentCanBeSynced()) {
                ++$this->docsNotSyncedCount;

                return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_QUEUED);
            }

            return $this->syncSupportingDocument($documentData);
        }
    }

    public function syncReportDocument(QueuedDocumentData $documentData): ?Document
    {
        try {
            $siriusResponse = $this->handleSiriusSync($documentData);

            $data = json_decode(strval($siriusResponse->getBody()), true);

            $this->handleReportSubmissionUpdate($documentData->getReportSubmissionId(), $data['data']['id']);

            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (\Throwable $e) {
            $this->handleSyncErrors($e, $documentData);

            return null;
        }
    }

    public function syncSupportingDocument(QueuedDocumentData $documentData): ?Document
    {
        try {
            $this->handleSiriusSync($documentData);

            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (\Throwable $e) {
            $this->handleSyncErrors($e, $documentData);

            return null;
        }
    }

    private function buildUpload(QueuedDocumentData $documentData)
    {
        $fileName = $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension($documentData->getFileName());
        $mimeType = MimeType::fromFilename($fileName);

        if (is_null($mimeType)) {
            throw new \Exception(self::MISSING_FILE_EXTENSION_ERROR, 400);
        }

        $file = (new SiriusDocumentFile())
            ->setName($fileName)
            ->setMimetype($mimeType)
            ->setS3Reference($documentData->getStorageReference());

        if ($documentData->isReportPdf()) {
            $siriusDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
                ->setReportingPeriodFrom($documentData->getReportStartDate())
                ->setReportingPeriodTo($this->determineEndDate($documentData))
                ->setYear(intval($documentData->getReportStartDate()->format('Y')))
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
     * @return mixed|ResponseInterface
     *
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
            $upload = $this->buildUpload($documentData);

            return $this->siriusApiGatewayClient->sendSupportingDocument(
                $upload,
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
     * @return \Exception|mixed|\Throwable
     */
    private function handleDocumentStatusUpdate(QueuedDocumentData $documentData, string $status, ?string $errorMessage = null)
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
        } catch (\Throwable $exception) {
            return $exception;
        }
    }

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

    private function handleSyncErrors(\Throwable $e, QueuedDocumentData $documentData)
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

        if (Document::SYNC_STATUS_PERMANENT_ERROR === $syncStatus) {
            if ($documentData->isReportPdf()) {
                $this->addToSyncErrorSubmissionIds($documentData->getReportSubmissionId());
            }

            ++$this->docsNotSyncedCount;
        }

        $this->handleDocumentStatusUpdate($documentData, $syncStatus, $errorMessage);
    }
}
