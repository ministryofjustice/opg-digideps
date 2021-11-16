<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UserDeletedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDeletedSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private DateTimeProvider $dateTimeProvider)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            UserDeletedEvent::NAME => 'logEvent',
        ];
    }

    public function logEvent(UserDeletedEvent $event)
    {
        $event = (new AuditEvents($this->dateTimeProvider))->userDeleted(
            $event->getTrigger(),
            $event->getDeletedBy()->getEmail(),
            $event->getDeletedUser()->getFullName(),
            $event->getDeletedUser()->getEmail(),
            $event->getDeletedUser()->getRoleName(),
        );

        $this->logger->notice('', $event);
    }
}
