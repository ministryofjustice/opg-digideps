<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ClientArchivedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientArchivedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger, 
        private readonly DateTimeProvider $dateTimeProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ClientArchivedEvent::NAME => 'logEvent',
        ];
    }

    public function logEvent(ClientArchivedEvent $event): void
    {
        $this->logger->notice('', (new AuditEvents($this->dateTimeProvider))->clientArchived(
            $event->getTrigger(),
            $event->getClient()->getCaseNumber(),
            $event->getClient()->getCourtDate(),
            $event->getCurrentUser()->getEmail(),
        ));
    }
}
