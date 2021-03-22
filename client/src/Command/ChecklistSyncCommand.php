<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Exception\PdfGenerationFailedException;
use App\Exception\SiriusDocumentSyncFailedException;
use App\Model\Sirius\QueuedChecklistData;
use App\Service\ChecklistPdfGenerator;
use App\Service\ChecklistSyncService;
use App\Service\Client\RestClient;
use App\Service\ParameterStoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChecklistSyncCommand extends Command
{
    /** @var string */
    const FALLBACK_ROW_LIMITS = '30';

    /** @var string */
    public static $defaultName = 'digideps:checklist-sync';

    /** @var ChecklistPdfGenerator */
    private $pdfGenerator;

    /** @var ChecklistSyncService */
    private $syncService;

    /** @var RestClient */
    private $restClient;

    /** @var ParameterStoreService */
    private $parameterStore;

    /** @var int */
    private $notSyncedCount = 0;

    /**
     * @param ChecklistPdfGenerator $pdfGenerator
     * @param ChecklistSyncService $syncService
     * @param RestClient $restClient
     * @param ParameterStoreService $parameterStore
     * @param null $name
     */
    public function __construct(
        ChecklistPdfGenerator $pdfGenerator,
        ChecklistSyncService $syncService,
        RestClient $restClient,
        ParameterStoreService $parameterStore,
        $name = null
    ) {
        $this->pdfGenerator = $pdfGenerator;
        $this->syncService = $syncService;
        $this->restClient = $restClient;
        $this->parameterStore = $parameterStore;

        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');

        if (!$this->isFeatureEnabled()) {
            $output->writeln('Feature disabled, sleeping');
            return 0;
        }

        /** @var array $reports */
        $reports = $this->getReportsWithQueuedChecklists();
        $output->writeln(sprintf('%d checklists to upload', count($reports)));

        /** @var Report $report */
        foreach ($reports as $report) {
            try {
                $content = $this->pdfGenerator->generate($report);
            } catch (PdfGenerationFailedException $e) {
                $this->updateChecklistWithError($report, $e);
                $this->notSyncedCount += 1;
                continue;
            }

            try {
                $queuedChecklistData = $this->buildChecklistData($report, $content);
                $uuid = $this->syncService->sync($queuedChecklistData);
                $this->updateChecklistWithSuccess($report, $uuid);
            } catch (SiriusDocumentSyncFailedException $e) {
                $this->updateChecklistWithError($report, $e);
                $this->notSyncedCount += 1;
            }
        }

        if ($this->notSyncedCount > 0) {
            $output->writeln(sprintf('%d checklists failed to sync', $this->notSyncedCount));
            $this->notSyncedCount = 0;
        }

        return 0;
    }

    /**
     * @return bool
     */
    private function isFeatureEnabled(): bool
    {
        return $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_CHECKLIST_SYNC) === '1';
    }

    /**
     * @return QueuedChecklistData[]
     */
    private function getReportsWithQueuedChecklists(): array
    {
        return $this->restClient->apiCall(
            'get',
            'report/all-with-queued-checklists',
            ['row_limit' => $this->getSyncRowLimit()],
            'Report\Report[]',
            [],
            false
        );
    }

    /**
     * @return string
     */
    private function getSyncRowLimit(): string
    {
        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT);
        return $limit ? $limit : self::FALLBACK_ROW_LIMITS;
    }

    /**
     * @param Report $report
     * @param $content
     * @return QueuedChecklistData
     */
    protected function buildChecklistData(Report $report, $content): QueuedChecklistData
    {
        return (new QueuedChecklistData())
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

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Uploads queued checklists to Sirius and reports back the success');
    }

    /**
     * @param Report $report
     * @param $e
     */
    protected function updateChecklistWithError(Report $report, $e): void
    {
        $this->updateChecklist($report->getChecklist()->getId(), Checklist::SYNC_STATUS_PERMANENT_ERROR, $e->getMessage());
    }

    /**
     * @param Report $report
     * @param $uuid
     */
    protected function updateChecklistWithSuccess(Report $report, $uuid): void
    {
        $this->updateChecklist($report->getChecklist()->getId(), Checklist::SYNC_STATUS_SUCCESS, null, $uuid);
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
}
