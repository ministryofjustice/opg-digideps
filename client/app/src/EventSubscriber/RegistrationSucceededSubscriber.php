<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\RegistrationSucceededEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\UserApi;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationSucceededSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserApi $userApi,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RegistrationSucceededEvent::DEPUTY => [
                ['updateRegistrationProps', 1],
            ],
        ];
    }

    public function updateRegistrationProps(RegistrationSucceededEvent $event): void
    {
        $preUpdatedUser = clone $event->getRegisteredUser();
        $updatedUser = clone $event->getRegisteredUser();

        $updatedUser->setRegistrationDate(new \DateTime());
        $updatedUser->setActive(true);

        $this->userApi->update($preUpdatedUser, $updatedUser, AuditEvents::TRIGGER_DEPUTY_USER_REGISTRATION_FLOW_COMPLETED);
    }
}
