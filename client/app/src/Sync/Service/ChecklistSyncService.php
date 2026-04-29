<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Sync\Service;

use OPG\Digideps\Frontend\Entity\Report\Checklist;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Sync\Exception\PdfGenerationFailedException;
use OPG\Digideps\Frontend\Sync\Exception\SiriusDocumentSyncFailedException;
use OPG\Digideps\Frontend\Sync\Model\Sirius\QueuedChecklistData;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentFile;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentUpload;
use OPG\Digideps\Frontend\Sync\Service\Client\Sirius\SiriusApiGatewayClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\MimeType;
use Psr\Http\Message\ResponseInterface;

class ChecklistSyncService
{
    public const string PAPER_REPORT_UUID_FALLBACK = '99999999-9999-9999-9999-999999999999';

    public function __construct(
        private readonly RestClient $restClient,
        private readonly SiriusApiGatewayClient $siriusApiGatewayClient,
        private readonly SiriusApiErrorTranslator $errorTranslator,
        private readonly ChecklistPdfGenerator $pdfGenerator,
    ) {
    }

    public function sync(QueuedChecklistData $checklistData): string
    {
        try {
            $siriusResponse = $this->sendDocument($checklistData);

            /** @var array $body */
            $body = json_decode(strval($siriusResponse->getBody()), true);

            return (string) $body['data']['id'];
        } catch (\Exception $e) {
            $message = substr($e->getMessage(), 0, 254);

            if ($e instanceof ClientException) {
                $message = $this->errorTranslator->translateApiError((string) $e->getResponse()->getBody());
            }

            throw new SiriusDocumentSyncFailedException($message);
        }
    }

    /**
     * @throws GuzzleException
     */
    private function sendDocument(QueuedChecklistData $checklistData): ResponseInterface
    {
        $reportSubmission = $checklistData->getSyncedReportSubmission();

        $reportSubmissionUuid = self::PAPER_REPORT_UUID_FALLBACK;
        if ($reportSubmission instanceof ReportSubmission) {
            $actualUuid = $reportSubmission->getUuid();
            if (!is_null($actualUuid)) {
                $reportSubmissionUuid = $actualUuid;
            }
        }

        return is_null($checklistData->getChecklistUuid()) ?
            $this->postChecklist($checklistData, $reportSubmissionUuid) :
            $this->putChecklist($checklistData, $reportSubmissionUuid);
    }

    /**
     * @throws GuzzleException
     */
    private function postChecklist(QueuedChecklistData $checklistData, string $reportSubmissionUuid): ResponseInterface
    {
        $upload = $this->buildUpload($checklistData);

        return $this->siriusApiGatewayClient->postChecklistPdf(
            $upload,
            $reportSubmissionUuid,
            strtoupper($checklistData->getCaseNumber())
        );
    }

    /**
     * @throws GuzzleException
     */
    private function putChecklist(QueuedChecklistData $checklistData, string $reportSubmissionUuid): ResponseInterface
    {
        return $this->siriusApiGatewayClient->putChecklistPdf(
            $this->buildUpload($checklistData),
            $reportSubmissionUuid,
            strtoupper($checklistData->getCaseNumber()),
            $checklistData->getChecklistUuid() ?? self::PAPER_REPORT_UUID_FALLBACK
        );
    }

    private function buildUpload(QueuedChecklistData $checklistData): SiriusDocumentUpload
    {
        $filename = sprintf(
            'checklist-%s-%s-%s.pdf',
            $checklistData->getCaseNumber(),
            $checklistData->getReportStartDate()?->format('Y'),
            $checklistData->getReportEndDate()?->format('Y')
        );

        $file = new SiriusDocumentFile()
            ->setName($filename)
            ->setMimetype(MimeType::fromFilename($filename) ?? '')
            ->setSource(base64_encode($checklistData->getChecklistFileContents()));

        $submissionId = is_null($checklistData->getSyncedReportSubmission()) ?
            null : $checklistData->getSyncedReportSubmission()->getId();

        $metadata = new SiriusChecklistPdfDocumentMetadata()
            ->setYear(intval($checklistData->getReportEndDate()?->format('Y')))
            ->setType($checklistData->getReportType())
            ->setSubmitterEmail($checklistData->getSubmitterEmail())
            ->setReportingPeriodFrom($checklistData->getReportStartDate())
            ->setReportingPeriodTo($checklistData->getReportEndDate())
            ->setSubmissionId($submissionId);

        return new SiriusDocumentUpload()
            ->setType('checklists')
            ->setAttributes($metadata)
            ->setFile($file);
    }

    private function updateChecklist(int $id, string $status, ?string $message = null, ?string $uuid = null): void
    {
        $data = ['syncStatus' => $status];

        if (null !== $message) {
            $errorMessage = json_decode($message, true) ? json_decode($message, true) : $message;
            $data['syncError'] = $errorMessage;
        }

        if (null !== $uuid) {
            $data['uuid'] = $uuid;
        }

        $this->restClient->apiCall(
            'put',
            sprintf('checklist/%s', $id),
            json_encode($data),
            'raw',
            [],
            false
        );
    }

    public function syncChecklistsByReports(array $reports): int
    {
        $notSyncedCount = 0;

        /** @var Report $report */
        foreach ($reports as $report) {
            try {
                $content = $this->pdfGenerator->generate($report);
            } catch (PdfGenerationFailedException $e) {
                $this->updateChecklistWithError($report, $e);
                ++$notSyncedCount;
                continue;
            }

            try {
                $queuedChecklistData = $this->buildChecklistData($report, $content);
                $uuid = $this->sync($queuedChecklistData);
                $this->updateChecklistWithSuccess($report, $uuid);
            } catch (SiriusDocumentSyncFailedException $e) {
                $this->updateChecklistWithError($report, $e);
                ++$notSyncedCount;
            }
        }

        return $notSyncedCount;
    }

    protected function buildChecklistData(Report $report, string $content): QueuedChecklistData
    {
        return new QueuedChecklistData()
            ->setChecklistId($report->getChecklist()->getId())
            ->setChecklistUuid($report->getChecklist()->getUuid())
            ->setCaseNumber($report->getClient()->getCaseNumber())
            ->setChecklistFileContents($content)
            ->setReportStartDate($report->getStartDate())
            ->setReportEndDate($report->getEndDate())
            ->setReportSubmissions($report->getReportSubmissions())
            ->setSubmitterEmail($report->getChecklist()->getSubmittedBy()->getEmail())
            ->setReportType($report->determineReportType());
    }

    protected function updateChecklistWithError(Report $report, \Throwable $e): void
    {
        $this->updateChecklist($report->getChecklist()->getId(), Checklist::SYNC_STATUS_PERMANENT_ERROR, $e->getMessage());
    }

    protected function updateChecklistWithSuccess(Report $report, ?string $uuid): void
    {
        $this->updateChecklist($report->getChecklist()->getId(), Checklist::SYNC_STATUS_SUCCESS, null, $uuid);
    }
}
