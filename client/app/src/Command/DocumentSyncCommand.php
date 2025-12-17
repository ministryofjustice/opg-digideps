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
    public const string FALLBACK_ROW_LIMITS = '100';
    public const string COMPLETED_MESSAGE = 'Sync command completed';

    public static $defaultName = 'digideps:document-sync';

    public function __construct(
        private readonly DocumentSyncService $documentSyncService,
        private readonly RestClient $restClient,
        private readonly SerializerInterface $serializer,
        private readonly ParameterStoreService $parameterStore
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

        $isFeatureEnabled = '1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC);

        if (!$isFeatureEnabled) {
            $output->writeln('Feature disabled, sleeping');
            return 0;
        }

        $syncRowLimit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT);

        $queuedDocumentData = $this->restClient->apiCall(
            'get',
            'document/queued',
            ['row_limit' => $syncRowLimit ?? self::FALLBACK_ROW_LIMITS],
            'array',
            [],
            false
        );

        $documents = $this->serializer->deserialize($queuedDocumentData, 'App\Model\Sirius\QueuedDocumentData[]', 'json');

        $output->writeln(sprintf('%d documents to upload', count($documents)));

        /** @var QueuedDocumentData $document */
        foreach ($documents as $document) {
            $this->documentSyncService->syncDocument($document);
        }

        if (count($this->documentSyncService->getSyncErrorSubmissionIds()) > 0) {
            $output->writeln(
                sprintf(
                    'sync_documents_to_sirius - failure - %d documents failed to sync',
                    count($this->documentSyncService->getSyncErrorSubmissionIds())
                )
            );
            $this->documentSyncService->setSubmissionsDocumentsToPermanentError();
            $this->documentSyncService->setSyncErrorSubmissionIds([]);
        }

        if ($this->documentSyncService->getDocsNotSyncedCount() > 0) {
            $output->writeln(
                sprintf(
                    'sync_documents_to_sirius - success - %d documents remaining to sync',
                    $this->documentSyncService->getDocsNotSyncedCount()
                )
            );

            $this->documentSyncService->setDocsNotSyncedCount(0);
        } else {
            $output->writeln(sprintf('sync_documents_to_sirius - success - %s', self::COMPLETED_MESSAGE));
        }

        return 0;
    }
}
