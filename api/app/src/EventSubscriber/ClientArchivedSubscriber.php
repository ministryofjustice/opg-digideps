<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\EventSubscriber;

use OPG\Digideps\Backend\Event\ClientArchivedEvent;
use OPG\Digideps\Backend\Service\Audit\AuditEvents;
use OPG\Digideps\Backend\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientArchivedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DateTimeProvider $dateTimeProvider
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientArchivedEvent::NAME => 'logEvent',
        ];
    }

    public function logEvent(ClientArchivedEvent $event)
    {
        $this->logger->notice('', new AuditEvents($this->dateTimeProvider)->clientArchived(
            $event->getTrigger(),
            $event->getClient()->getCaseNumber(),
            $event->getClient()->getCourtDate(),
            $event->getCurrentUser()->getEmail(),
        ));
    }
}
