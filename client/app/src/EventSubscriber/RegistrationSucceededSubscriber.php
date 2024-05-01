<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\RegistrationSucceededEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\UserApi;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationSucceededSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider,
        private UserApi $userApi
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            RegistrationSucceededEvent::DEPUTY => [
                ['logDeputyEvent', 2],
                ['updateRegistrationProps', 1],
            ],  RegistrationSucceededEvent::ADMIN => [
                ['logAdminEvent', 1],
            ],
        ];
    }

    public function logDeputyEvent(RegistrationSucceededEvent $event)
    {
        $this->logger->notice(
            '',
            (new AuditEvents($this->dateTimeProvider))->selfRegistrationSucceeded($event->getRegisteredUser())
        );
    }

    public function logAdminEvent(RegistrationSucceededEvent $event)
    {
        $this->logger->notice(
            '',
            (new AuditEvents($this->dateTimeProvider))->adminRegistrationSucceeded($event->getRegisteredUser())
        );
    }

    public function updateRegistrationProps(RegistrationSucceededEvent $event)
    {
        $preUpdatedUser = clone $event->getRegisteredUser();
        $updatedUser = clone $event->getRegisteredUser();

        $updatedUser->setRegistrationDate(new \DateTime());
        $updatedUser->setActive(true);

        $this->userApi->update($preUpdatedUser, $updatedUser, AuditEvents::TRIGGER_DEPUTY_USER_REGISTRATION_FLOW_COMPLETED);
    }
}
