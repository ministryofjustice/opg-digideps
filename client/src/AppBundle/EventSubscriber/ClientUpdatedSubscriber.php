<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\ClientUpdatedEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientUpdatedEvent::NAME => 'logEvent'
        ];
    }

    public function logEvent(ClientUpdatedEvent $clientUpdatedEvent)
    {
        $event = (new AuditEvents($this->dateTimeProvider))->clientEmailChanged(
            $clientUpdatedEvent->getTrigger(),
            $clientUpdatedEvent->getPreUpdateClient()->getEmail(),
            $clientUpdatedEvent->getPostUpdateClient()->getEmail(),
            $clientUpdatedEvent->getChangedBy()->getEmail(),
            $clientUpdatedEvent->getPostUpdateClient()->getFullName(),
        );

        $message = empty($clientUpdatedEvent->getPostUpdateClient()->getEmail()) ? 'Client email address removed' : '';
        $this->logger->notice($message, $event);
    }
}
