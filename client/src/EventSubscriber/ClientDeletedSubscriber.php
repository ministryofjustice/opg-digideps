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
    /** @var LoggerInterface */
    private $logger;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
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
