<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UserRetentionPolicyCommandEvent;
use App\Service\Audit\AuditEvents;
use App\Service\DateTimeProvider;
use Psr\Log\LoggerInterface;

class UserRetentionPolicyCommandSubscriber
{
    public function __construct(
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            UserRetentionPolicyCommandEvent::NAME => 'logEvent',
        ];
    }

    public function logEvent(UserRetentionPolicyCommandEvent $event)
    {
        $this->logger->notice('', (new AuditEvents($this->dateTimeProvider))->userDeletedAutomation(
            $event->getTrigger(),
            $event->getDeletedAdminUser()->getId(),
            $event->getDeletedAdminUser()->getEmail(),
        ));
    }
}
