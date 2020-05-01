<?php declare(strict_types=1);

namespace AppBundle\Command;


use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\FeatureFlagService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

class DocumentSyncCommand extends DaemonableCommand
{
    protected static $defaultName = 'digideps:document-sync';

    /** @var DocumentSyncService */
    private $documentSyncService;

    /** @var RestClient */
    private $restClient;

    /** @var FeatureFlagService */
    private $featureFlags;

    /** @var Serializer  */
    private $serializer;

    public function __construct(
        DocumentSyncService $documentSyncService,
        RestClient $restClient,
        FeatureFlagService $featureFlags,
        Serializer $serializer
    )
    {
        $this->documentSyncService = $documentSyncService;
        $this->restClient = $restClient;
        $this->featureFlags = $featureFlags;
        $this->serializer = $serializer;

        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Uploads queued documents to Sirius and reports back the success');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->daemonize($input, $output, function() use ($output) {
            if (!$this->isFeatureEnabled()) {
                $output->writeln('Feature disabled, sleeping');
                return;
            }

            /** @var QueuedDocumentData[] $documents */
            $documents = $this->getQueuedDocumentsData();

            $output->writeln(count($documents) . ' documents to upload');

            foreach ($documents as &$document) {
                $this->documentSyncService->syncDocument($document);
            }
        }, 3 * 60);
    }

    private function isFeatureEnabled(): bool
    {
        return $this->featureFlags->get(FeatureFlagService::FLAG_DOCUMENT_SYNC) === '1';
    }

    /**
     * @return QueuedDocumentData[]
     */
    private function getQueuedDocumentsData(): array
    {
        $queuedDocumentData = $this->restClient->apiCall('get', 'document/queued', [], 'array', [], false);

        return $this->serializer->deserialize($queuedDocumentData, 'AppBundle\Model\Sirius\QueuedDocumentData[]', 'json');
    }
}
