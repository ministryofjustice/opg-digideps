<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ReportSubmittedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ReportApi;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
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
