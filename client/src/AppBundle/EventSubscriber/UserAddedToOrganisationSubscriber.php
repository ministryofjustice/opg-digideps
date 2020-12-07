<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\UserAddedToOrganisationEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserAddedToOrganisationSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private DateTimeProvider $dateTimeProvider;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserAddedToOrganisationEvent::NAME => 'logEvent'
        ];
    }

    public function logEvent(UserAddedToOrganisationEvent $event)
    {
        $auditEvent = (new AuditEvents($this->dateTimeProvider))
            ->userAddedToOrg(
                $event->getTrigger(),
                $event->getAddedUser(),
                $event->getOrganisation(),
                $event->getCurrentUser()
            );

        $this->logger->notice('', $auditEvent);
    }
}
