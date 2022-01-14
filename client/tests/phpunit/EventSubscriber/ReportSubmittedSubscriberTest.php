<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\ReportSubmittedEvent;
use App\EventSubscriber\ReportSubmittedSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ReportApi;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class ReportSubmittedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ReportSubmittedEvent::NAME => 'log', ReportSubmittedEvent::NAME => 'sendEmail'],
            ReportSubmittedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @test
     */
    public function sendEmail()
    {
        $reportApi = self::prophesize(ReportApi::class);
        $mailer = self::prophesize(Mailer::class);
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $submittedBy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createReport();
        $nextYearReport = ReportHelpers::createReport();
        $nextYearReportId = '5';

        $reportApi
            ->getReport(5, ['submit'])
            ->shouldBeCalled()
            ->willReturn($nextYearReport);

        $mailer
            ->sendReportSubmissionConfirmationEmail($submittedBy, $submittedReport, $nextYearReport)
            ->shouldBeCalled();

        $sut = new ReportSubmittedSubscriber($reportApi->reveal(), $mailer->reveal(), $logger->reveal(), $dateTimeProvider->reveal());
        $event = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReportId);

        $sut->sendEmail($event);
    }

    /**
     * @test
     */
    public function sendEmailEmailNotSentForResubmissions()
    {
        $reportApi = self::prophesize(ReportApi::class);
        $mailer = self::prophesize(Mailer::class);
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $submittedBy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createReport();
        $nextYearReportId = null;

        $reportApi
            ->getReport(Argument::cetera())
            ->shouldNotBeCalled();

        $mailer
            ->sendReportSubmissionConfirmationEmail(Argument::cetera())
            ->shouldNotBeCalled();

        $sut = new ReportSubmittedSubscriber($reportApi->reveal(), $mailer->reveal(), $logger->reveal(), $dateTimeProvider->reveal());
        $event = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReportId);

        $sut->sendEmail($event);
    }

    /**
     * @test
     */
    public function log()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $reportApi = self::prophesize(ReportApi::class);
        $mailer = self::prophesize(Mailer::class);

        $submittedReport = (ReportHelpers::createReport())
            ->setUnSubmitDate(new DateTime());

        $nextYearReport = ReportHelpers::createReport();

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $submittedBy = UserHelpers::createUser();
        $trigger = 'RESUBMIT_REPORT';

        $sut = new ReportSubmittedSubscriber($reportApi->reveal(), $mailer->reveal(), $logger->reveal(), $dateTimeProvider->reveal());

        $reportResubmittedEvent = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReport);

        $expectedEvent = [
            'trigger' => $trigger,
            'deputy_user' => $submittedBy->getId(),
            'report_id' => $submittedReport->getId(),
            'date_resubmitted' => $submittedReport->getSubmitDate(),
            'event' => AuditEvents::EVENT_REPORT_RESUBMITTED,
            'type' => 'audit',
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logResubmittedReport($reportResubmittedEvent);
    }
}
