<?php declare(strict_types=1);


namespace AppBundle\Service;


use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Model\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Model\Sirius\SiriusSupportingDocumentMetadata;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
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
     * @param Document $document
     * @return string
     */
    public function syncDocument(Document $document)
    {
        $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_IN_PROGRESS);
        if ($document->isReportPdf() && mimetype_from_filename($document->getFileName()) == 'application/pdf') {
            return $this->syncReportDocument($document);
        } else {
            if (!$document->supportingDocumentCanBeSynced()) {
                return $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_QUEUED);
            }

            return $this->syncSupportingDocument($document);
        }
    }

    /**
     * @param Document $document
     * @return Document|null
     */
    public function syncReportDocument(Document $document): ?Document
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
     * @param Document $document
     * @return Document|null
     */
    public function syncSupportingDocument(Document $document): ?Document
    {
        try {
            $content = $this->retrieveDocumentContentFromS3($document);
            $this->handleSiriusSync($document, $content);
            return $this->handleDocumentStatusUpdate($document, Document::SYNC_STATUS_SUCCESS);
        } catch (Throwable $e) {
            $this->handleSyncErrors($e, $document);
            return null;
        }
    }

    private function buildUpload(Document $document, string $content)
    {
        $report = $document->getReport();

        if ($document->isReportPdf()) {
            $siriusDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
                ->setReportingPeriodFrom($report->getStartDate())
                ->setReportingPeriodTo($this->determineEndDate($report))
                ->setYear((intval($report->getStartDate()->format('Y'))))
                ->setDateSubmitted($report->getSubmitDate())
                ->setType($this->determineReportType($report))
                ->setSubmissionId($document->getReportSubmission()->getId());

            $type = 'reports';
        } else {
            $siriusDocumentMetadata =
                (new SiriusSupportingDocumentMetadata())
                    ->setSubmissionId($document->getReportSubmission()->getId());

            $type = 'supportingdocuments';
        }

        $file = (new SiriusDocumentFile())
            ->setName($document->getFileName())
            ->setMimetype(mimetype_from_filename($document->getFileName()))
            ->setSource(base64_encode($content));

        return (new SiriusDocumentUpload())
            ->setType($type)
            ->setAttributes($siriusDocumentMetadata)
            ->setFile($file);
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
     * @return string
     */
    private function retrieveDocumentContentFromS3(Document $document)
    {
        return (string) $this->storage->retrieve($document->getStorageReference());
    }

    /**
     * @param Document $document
     * @param string $content
     * @return mixed|ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleSiriusSync(Document $document, string $content)
    {
        $upload = $this->buildUpload($document, $content);
        $caseRef = $document->getReport()->getClient()->getCaseNumber();

        if($document->isReportPdf()) {
            return $this->siriusApiGatewayClient->sendReportPdfDocument($upload, $caseRef);
        } else {
            /** @var ReportSubmission $reportPdfSubmission */
            $reportPdfSubmission = $document->getSyncedReportSubmission();
            return $this->siriusApiGatewayClient->sendSupportingDocument($upload, $reportPdfSubmission->getUuid(), $caseRef);
        }
    }

    /**
     * @param Document $document
     * @return Document|Throwable
     */
    private function handleDocumentStatusUpdate(Document $document, string $status, ?string $errorMessage=null)
    {
        $data = ['syncStatus' => $status];

        if (!is_null($errorMessage)) {
            $errorMessage = json_decode($errorMessage, true) ? json_decode($errorMessage, true) : $errorMessage;
            $data['syncError'] = $errorMessage;
        }

        try {
            return $this->restClient->apiCall(
                'put',
                sprintf('document/%s', $document->getId()),
                json_encode($data),
                'Report\\Document',
                [],
                false
            );
        } catch (Throwable $exception) {
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

    private function handleSyncErrors(Throwable $e, Document $document)
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

        $this->handleDocumentStatusUpdate($document, $syncStatus, $errorMessage);
    }
}
