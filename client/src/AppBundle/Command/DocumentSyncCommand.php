<?php declare(strict_types=1);

namespace AppBundle\Command;


use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\FeatureFlagService;
use AppBundle\Service\ParameterStoreService;
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

    /** @var ParameterStoreService */
    private $parameterStore;

    public function __construct(
        DocumentSyncService $documentSyncService,
        RestClient $restClient,
        FeatureFlagService $featureFlags,
        Serializer $serializer,
        ParameterStoreService $parameterStore
    )
    {
        $this->documentSyncService = $documentSyncService;
        $this->restClient = $restClient;
        $this->featureFlags = $featureFlags;
        $this->serializer = $serializer;
        $this->parameterStore = $parameterStore;

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

            foreach ($documents as $document) {
                $this->documentSyncService->syncDocument($document);
            }
        }, (int) $this->getSyncIntervalMinutes() * 60);
    }

    private function isFeatureEnabled(): bool
    {
        return $this->featureFlags->get(FeatureFlagService::FLAG_DOCUMENT_SYNC) === '1';
    }

    private function getSyncIntervalMinutes(): string
    {
        return $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_INTERVAL_MINUTES);
    }

    private function getSyncRowLimit(): string
    {
        return $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT);
    }

    /**
     * @return QueuedDocumentData[]
     */
    private function getQueuedDocumentsData(): array
    {
        $queuedDocumentData = $this->restClient->apiCall(
            'get',
            'document/queued',
            ['row_limit' => $this->getSyncRowLimit()],
            'array',
            [],
            false
        );

        return $this->serializer->deserialize($queuedDocumentData, 'AppBundle\Model\Sirius\QueuedDocumentData[]', 'json');
    }
}
