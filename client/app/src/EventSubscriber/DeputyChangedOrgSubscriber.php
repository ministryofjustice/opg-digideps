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

    public function __construct(
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider
    ){
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeputyChangedOrgEvent::NAME => 'auditLog',
        ];
    }

    public function auditLog(DeputyChangedOrgEvent $event)
    {
            $deputyChangedOrgEvent = (new AuditEvents($this->dateTimeProvider))
                ->deputyChangedOrganisationEvent (
                    $event->getTrigger(),
                    $event->getDeputyId(),
                    $event->getPreviousOrgId(),
                    $event->getNewOrgId(),
                    $event->getClientId()
                );

            $this->logger->notice('', $deputyChangedOrgEvent);
        }
}
