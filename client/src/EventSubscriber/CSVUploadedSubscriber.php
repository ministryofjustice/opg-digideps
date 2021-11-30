<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\CSVUploadedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CSVUploadedSubscriber implements EventSubscriberInterface
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DateTimeProvider $dateTimeProvider,
        LoggerInterface $logger
    ) {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CSVUploadedEvent::NAME => 'auditLog',
        ];
    }

    public function auditLog(CSVUploadedEvent $event)
    {
        $csvUploadedEvent = (new AuditEvents($this->dateTimeProvider))
            ->csvUploaded(
                $event->getTrigger(),
                $event->getSource(),
                $event->getRoleType()
            );

        $this->logger->notice('', $csvUploadedEvent);
    }
}
