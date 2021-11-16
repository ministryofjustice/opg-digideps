<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Exception\SiriusDocumentSyncFailedException;
use App\Model\Sirius\QueuedChecklistData;
use App\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use App\Model\Sirius\SiriusDocumentFile;
use App\Model\Sirius\SiriusDocumentUpload;
use App\Service\Client\RestClient;
use App\Service\Client\Sirius\SiriusApiGatewayClient;
use function GuzzleHttp\Psr7\mimetype_from_filename;
use Throwable;

class ChecklistSyncService
{
    /** @var int */
    const FAILED_TO_SYNC = -1;

    /** @var string */
    const PAPER_REPORT_UUID_FALLBACK = '99999999-9999-9999-9999-999999999999';

    public function __construct(private SiriusApiGatewayClient $siriusApiGatewayClient, private SiriusApiErrorTranslator $errorTranslator)
    {
    }

    /**
     * @return mixed
     */
    public function sync(QueuedChecklistData $checklistData)
    {
        try {
            $siriusResponse = $this->sendDocument($checklistData);

            return json_decode(strval($siriusResponse->getBody()), true)['data']['id'];
        } catch (Throwable $e) {
            throw new SiriusDocumentSyncFailedException($this->determineErrorMessage($e));
        }
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface|void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendDocument(QueuedChecklistData $checklistData)
    {
        $reportSubmission = $checklistData->getSyncedReportSubmission();
        $reportSubmissionUuid = ($reportSubmission instanceof ReportSubmission) ?
            $reportSubmission->getUuid() :
            self::PAPER_REPORT_UUID_FALLBACK;

        return (null === $checklistData->getChecklistUuid()) ?
            $this->postChecklist($checklistData, $reportSubmissionUuid) :
            $this->putChecklist($checklistData, $reportSubmissionUuid);
    }

    /**
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function postChecklist(QueuedChecklistData $checklistData, string $reportSubmissionUuid)
    {
        $upload = $this->buildUpload($checklistData);

        return $this->siriusApiGatewayClient->postChecklistPdf(
            $upload,
            $reportSubmissionUuid,
            strtoupper($checklistData->getCaseNumber())
        );
    }

    /**
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function putChecklist(QueuedChecklistData $checklistData, string $reportSubmissionUuid)
    {
        return $this->siriusApiGatewayClient->putChecklistPdf(
            $this->buildUpload($checklistData),
            $reportSubmissionUuid,
            strtoupper($checklistData->getCaseNumber()),
            $checklistData->getChecklistUuid()
        );
    }

    /**
     * @param string $content
     * @param Report $report
     */
    private function buildUpload(QueuedChecklistData $checklistData): SiriusDocumentUpload
    {
        $filename = sprintf(
            'checklist-%s-%s-%s.pdf',
            $checklistData->getCaseNumber(),
            $checklistData->getReportStartDate()->format('Y'),
            $checklistData->getReportEndDate()->format('Y')
        );

        $file = (new SiriusDocumentFile())
            ->setName($filename)
            ->setMimetype(mimetype_from_filename($filename))
            ->setSource(base64_encode($checklistData->getChecklistFileContents()));

        $submissionId = is_null($checklistData->getSyncedReportSubmission()) ?
            null : $checklistData->getSyncedReportSubmission()->getId();

        $metadata = (new SiriusChecklistPdfDocumentMetadata())
            ->setYear((int) $checklistData->getReportEndDate()->format('Y'))
            ->setType($checklistData->getReportType())
            ->setSubmitterEmail($checklistData->getSubmitterEmail())
            ->setReportingPeriodFrom($checklistData->getReportStartDate())
            ->setReportingPeriodTo($checklistData->getReportEndDate())
            ->setSubmissionId($submissionId);

        return (new SiriusDocumentUpload())
            ->setType('checklists')
            ->setAttributes($metadata)
            ->setFile($file);
    }

    /**
     * @param $e
     */
    private function determineErrorMessage(Throwable $e): string
    {
        return ($this->errorCanBeTranslated($e)) ?
            $this->errorTranslator->translateApiError((string) $e->getResponse()->getBody()) :
            substr($e->getMessage(), 0, 254);
    }

    private function errorCanBeTranslated(Throwable $e): bool
    {
        return
            method_exists($e, 'getResponse') &&
            method_exists($e->getResponse(), 'getBody') &&
            is_array($e->getResponse()->getBody()) &&
            isset($e->getResponse()->getBody()['errors']);
    }
}
