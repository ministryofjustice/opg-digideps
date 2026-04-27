<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\UserAddedToOrganisationEvent;
use OPG\Digideps\Frontend\Event\UserRemovedFromOrganisationEvent;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
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
            UserRemovedFromOrganisationEvent::NAME => 'logUserRemovedEvent',
        ];
    }

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
