<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Exception\SiriusDocumentSyncFailedException;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use function GuzzleHttp\Psr7\mimetype_from_filename;
use Throwable;

class ChecklistSyncService
{
    /** @var RestClient */
    private $restClient;

    /** @var SiriusApiGatewayClient */
    private $siriusApiGatewayClient;

    /** @var SiriusApiErrorTranslator */
    private $errorTranslator;

    /** @var int */
    const FAILED_TO_SYNC = -1;

    /** @var string */
    const PAPER_REPORT_UUID_FALLBACK = '99999999-9999-9999-9999-999999999999';

    /**
     * @param RestClient $restClient
     * @param SiriusApiGatewayClient $siriusApiGatewayClient
     * @param SiriusApiErrorTranslator $errorTranslator
     */
    public function __construct(
        RestClient $restClient,
        SiriusApiGatewayClient $siriusApiGatewayClient,
        SiriusApiErrorTranslator $errorTranslator
    )
    {
        $this->restClient = $restClient;
        $this->siriusApiGatewayClient = $siriusApiGatewayClient;
        $this->errorTranslator = $errorTranslator;
    }

    /**
     * @param QueuedChecklistData $checklistData
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
     * @param QueuedChecklistData $checklistData
     * @return mixed|\Psr\Http\Message\ResponseInterface|void
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
     * @param QueuedChecklistData $checklistData
     * @param string $reportSubmissionUuid
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function postChecklist(QueuedChecklistData $checklistData, string $reportSubmissionUuid)
    {
        return $this->siriusApiGatewayClient->postChecklistPdf(
            $this->buildUpload($checklistData),
            $reportSubmissionUuid,
            strtoupper($checklistData->getCaseNumber())
        );
    }

    /**
     * @param QueuedChecklistData $checklistData
     * @param string $reportSubmissionUuid
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function putChecklist(QueuedChecklistData $checklistData, string $reportSubmissionUuid)
    {
//        return $this->siriusApiGatewayClient->putChecklistPdf(
//            $this->buildUpload($checklistData),
//            $reportSubmissionUuid,
//            strtoupper($checklistData->getCaseNumber())
//            $checklistData->getChecklistUuid()
//        );
    }

    /**
     * @param string $content
     * @param Report $report
     * @return SiriusDocumentUpload
     */
    private function buildUpload(QueuedChecklistData $checklistData): SiriusDocumentUpload
    {
        $filename = sprintf('checklist-%s-%s-%s.pdf',
            $checklistData->getCaseNumber(),
            $checklistData->getReportStartDate()->format('Y'),
            $checklistData->getReportEndDate()->format('Y')
        );

        $file = (new SiriusDocumentFile())
            ->setName($filename)
            ->setMimetype(mimetype_from_filename($filename))
            ->setSource(base64_encode($checklistData->getChecklistFileContents()));

        return (new SiriusDocumentUpload())
            ->setType('checklists')
            ->setAttributes(new SiriusChecklistPdfDocumentMetadata())
            ->setFile($file);
    }

    /**
     * @param $e
     * @return string
     */
    private function determineErrorMessage(\Throwable $e): string
    {
        return ($this->errorCanBeTranslated($e)) ?
            $this->errorTranslator->translateApiError((string)$e->getResponse()->getBody()) :
            substr($e->getMessage(), 0, 254);

    }

    /**
     * @param int $id
     * @param string $status
     * @param string|null $message
     * @param string|null $uuid
     */
    private function updateChecklist(int $id, string $status, string $message = null, string $uuid = null): void
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

    /**
     * @param Throwable $e
     * @return bool
     */
    private function errorCanBeTranslated(\Throwable $e): bool
    {
        return
            method_exists($e, 'getResponse') &&
            method_exists($e->getResponse(), 'getBody') &&
            is_array($e->getResponse()->getBody()) &&
            isset($e->getResponse()->getBody()['errors']);
    }
}
