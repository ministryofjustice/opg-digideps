<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ChecklistsSynchronisedEvent;
use App\Service\ChecklistSyncService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChecklistsSynchronisedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $verboseLogger,
        private ChecklistSyncService $checklistSyncService
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            ChecklistsSynchronisedEvent::NAME => 'synchroniseChecklists',
        ];
    }

    public function synchroniseChecklists(ChecklistsSynchronisedEvent $event)
    {
        $reports = $event->getReports();

        $notSyncedCount = $this->checklistSyncService->syncChecklistsByReports($reports);

        if ($notSyncedCount > 0) {
            $this->verboseLogger->notice(sprintf('%d checklists failed to sync', $notSyncedCount));
        }
    }
}
