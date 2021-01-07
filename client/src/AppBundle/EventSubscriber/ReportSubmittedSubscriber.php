<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Event\ReportSubmittedEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubmittedSubscriber implements EventSubscriberInterface
{
    /** @var ReportApi */
    private $reportApi;

    /* @var Mailer */
    private $mailer;

    private LoggerInterface $logger;

    /**
     * @var DateTimeProvider
     */
    private DateTimeProvider $dateTimeProvider;

    public function __construct(ReportApi $reportApi, Mailer $mailer, LoggerInterface $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->reportApi = $reportApi;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public static function getSubscribedEvents()
    {
        return [
            ReportSubmittedEvent::NAME => 'logResubmittedReport',
            ReportSubmittedEvent::NAME => 'sendEmail'
        ];
    }

    public function sendEmail(ReportSubmittedEvent $event)
    {
        if ($event->getNewYearReportId()) {
            $newReport = $this->reportApi->getReport(intval($event->getNewYearReportId()), ['submit']);
            $this->mailer->sendReportSubmissionConfirmationEmail($event->getSubmittedBy(), $event->getSubmittedReport(), $newReport);
        }
    }

    public function logResubmittedReport(ReportSubmittedEvent $event)
    {
        if ($event->getSubmittedReport()->getUnSubmitDate() !== null) {
            $auditEvent = (new AuditEvents($this->dateTimeProvider))
                ->reportResubmitted(
                    $event->getSubmittedReport(),
                    $event->getSubmittedBy()
                );

            $this->logger->notice('', $auditEvent);
        }
    }
}
