<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\RegistrationSucceededEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationSucceededAuditingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private DateTimeProvider $dateTimeProvider,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RegistrationSucceededEvent::DEPUTY => [
                ['logDeputyEvent', 2],
            ],  RegistrationSucceededEvent::ADMIN => [
                ['logAdminEvent', 1],
            ],
        ];
    }

    public function logDeputyEvent(RegistrationSucceededEvent $event): void
    {
        try {
            $this->logger->notice(
                '',
                (new AuditEvents($this->dateTimeProvider))->selfRegistrationSucceeded($event->getRegisteredUser())
            );
        } catch (\Exception $e) {
            error_log('Failed to create audit log for successful registration: '.$e->getMessage());
        }
    }

    public function logAdminEvent(RegistrationSucceededEvent $event): void
    {
        $this->logger->notice(
            '',
            (new AuditEvents($this->dateTimeProvider))->adminRegistrationSucceeded($event->getRegisteredUser())
        );
    }
}
