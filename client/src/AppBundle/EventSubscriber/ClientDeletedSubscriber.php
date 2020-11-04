<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\ClientDeletedEvent;
use AppBundle\Service\Audit\AuditEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientDeletedSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AuditEvents */
    private $auditEvents;

    public function __construct(LoggerInterface $logger, AuditEvents $auditEvents)
    {
        $this->logger = $logger;
        $this->auditEvents = $auditEvents;
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientDeletedEvent::NAME => 'logEvent'
        ];
    }

    public function logEvent(ClientDeletedEvent $event)
    {
        $this->logger->notice('', $this->auditEvents->clientDischarged(
            $event->getTrigger(),
            $event->getCaseNumber(),
            $event->getDischargedByEmail(),
            $event->getDischargedDeputyName(),
            $event->getDeputyshipStartDate()
        ));
    }
}
