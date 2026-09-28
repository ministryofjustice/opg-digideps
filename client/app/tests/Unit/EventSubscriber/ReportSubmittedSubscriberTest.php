<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\ReportSubmittedEvent;
use OPG\Digideps\Frontend\EventSubscriber\ReportSubmittedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReportSubmittedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                ReportSubmittedEvent::NAME => [
                    ['logResubmittedReport', 2],
                    ['sendEmail', 1],
                ],
            ],
            ReportSubmittedSubscriber::getSubscribedEvents()
        );
    }

    public function testSendEmail(): void
    {
        $reportApi = self::createMock(ReportApi::class);
        $mailer = self::createMock(Mailer::class);
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);
        $submittedBy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createReport();
        $nextYearReport = ReportHelpers::createReport();
        $nextYearReportId = '5';

        $reportApi->expects(self::once())->method('getReport')->with(5, ['submit'])->willReturn($nextYearReport);

        $mailer->expects(self::once())
            ->method('sendReportSubmissionConfirmationEmail')
            ->with($submittedBy, $submittedReport, $nextYearReport);

        $event = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReportId);

        new ReportSubmittedSubscriber($reportApi, $mailer, $logger, $dateTimeProvider)->sendEmail($event);
    }

    public function testSendEmailEmailNotSentForResubmissions(): void
    {
        $reportApi = self::createMock(ReportApi::class);
        $mailer = self::createMock(Mailer::class);
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);
        $submittedBy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createReport();
        $nextYearReportId = null;

        $reportApi->expects(self::never())->method('getReport');

        $mailer->expects(self::never())->method('sendReportSubmissionConfirmationEmail');

        $event = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReportId);

        new ReportSubmittedSubscriber($reportApi, $mailer, $logger, $dateTimeProvider)->sendEmail($event);
    }

    public function testLog(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);
        $reportApi = self::createMock(ReportApi::class);
        $mailer = self::createMock(Mailer::class);

        $submittedReport = ReportHelpers::createReport()->setUnSubmitDate(new \DateTime());

        $nextYearReport = ReportHelpers::createReport();

        $submittedBy = UserHelpers::createUser();
        $trigger = 'RESUBMIT_REPORT';

        $reportResubmittedEvent = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReport);

        $expectedEvent = [
            'trigger' => $trigger,
            'deputy_user' => $submittedBy->getId(),
            'report_id' => $submittedReport->getId(),
            'date_resubmitted' => $submittedReport->getSubmitDate(),
            'event' => AuditEvents::EVENT_REPORT_RESUBMITTED,
            'type' => 'audit',
        ];

        $logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        new ReportSubmittedSubscriber($reportApi, $mailer, $logger, $dateTimeProvider)
            ->logResubmittedReport($reportResubmittedEvent);
    }
}
