<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use function GuzzleHttp\Psr7\mimetype_from_filename;
use Throwable;
use Twig\Environment;

class ChecklistSyncService
{
    /** @var RestClient */
    private $restClient;

    /** @var SiriusApiGatewayClient */
    private $siriusApiGatewayClient;

    /** @var Environment container */
    private $templating;

    /** @var WkHtmlToPdfGenerator */
    private $wkhtmltopdf;

    /** @var SiriusApiErrorTranslator */
    private $errorTranslator;

    /** @var int */
    private $notSyncedCount;

    /**
     * @param RestClient $restClient
     * @param SiriusApiGatewayClient $siriusApiGatewayClient
     * @param Environment $templating
     * @param WkHtmlToPdfGenerator $wkhtmltopdf
     * @param SiriusApiErrorTranslator $errorTranslator
     */
    public function __construct(
        RestClient $restClient,
        SiriusApiGatewayClient $siriusApiGatewayClient,
        Environment $templating,
        WkHtmlToPdfGenerator $wkhtmltopdf,
        SiriusApiErrorTranslator $errorTranslator
    )
    {
        $this->restClient = $restClient;
        $this->siriusApiGatewayClient = $siriusApiGatewayClient;
        $this->templating = $templating;
        $this->wkhtmltopdf = $wkhtmltopdf;
        $this->errorTranslator = $errorTranslator;
    }

    /**
     * @param QueuedChecklistData $checklistData
     * @return null
     */
    public function sync(QueuedChecklistData $checklistData): void
    {
        $report = $checklistData->getReport();

        try {
            $content = $this->generateDocument($report);
        } catch (Throwable $e) {
            $this->notSyncedCount += 1;
            $this->updateChecklist($report->getChecklist()->getId(), 'PDF_GENERATION_ERROR', $e->getMessage());

            return;
        }

        try {
            $siriusResponse = $this->sendDocument($content, $report);
            $uuid = json_decode(strval($siriusResponse->getBody()), true)['data']['id'];
            $this->updateChecklist($report->getChecklist()->getId(), Checklist::SYNC_STATUS_SUCCESS, null, $uuid);
        } catch (Throwable $e) {
            $this->notSyncedCount += 1;
            $this->updateChecklist($report->getChecklist()->getId(), Checklist::SYNC_STATUS_PERMANENT_ERROR, $this->determineErrorMessage($e));
        }
    }

    /**
     * @param Report $report
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function generateDocument(Report $report): string
    {
        $html = $this->templating->render('AppBundle:Admin/Client/Report/Formatted:checklist_formatted_standalone.html.twig', [
            'report' => $report,
            'lodgingChecklist' => $report->getChecklist(),
            'reviewChecklist' => $report->getReviewChecklist()
        ]);

        return $this->wkhtmltopdf->getPdfFromHtml($html);
    }

    /**
     * @param string $content
     * @param QueuedChecklistData $checklistData
     * @return mixed|\Psr\Http\Message\ResponseInterface|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendDocument(string $content, QueuedChecklistData $checklistData)
    {
        if (null === $reportSubmission = $checklistData->getSyncedReportSubmission()) {
            // Can't yet sync as Report has not been synced.
            return;
        }

        $report = $checklistData->getReport();

        return $this->siriusApiGatewayClient->sendChecklistPdf(
            $this->buildUpload($content, $report),
            $reportSubmission->getUuid(),
            strtoupper($report->getClient()->getCaseNumber())
        );
    }

    /**
     * @param string $content
     * @param Report $report
     * @return SiriusDocumentUpload
     */
    private function buildUpload(string $content, Report $report): SiriusDocumentUpload
    {
        $filename = sprintf('checklist-%s-%s-%s.pdf',
            $report->getClient()->getCaseNumber(),
            $report->getStartDate()->format('Y'),
            $report->getEndDate()->format('Y')
        );

        $file = (new SiriusDocumentFile())
            ->setName($filename)
            ->setMimetype(mimetype_from_filename($filename))
            ->setSource(base64_encode($content));

        return (new SiriusDocumentUpload())
            ->setType('checklists')
            ->setAttributes(null)
            ->setFile($file);
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
     * @return array
     */
    public function getSyncErrorSubmissionIds(): array
    {
        return [];
    }

    /**
     * @param array $ids
     */
    public function setSyncErrorSubmissionIds(array $ids): void
    {

    }

    /**
     * @return int
     */
    public function getChecklistsNotSyncedCount(): int
    {
        return $this->notSyncedCount;
    }

    /**
     * @param int $count
     */
    public function setChecklistsNotSyncedCount(int $count): void
    {
        $this->notSyncedCount = $count;
    }

    public function setChecklistsToPermanentError(): void
    {

    }

    /**
     * @param $e
     * @return string
     */
    private function determineErrorMessage($e): string
    {
        if (method_exists($e, 'getResponse') && method_exists($e->getResponse(), 'getBody')) {
            $errorMessage = $this->errorTranslator->translateApiError((string)$e->getResponse()->getBody());
        } else {
            $errorMessage = (string)$e->getMessage();
        }
        return $errorMessage;
    }

}
