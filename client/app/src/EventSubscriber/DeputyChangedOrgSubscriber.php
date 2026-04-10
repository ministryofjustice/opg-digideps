<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\DeputyChangedOrgEvent;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyChangedOrgSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider
    ) {
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
                ->deputyChangedOrganisationEvent(
                    $event->getTrigger(),
                    $event->getDeputyId(),
                    $event->getPreviousOrgId(),
                    $event->getNewOrgId(),
                    $event->getClientId()
                );

            $this->logger->notice('', $deputyChangedOrgEvent);
    }
}
