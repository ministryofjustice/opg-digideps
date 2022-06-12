<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\OrgCreatedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrgCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrgCreatedEvent::NAME => 'auditLog',
        ];
    }

    public function auditLog(OrgCreatedEvent $event)
    {
        $orgCreatedEvent = (new AuditEvents($this->dateTimeProvider))
            ->orgCreated(
                $event->getTrigger(),
                $event->getCurrentUser(),
                $event->getOrganisation()
            );

        $this->logger->notice('', $orgCreatedEvent);
    }
}
