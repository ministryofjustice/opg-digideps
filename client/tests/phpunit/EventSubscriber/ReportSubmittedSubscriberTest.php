<?php declare(strict_types=1);


namespace Tests\App\EventListener;

use App\Event\ReportSubmittedEvent;
use App\EventSubscriber\ReportSubmittedSubscriber;
use App\Service\Client\Internal\ReportApi;
use App\Service\Mailer\Mailer;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
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
