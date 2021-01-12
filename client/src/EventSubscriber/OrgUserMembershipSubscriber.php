<?php declare(strict_types=1);


namespace App\EventSubscriber;

use App\Event\UserAddedToOrganisationEvent;
use App\Event\UserRemovedFromOrganisationEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrgUserMembershipSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private DateTimeProvider $dateTimeProvider;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            UserAddedToOrganisationEvent::NAME => 'logUserAddedEvent',
            UserRemovedFromOrganisationEvent::NAME => 'logUserRemovedEvent'
        ];
    }

    /**
     * @param UserAddedToOrganisationEvent $event
     */
    public function logUserAddedEvent(UserAddedToOrganisationEvent $event)
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

    /**
     * @param UserRemovedFromOrganisationEvent $event
     */
    public function logUserRemovedEvent(UserRemovedFromOrganisationEvent $event)
    {
        $auditEvent = (new AuditEvents($this->dateTimeProvider))
            ->userRemovedFromOrg(
                $event->getTrigger(),
                $event->getRemovedUser(),
                $event->getOrganisation(),
                $event->getCurrentUser()
            );

        $this->logger->notice('', $auditEvent);
    }
}
