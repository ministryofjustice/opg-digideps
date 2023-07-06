<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\AdminManagerCreatedEvent;
use App\Event\AdminManagerDeletedEvent;
use App\Event\AdminUserCreatedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminUserLifeCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Mailer $mailer,
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminUserCreatedEvent::NAME => 'sendEmail',
            AdminManagerCreatedEvent::NAME => 'logAdminManagerCreatedEvent',
            AdminManagerDeletedEvent::NAME => 'logAdminManagerDeletedEvent',
        ];
    }

    public function sendEmail(AdminUserCreatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getCreatedUser());
    }

    public function logAdminManagerCreatedEvent(AdminManagerCreatedEvent $event)
    {
        $adminManagerCreatedEvent = (new AuditEvents($this->dateTimeProvider))
            ->adminManagerCreated(
                $event->getTrigger(),
                $event->getCurrentUser(),
                $event->getCreatedAdminManager()
            );
        $this->logger->notice('', $adminManagerCreatedEvent);
    }

    public function logAdminManagerDeletedEvent(AdminManagerDeletedEvent $event)
    {
        $adminManagerDeletedEvent = (new AuditEvents($this->dateTimeProvider))
            ->adminManagerDeleted(
                $event->getTrigger(),
                $event->getCurrentUser(),
                $event->getDeletedAdminManager()
            );
        $this->logger->notice('', $adminManagerDeletedEvent);
    }
}
