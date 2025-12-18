<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DocumentSyncRunner;
use App\Service\ParameterStoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends Command
{
    public static $defaultName = 'digideps:document-sync';

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

        $this->documentSyncRunner->run($output);

        return 0;
    }
}
