<?php

namespace OPG\Digideps\Backend\Command;

use OPG\Digideps\Backend\Cleanup\ReportCleaner;
use OPG\Digideps\Backend\Exception\NotFound;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\Service\File\Storage\S3Storage;
use OPG\Digideps\Common\Cleanup\CleanupModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCleanupCommand extends Command
{
    public function __construct(
        private readonly ReportCleaner $reportCleaner,
        private readonly ClientRepository $clientRepository,
        private readonly S3Storage $s3Storage,
    ) {
        parent::__construct('digideps:cleanup:reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $model = new CleanupModel(null, $input->hasArgument('not-dry-run'), $input->hasArgument('allow-not-continuous'));
        $clientIds = [];
        if ($model->caseNumber !== null) {
            array_push($clientIds, ...array_map(fn (string $caseNumber): int => $this->clientRepository->findByCaseNumber($caseNumber)?->getId() ?? throw new NotFound("Client with case number {$caseNumber}"), explode(',', $model->caseNumber)));
        }
        $csv = $this->reportCleaner->clean(!$model->notDryRun, $model->allowNotContinuous, ...$clientIds);
        $this->s3Storage->store('ReportCleaner.csv', $csv);
        return 0;
    }
}
