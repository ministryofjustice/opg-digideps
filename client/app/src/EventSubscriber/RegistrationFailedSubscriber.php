<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\RegistrationFailedEvent;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
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
