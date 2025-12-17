<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Sirius\QueuedDocumentData;
use App\Service\Client\RestClient;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Run the document sync process.
 */
class DocumentSyncRunner
{
    public const string FALLBACK_ROW_LIMITS = '100';
    public const string COMPLETED_MESSAGE = 'Sync command completed';

    public function __construct(
        private readonly DocumentSyncService $documentSyncService,
        private readonly RestClient $restClient,
        private readonly SerializerInterface $serializer,
        private readonly ParameterStoreService $parameterStore
    ) {
    }

    public function run(OutputInterface $output): void
    {
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

        $countErrorIds = count($this->documentSyncService->getSyncErrorSubmissionIds());

        if ($countErrorIds > 0) {
            $output->writeln(
                sprintf(
                    'sync_documents_to_sirius - failure - %d documents failed to sync',
                    $countErrorIds
                )
            );
            $this->documentSyncService->setSubmissionsDocumentsToPermanentError();
            $this->documentSyncService->setSyncErrorSubmissionIds([]);
        }

        $docsNotSyncedCount = $this->documentSyncService->getDocsNotSyncedCount();

        if ($docsNotSyncedCount > 0) {
            $output->writeln(
                sprintf(
                    'sync_documents_to_sirius - success - %d documents remaining to sync',
                    $docsNotSyncedCount
                )
            );

            $this->documentSyncService->setDocsNotSyncedCount(0);
        } else {
            $output->writeln(sprintf('sync_documents_to_sirius - success - %s', self::COMPLETED_MESSAGE));
        }
    }
}
