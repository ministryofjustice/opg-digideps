<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ChecklistSyncService;
use App\Service\Client\Internal\ReportApi;
use App\Service\ParameterStoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChecklistSyncCommand extends Command
{
    /** @var string */
    public const FALLBACK_ROW_LIMITS = '30';
    public const COMPLETED_MESSAGE = 'Sync command completed';

    /** @var string */
    public static $defaultName = 'digideps:checklist-sync';

    public function __construct(
        private ChecklistSyncService $syncService,
        private ParameterStoreService $parameterStore,
        private ReportApi $reportApi,
        $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');

        if (!$this->isFeatureEnabled()) {
            $output->writeln('Feature disabled, sleeping');

            return 0;
        }

        $rowLimit = $this->getSyncRowLimit();

        /** @var array $reports */
        $reports = $this->reportApi->getReportsWithQueuedChecklists($rowLimit);
        $output->writeln(sprintf('%d checklists to upload', count($reports)));

        $notSyncedCount = $this->syncService->syncChecklistsByReports($reports);

        if ($notSyncedCount > 0) {
            $output->writeln(sprintf('sync_checklists_check - failure - %d checklists failed to sync', $notSyncedCount));
        } else {
            $output->writeln(sprintf('sync_checklists_check - success - %d', self::COMPLETED_MESSAGE));
        }

        return 0;
    }

    private function isFeatureEnabled(): bool
    {
        return '1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_CHECKLIST_SYNC);
    }

    protected function configure(): void
    {
        $this->setDescription('Uploads queued checklists to Sirius and reports back the success');
    }

    private function getSyncRowLimit(): string
    {
        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT);

        return $limit ?: self::FALLBACK_ROW_LIMITS;
    }
}
