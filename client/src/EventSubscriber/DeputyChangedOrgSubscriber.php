<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\DeputyChangedOrgEvent;
use App\Service\Audit\AuditEvents;
use App\Service\DateTimeProvider;
use App\Service\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeputyChangedOrgSubscriber implements EventSubscriberInterface
{
    private DateTimeProvider $dateTimeProvider;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeputyChangedOrgEvent::NAME => 'auditLog',
        ];
    }

    public function auditLog(DeputyChangedOrgEvent $event)
    {
// deputy has changed organisation
// deputy has same client id/case numbers - log

        if ($this->deputyOrganisationHasChanged($event) || ($this->deputyOrganisationHasChanged($event) && $this->clientHasNotChanged($event))) {
            $deputyChangedOrgEvent = (new AuditEvents($this->dateTimeProvider))
                ->deputyChangedOrg(
                    $event->getTrigger(),
                    $event->getPostUpdateDeputy()->getFullName(),
                    $event->getPreUpdateDeputy()->getOrganisations(),
                    $event->getPostUpdateDeputy()->getOrganisations(),
//                    $event->getPreUpdateClient()->getDeputy(),
//                    $event->getPostUpdateClient()->getDeputy(),
                    $event->getPreUpdateDeputy()->getClients(),
                    $event->getPostUpdateDeputy()->getClients(),
                );
            $this->logger->notice('', $deputyChangedOrgEvent);
        }
    }

    private function deputyOrganisationHasChanged(DeputyChangedOrgEvent $event): bool
    {
        return $event->getPreUpdateDeputy()->getOrganisations() !== $event->getPostUpdateDeputy()->getOrganisations();
    }

    private function clientHasNotChanged(DeputyChangedOrgEvent $event): bool
    {
//        if postUpdateDeputy org has changed but their clients named deputy is the same return true

        $deputyId = $event->getPostUpdateDeputy()->getId();
        $preUpdateClientsDeputyId = $event->getPreUpdateClient()->getNamedDeputy()->getId();
        $postUpdateClientsDeputyId = $event->getPostUpdateClient()->getNamedDeputy()->getId();

        return $event->getPreUpdateDeputy()->getClients() === $event->getPostUpdateDeputy()->getClients();
    }
}


