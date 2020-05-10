<?php declare(strict_types=1);

namespace AppBundle\Command;


use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\ParameterStoreService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

class DocumentSyncCommand extends DaemonableCommand
{
    const FALLBACK_INTERVAL_MINUTES = '4.5';
    const FALLBACK_ROW_LIMITS = '100';

    protected static $defaultName = 'digideps:document-sync';

    /** @var DocumentSyncService */
    private $documentSyncService;

    /** @var RestClient */
    private $restClient;

    /** @var Serializer  */
    private $serializer;

    /** @var ParameterStoreService */
    private $parameterStore;

    public function __construct(
        DocumentSyncService $documentSyncService,
        RestClient $restClient,
        Serializer $serializer,
        ParameterStoreService $parameterStore
    )
    {
        $this->documentSyncService = $documentSyncService;
        $this->restClient = $restClient;
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
        ini_set('memory_limit', '512M');

        if (!$this->isFeatureEnabled()) {
            $output->writeln('Feature disabled, sleeping');
            return 0;
        }

        print_r('Memory usage at start is.........');
        var_dump(memory_get_usage(true));

        /** @var QueuedDocumentData[] $documents */
        $documents = $this->getQueuedDocumentsData();

        $output->writeln(sprintf('%d documents to upload', count($documents)));

        print_r('Memory usage after getting docs is.........');
        var_dump(memory_get_usage(true));

        foreach ($documents as &$document) {
            $this->documentSyncService->syncDocument($document);
        }

        print_r('Memory usage after syncing is.........');
        var_dump(memory_get_usage(true));

        if ($this->documentSyncService->getSyncErrorSubmissionIds()) {
            $documentsUpdated = $this->documentSyncService->setSubmissionsDocumentsToPermanentError();
            $output->writeln(sprintf('%d documents failed to sync', $documentsUpdated));
            $this->documentSyncService->setSyncErrorSubmissionIds([]);
        }

        return 0;
    }

    private function isFeatureEnabled(): bool
    {
        return $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC) === '1';
    }

    private function getSyncIntervalMinutes(): string
    {
        $minutes = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_INTERVAL_MINUTES);
        return $minutes ? $minutes : self::FALLBACK_INTERVAL_MINUTES;
    }

    private function getSyncRowLimit(): string
    {
        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT);
        return $limit ? $limit : self::FALLBACK_ROW_LIMITS;
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
