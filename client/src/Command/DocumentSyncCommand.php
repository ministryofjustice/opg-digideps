<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Sirius\QueuedDocumentData;
use App\Service\Client\RestClient;
use App\Service\DocumentSyncService;
use App\Service\ParameterStoreService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentSyncCommand extends DaemonableCommand
{
    const FALLBACK_ROW_LIMITS = '100';

    public static $defaultName = 'digideps:document-sync';

    public function __construct(
        private DocumentSyncService $documentSyncService,
        private RestClient $restClient,
        private SerializerInterface $serializer,
        private ParameterStoreService $parameterStore
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

        if (!$this->isFeatureEnabled()) {
            $output->writeln('Feature disabled, sleeping');

            return 0;
        }

        /** @var QueuedDocumentData[] $documents */
        $documents = $this->getQueuedDocumentsData();

        $output->writeln(sprintf('%d documents to upload', count($documents)));

        foreach ($documents as $document) {
            $this->documentSyncService->syncDocument($document);
        }

        if (count($this->documentSyncService->getSyncErrorSubmissionIds()) > 0) {
            $this->documentSyncService->setSubmissionsDocumentsToPermanentError();
            $this->documentSyncService->setSyncErrorSubmissionIds([]);
        }

        if ($this->documentSyncService->getDocsNotSyncedCount() > 0) {
            $output->writeln(sprintf('%d documents failed to sync', $this->documentSyncService->getDocsNotSyncedCount()));
            $this->documentSyncService->setDocsNotSyncedCount(0);
        }

        return 0;
    }

    private function isFeatureEnabled(): bool
    {
        return '1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC);
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

        return $this->serializer->deserialize($queuedDocumentData, 'App\Model\Sirius\QueuedDocumentData[]', 'json');
    }
}
