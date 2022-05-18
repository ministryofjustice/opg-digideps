<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\AdminUserCreatedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminUserCreatedSubscriber implements EventSubscriberInterface
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
            AdminUserCreatedEvent::NAME => 'auditLog',
        ];
    }

    public function sendEmail(AdminUserCreatedEvent $event)
    {
        $this->mailer->sendActivationEmail($event->getCreatedUser());
    }

    public function auditLog(AdminUserCreatedEvent $event)
    {
        $adminUserCreatedEvent = (new AuditEvents($this->dateTimeProvider))
            ->adminUserCreated(
                $event->getTrigger(),
                $event->getCurrentUser(),
                $event->getCreatedUser(),
                $event->getRoleType()
            );
        $this->logger->notice('', $adminUserCreatedEvent);
    }
}
