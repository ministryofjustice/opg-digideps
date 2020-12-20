<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\ReportUnsubmittedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
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
