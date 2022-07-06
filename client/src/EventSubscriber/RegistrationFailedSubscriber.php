<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\RegistrationFailedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private DateTimeProvider $dateTimeProvider)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            RegistrationFailedEvent::NAME => 'logAuditEvent',
        ];
    }

    public function logAuditEvent(RegistrationFailedEvent $event)
    {
        $registrationFailedEvent = (new AuditEvents($this->dateTimeProvider))
            ->selfRegistrationFailed(
                $event->getFailureData(),
                $event->getErrorMessage()
            );

        $this->logger->notice('', $registrationFailedEvent);
    }
}
