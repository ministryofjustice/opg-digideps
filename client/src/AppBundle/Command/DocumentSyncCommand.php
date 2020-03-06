<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends DaemonableCommand
{
    protected static $defaultName = 'digideps:document-sync';

    /** @var DocumentSyncService */
    private $documentSyncService;

    /** @var RestClient */
    private $restClient;

    public function __construct(DocumentSyncService $documentSyncService, RestClient $restClient)
    {
        $this->documentSyncService = $documentSyncService;
        $this->restClient = $restClient;

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
        }, 5 * 60);
    }

    private function getQueuedDocuments()
    {
        $options = [
            'query' => [
                'groups' => ['documents', 'document-synchronisation', 'document-storage-reference', 'document-report', 'report', 'report-client', 'client-case-number']
            ]
        ];

        return $this->restClient->apiCall('get', 'document/queued', [], 'Report\\Document[]', $options, false);
    }
}
