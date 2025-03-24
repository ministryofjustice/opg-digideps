<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UserRetentionPolicyCommandEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserRetentionPolicyCommandSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DateTimeProvider $dateTimeProvider
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
        $this->logger->notice('', (new AuditEvents($this->dateTimeProvider))->userAccountAutomatedDeletion(
            $event->getTrigger(),
            $event->getDeletedAdminUser()->getId(),
            $event->getDeletedAdminUser()->getEmail(),
        ));
    }
}
