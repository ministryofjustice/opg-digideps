<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\DocumentsSynchronisedEvent;
use App\Service\DocumentSyncService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentsSynchronisedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $verboseLogger,
        private DocumentSyncService $documentSyncService
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            DocumentsSynchronisedEvent::NAME => 'synchroniseDocuments',
        ];
    }

    public function synchroniseDocuments(DocumentsSynchronisedEvent $event)
    {
        $documents = $event->getDocuments();
        foreach ($documents as $document) {
            $this->documentSyncService->syncDocument($document);
        }

        if (count($this->documentSyncService->getSyncErrorSubmissionIds()) > 0) {
            $this->documentSyncService->setSubmissionsDocumentsToPermanentError();
            $this->documentSyncService->setSyncErrorSubmissionIds([]);
        }

        if ($this->documentSyncService->getDocsNotSyncedCount() > 0) {
            $this->verboseLogger->notice(sprintf('%d documents failed to sync', $this->documentSyncService->getDocsNotSyncedCount()));
            $this->documentSyncService->setDocsNotSyncedCount(0);
        }
    }
}
