<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\DocumentSyncService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends DaemonableCommand
{
    protected static $defaultName = 'digideps:document-sync';

    /** @var DocumentSyncService */
    private $documentSyncService;

    public function __construct(DocumentSyncService $documentSyncService)
    {
        $this->documentSyncService = $documentSyncService;

        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Uploads queued documents to Sirius and reports back the success');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->daemonize($input, $output, function() use ($output) {
            $documents = $this->getQueuedDocuments();
            $output->writeln(count($documents) . ' documents to upload');

            foreach ($documents as $document) {
                $this->documentSyncService->syncReportDocument($document);
            }
        });
    }

    private function getQueuedDocuments()
    {
        $doc = new Document();
        $doc->setFileName(mt_rand() . 'example.pdf');
        $doc->setStorageReference('example_ref');
        return [$doc];
    }
}
