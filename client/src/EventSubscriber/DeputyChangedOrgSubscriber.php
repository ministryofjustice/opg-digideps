<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\DeputyChangedOrgEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyChangedOrgSubscriber implements EventSubscriberInterface
{
    private DateTimeProvider $dateTimeProvider;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeputyChangedOrgEvent::NAME => 'auditLog',
        ];
    }

    public function auditLog(DeputyChangedOrgEvent $event)
    {

        if ($event->getPreviousDeputyOrg()->getOrganisation() !== $event->getClient()->getOrganisation()
            && $event->getPreviousDeputyOrg()->getCaseNumber() === $event->getClient()->getCaseNumber()) {

            $deputyChangedOrgEvent = (new AuditEvents($this->dateTimeProvider))
                ->deputyChangedOrganisationEvent (
                    $event->getTrigger(),
                    $event->getPreviousDeputyOrg(),
                    $event->getClient()
                );

            $this->logger->notice('', $deputyChangedOrgEvent);
        }
    }
}


