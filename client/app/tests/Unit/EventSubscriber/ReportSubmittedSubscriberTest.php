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
            [
                ReportSubmittedEvent::NAME => [
                    ['logResubmittedReport', 2],
                    ['sendEmail', 1],
                ],
            ],
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

        $submittedReport = ReportHelpers::createReport()
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
