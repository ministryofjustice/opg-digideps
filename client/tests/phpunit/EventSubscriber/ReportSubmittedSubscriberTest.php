<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\ReportSubmittedEvent;
use AppBundle\EventSubscriber\ReportSubmittedSubscriber;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Mailer\Mailer;
use AppBundle\TestHelpers\ReportHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ReportSubmittedSubscriberTest extends TestCase
{
    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ReportSubmittedEvent::NAME => 'sendEmail'],
            ReportSubmittedSubscriber::getSubscribedEvents()
        );
    }

    /** @test */
    public function sendEmail()
    {
        $reportApi = self::prophesize(ReportApi::class);
        $mailer = self::prophesize(Mailer::class);
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

        $sut = new ReportSubmittedSubscriber($reportApi->reveal(), $mailer->reveal());
        $event = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReportId);

        $sut->sendEmail($event);
    }

    /** @test */
    public function sendEmail_email_not_sent_for_resubmissions()
    {
        $reportApi = self::prophesize(ReportApi::class);
        $mailer = self::prophesize(Mailer::class);
        $submittedBy = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createReport();
        $nextYearReportId = null;

        $reportApi
            ->getReport(Argument::cetera())
            ->shouldNotBeCalled();

        $mailer
            ->sendReportSubmissionConfirmationEmail(Argument::cetera())
            ->shouldNotBeCalled();

        $sut = new ReportSubmittedSubscriber($reportApi->reveal(), $mailer->reveal());
        $event = new ReportSubmittedEvent($submittedReport, $submittedBy, $nextYearReportId);

        $sut->sendEmail($event);
    }
}
