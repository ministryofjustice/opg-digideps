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
    public function __construct(private ReportApi $reportApi, private Mailer $mailer, private LoggerInterface $logger, private DateTimeProvider $dateTimeProvider)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ReportSubmittedEvent::NAME => 'sendEmail',
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
