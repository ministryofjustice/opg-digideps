<?php

declare(strict_types=1);

namespace App\Sync\Command;

use App\Service\ParameterStoreService;
use App\Sync\Service\DocumentSyncRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends Command
{
    public static $defaultName = 'digideps:document-sync';

    public const int FALLBACK_ROW_LIMITS = 100;

    public function __construct(
        private readonly ParameterStoreService $parameterStore,
        private readonly DocumentSyncRunner $documentSyncRunner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Uploads queued documents to Sirius and reports back the success');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');

        $isFeatureEnabled = ('1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC));

        if (!$isFeatureEnabled) {
            $output->writeln('Feature disabled, sleeping');
            return 0;
        }

        $syncRowLimit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT);

        if (is_null($syncRowLimit)) {
            $syncRowLimit = self::FALLBACK_ROW_LIMITS;
        }

        $this->documentSyncRunner->run($output, intval($syncRowLimit));

        return 0;
    }
}
