<?php

namespace App\Command;

use App\Repository\DocumentRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResyncResubmittableErrorDocuments extends Command
{
    protected static $defaultName = 'digideps:resync-resubmittable-error-documents';

    public function __construct(private readonly DocumentRepository $documentRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $updatedDocuments = $this->documentRepository->getResubmittableErrorDocumentsAndSetToQueued('100');
            $output->writeln('resync_resubmittable_error_documents - success - Updated '.count($updatedDocuments).' documents back to QUEUED status');

            return 0;
        } catch (Exception $e) {
            $output->writeln('resync_resubmittable_error_documents - failure - Failed to update documents back to QUEUED status');
            $output->writeln($e);

            return 1;
        }
    }
}
