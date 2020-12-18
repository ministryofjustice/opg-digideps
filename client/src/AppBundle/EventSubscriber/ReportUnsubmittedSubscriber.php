<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\ReportUnsubmittedEvent;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Mailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Event\UserAddedToOrganisationEvent;
use AppBundle\Event\UserRemovedFromOrganisationEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;

class ReportUnsubmittedSubscriber implements EventSubscriberInterface
{
    /** @var ReportApi */
    private $reportApi;

    private LoggerInterface $logger;

    private DateTimeProvider $dateTimeProvider;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider, ReportApi $reportApi)
    {
        $this->reportApi = $reportApi;
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
    public function logUserAddedEvent(ReportUnsubmittedEvent $event)
    {
        $auditEvent = (new AuditEvents($this->dateTimeProvider))
            ->reportUnsubmitted(
                $event->getTrigger(),
                $event->getUnsubmittedReport(),
                $event->getUnsubmittedBy()
            );

        $this->logger->notice('', $auditEvent);
    }
}
