<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\CSVUploadedEvent;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
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
        $csvUploadedEvent = new AuditEvents($this->dateTimeProvider)
            ->csvUploaded(
                $event->getTrigger(),
                $event->getRoleType()
            );

        $this->logger->notice('', $csvUploadedEvent);
    }
}
