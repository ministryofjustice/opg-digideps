<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Sync\Service;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\File\FileNameManipulation;
use OPG\Digideps\Frontend\Sync\Exception\SiriusDocumentSyncFailedException;
use OPG\Digideps\Frontend\Sync\Model\Sirius\QueuedDocumentData;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentFile;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentUpload;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusReportPdfDocumentMetadata;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusSupportingDocumentMetadata;
use OPG\Digideps\Frontend\Sync\Service\Client\Sirius\SiriusApiGatewayClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\MimeType;
use Psr\Http\Message\ResponseInterface;

class DocumentSyncService
{
    private const string MISSING_FILE_EXTENSION_ERROR =
        'File extension is missing from filename. This file will need to be manually synced with Sirius';

    /** @var int[] */
    private array $syncErrorSubmissionIds = [];

    private int $docsNotSyncedCount;

    public function __construct(
        private readonly SiriusApiGatewayClient $siriusApiGatewayClient,
        private readonly RestClient $restClient,
        private readonly SiriusApiErrorTranslator $errorTranslator,
    ) {
        $this->docsNotSyncedCount = 0;
    }

    /**
     * @return int[]
     */
    public function getSyncErrorSubmissionIds(): array
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

    public function getDocsNotSyncedCount(): int
    {
        return $this->docsNotSyncedCount;
    }

    public function setDocsNotSyncedCount(int $count): int
    {
        return $this->docsNotSyncedCount = $count;
    }

    /**
     * @return QueuedDocumentData|\Exception|mixed|\Throwable|null|Document
     */
    public function syncDocument(QueuedDocumentData $documentData): mixed
    {
        if ($documentData->isReportPdf() && 'application/pdf' == MimeType::fromFilename($documentData->getFileName())) {
            return $this->syncReportDocument($documentData);
        }

        if ($documentData->supportingDocumentCanBeSynced()) {
            return $this->syncSupportingDocument($documentData);
        }

        ++$this->docsNotSyncedCount;

        return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_QUEUED);
    }

    public function syncReportDocument(QueuedDocumentData $documentData): mixed
    {
        try {
            $siriusResponse = $this->handleSiriusSync($documentData);

            /** @var array $data */
            $data = json_decode(strval($siriusResponse->getBody()), associative: true);

            $this->restClient->apiCall(
                'put',
                sprintf('report-submission/%s/update-uuid', $documentData->getReportSubmissionId()),
                json_encode(['uuid' => $data['data']['id']]),
                'raw',
                [],
                false
            );

            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (\Throwable $e) {
            $this->handleSyncErrors($e, $documentData);

            return null;
        }
    }

    public function syncSupportingDocument(QueuedDocumentData $documentData): mixed
    {
        try {
            $this->handleSiriusSync($documentData);

            return $this->handleDocumentStatusUpdate($documentData, Document::SYNC_STATUS_SUCCESS);
        } catch (\Throwable $e) {
            $this->handleSyncErrors($e, $documentData);

            return null;
        }
    }

    /**
     * @throws \Exception
     */
    private function buildUpload(QueuedDocumentData $documentData): SiriusDocumentUpload
    {
        $fileName = FileNameManipulation::fileNameSanitation($documentData->getFileName());
        $mimeType = MimeType::fromFilename($fileName);

        if (is_null($mimeType)) {
            throw (new \Exception(self::MISSING_FILE_EXTENSION_ERROR, 400));
        }

        $file = (new SiriusDocumentFile())
            ->setName($fileName)
            ->setMimetype($mimeType)
            ->setS3Reference($documentData->getStorageReference());

        if ($documentData->isReportPdf()) {
            $reportType = 'PF';
            if (in_array($documentData->getReportType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
                $reportType = 'HW';
            }

            $siriusDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
                ->setReportingPeriodFrom($documentData->getReportStartDate())
                ->setReportingPeriodTo($this->determineEndDate($documentData))
                ->setDateSubmitted($documentData->getReportSubmitDate())
                ->setType($reportType)
                ->setSubmissionId($documentData->getReportSubmissionId());

            if (!is_null($documentData->getReportStartDate())) {
                $siriusDocumentMetadata->setYear(intval($documentData->getReportStartDate()->format('Y')));
            }

            $type = 'reports';
        } else {
            $siriusDocumentMetadata = (new SiriusSupportingDocumentMetadata())
                ->setSubmissionId($documentData->getReportSubmissionId());

            $type = 'supportingdocuments';
        }

        return (new SiriusDocumentUpload())
            ->setType($type)
            ->setAttributes($siriusDocumentMetadata)
            ->setFile($file);
    }

    public function determineEndDate(QueuedDocumentData $documentData): ?\DateTime
    {
        return $documentData->getReportEndDate();
    }

    /**
     * @throws GuzzleException|SiriusDocumentSyncFailedException
     */
    public function handleSiriusSync(QueuedDocumentData $documentData): ResponseInterface
    {
        if ($documentData->isReportPdf()) {
            return $this->siriusApiGatewayClient->sendReportPdfDocument(
                $this->buildUpload($documentData),
                strtoupper($documentData->getCaseNumber())
            );
        } elseif (!is_null($documentData->getReportSubmissionUuid())) {
            $upload = $this->buildUpload($documentData);

            return $this->siriusApiGatewayClient->sendSupportingDocument(
                $upload,
                $documentData->getReportSubmissionUuid(),
                strtoupper($documentData->getCaseNumber())
            );
        }

        throw new SiriusDocumentSyncFailedException(Document::SYNC_STATUS_TEMPORARY_ERROR);
    }

    public function setSubmissionsDocumentsToPermanentError(): void
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
     * @return Document|\Throwable
     */
    private function handleDocumentStatusUpdate(QueuedDocumentData $documentData, string $status, ?string $errorMessage = null): mixed
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
                Document::class,
                [],
                false
            );
        } catch (\Throwable $exception) {
            return $exception;
        }
    }

    private function handleSyncErrors(\Throwable $e, QueuedDocumentData $documentData): void
    {
        if (method_exists($e, 'getResponse') && method_exists($e->getResponse(), 'getBody')) {
            /** @var ResponseInterface $response */
            $response = $e->getResponse();
            $errorMessage = $this->errorTranslator->translateApiError((string)$response->getBody());
        } else {
            $errorMessage = $e->getMessage();
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
                $this->syncErrorSubmissionIds[] = $documentData->getReportSubmissionId();
            }

            ++$this->docsNotSyncedCount;
        }

        $this->handleDocumentStatusUpdate($documentData, $syncStatus, $errorMessage);
    }
}
