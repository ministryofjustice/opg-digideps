<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\ReportSubmittedEvent;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubmittedSubscriber implements EventSubscriberInterface
{
    /** @var ReportApi */
    private $reportApi;

    /* @var Mailer */
    private $mailer;

    private LoggerInterface $logger;

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
            ReportSubmittedEvent::NAME => [
                ['logResubmittedReport', 2],
                ['sendEmail', 1],
            ],
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
        if (null !== $event->getSubmittedReport()->getUnSubmitDate()) {
            $auditEvent = (new AuditEvents($this->dateTimeProvider))
                ->reportResubmitted(
                    $event->getSubmittedReport(),
                    $event->getSubmittedBy()
                );

            $this->logger->notice('', $auditEvent);
        }
    }
}
