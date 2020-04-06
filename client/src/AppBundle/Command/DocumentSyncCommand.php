<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\FeatureFlagService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends DaemonableCommand
{
    protected static $defaultName = 'digideps:document-sync';

    /** @var DocumentSyncService */
    private $documentSyncService;

    /** @var RestClient */
    private $restClient;

    /** @var FeatureFlagService */
    private $featureFlags;

    public function __construct(DocumentSyncService $documentSyncService, RestClient $restClient, FeatureFlagService $featureFlags)
    {
        $this->documentSyncService = $documentSyncService;
        $this->restClient = $restClient;
        $this->featureFlags = $featureFlags;

        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Uploads queued documents to Sirius and reports back the success');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->daemonize($input, $output, function() use ($output) {
            if (!$this->isFeatureEnabled()) {
                $output->writeln('Feature disabled, sleeping');
                return;
            }

            $documents = $this->getQueuedDocuments();
            $output->writeln(count($documents) . ' documents to upload');

            foreach ($documents as $document) {
                $this->documentSyncService->syncDocument($document);
            }
        }, 5 * 60);
    }

    private function isFeatureEnabled(): bool
    {
        return $this->featureFlags->get(FeatureFlagService::FLAG_DOCUMENT_SYNC) === '1';
    }

    /**
     * @return Document[]
     */
    private function getQueuedDocuments(): array
    {
        $options = [
            'query' => [
                'groups' => ['documents', 'document-synchronisation', 'document-storage-reference', 'document-report', 'report', 'report-client', 'client-case-number']
            ]
        ];

        return $this->restClient->apiCall('get', 'document/queued', [], 'Report\\Document[]', $options, false);
    }
}
