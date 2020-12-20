<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\ReportSubmittedEvent;
use AppBundle\EventSubscriber\ReportSubmittedSubscriber;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\ReportHelpers;
use AppBundle\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Prophecy\Argument;

class ReportSubmittedSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ReportSubmittedEvent::NAME => 'submitReportEvent'],
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
    public function sendEmail_email_not_sent_for_resubmissions()
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
    public function logReportSubmittedEvent()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $reportApi = self::prophesize(ReportApi::class);
        $mailer = self::prophesize(Mailer::class);
        $submittedReport = ReportHelpers::createReport();
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
            'type' => 'audit'
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logReportSubmittedEvent($reportResubmittedEvent);
    }
}
