<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ClientDeletedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientDeletedSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private DateTimeProvider $dateTimeProvider)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientDeletedEvent::NAME => 'logEvent',
        ];
    }

    public function logEvent(ClientDeletedEvent $event)
    {
        $clientsDeputy = $event->getClientWithUsers()->getDeputy();
        $clientsDeputyName = (is_null($clientsDeputy) ? '' : $clientsDeputy->getFullName());

        $this->logger->notice('', (new AuditEvents($this->dateTimeProvider))->clientDischarged(
            $event->getTrigger(),
            $event->getClientWithUsers()->getCaseNumber(),
            $event->getCurrentUser()->getEmail(),
            $clientsDeputyName,
            $event->getClientWithUsers()->getCourtDate()
        ));
    }
}
