<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\RegistrationSucceededEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\UserApi;
use App\Service\Time\DateTimeProvider;
use DateTime;
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
            RegistrationSucceededEvent::NAME => [
                ['logEvent', 2],
                ['updateRegistrationProps', 1],
            ],
        ];
    }

    public function logEvent(RegistrationSucceededEvent $event)
    {
        $this->logger->notice(
            '',
            (new AuditEvents($this->dateTimeProvider))->selfRegistrationSucceeded($event->getRegisteredDeputy())
        );
    }

    public function updateRegistrationProps(RegistrationSucceededEvent $event)
    {
        $preUpdatedUser = clone $event->getRegisteredDeputy();
        $updatedUser = clone $event->getRegisteredDeputy();

        $updatedUser->setRegistrationDate(new DateTime());
        $updatedUser->setActive(true);

        $this->userApi->update($preUpdatedUser, $updatedUser, AuditEvents::TRIGGER_DEPUTY_USER_REGISTRATION_FLOW_COMPLETED);
    }
}
