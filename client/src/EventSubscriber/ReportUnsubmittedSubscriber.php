<?php declare(strict_types=1);


namespace App\EventSubscriber;

use App\Event\ReportUnsubmittedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;

class ReportUnsubmittedSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    private DateTimeProvider $dateTimeProvider;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }


    public static function getSubscribedEvents()
    {
        return [
            ReportUnsubmittedEvent::NAME => 'logReportUnsubmittedEvent'
        ];
    }

    /**
     * @param ReportUnsubmittedEvent $event
     * @throws \Exception
     */
    public function logReportUnsubmittedEvent(ReportUnsubmittedEvent $event)
    {
        $auditEvent = (new AuditEvents($this->dateTimeProvider))
            ->reportUnsubmitted(
                $event->getUnsubmittedReport(),
                $event->getUnsubmittedBy(),
                $event->getTrigger(),
            );

        $this->logger->notice('', $auditEvent);
    }
}
