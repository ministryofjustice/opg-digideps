<?php

declare(strict_types=1);

namespace App\Sync\Command;

use App\Service\Client\Internal\ReportApi;
use App\Service\ParameterStoreService;
use App\Sync\Service\ChecklistSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('digideps:checklist-sync')]
class ChecklistSyncCommand extends Command
{
    public const string FALLBACK_ROW_LIMITS = '30';
    public const string COMPLETED_MESSAGE = 'sync_checklists_to_sirius - success - Sync command completed';

    public function __construct(
        private readonly ChecklistSyncService $syncService,
        private readonly ParameterStoreService $parameterStore,
        private readonly ReportApi $reportApi,
    ) {
        parent::__construct();
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
            $output->writeln(sprintf('sync_checklists_to_sirius - failure - %d checklists failed to sync', $notSyncedCount));
        } else {
            $output->writeln(self::COMPLETED_MESSAGE);
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
