<?php declare(strict_types=1);


namespace Tests\AppBundle\EventListener;

use AppBundle\Event\ReportUnsubmittedEvent;
use AppBundle\EventSubscriber\ReportUnsubmittedSubscriber;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\ReportHelpers;
use AppBundle\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReportUnsubmittedSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [
                ReportUnsubmittedEvent::NAME => 'logReportUnsubmittedEvent',
            ],
            ReportUnsubmittedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @test
     */
    public function logReportUnsubmittedEvent()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $currentUser = UserHelpers::createUser();
        $trigger = 'UNSUBMIT_REPORT';


        $submittedReport = ReportHelpers::createSubmittedReport();

        $sut = new ReportUnsubmittedSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $reportUnsubmittedEvent = new ReportUnsubmittedEvent($submittedReport, $currentUser, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'deputy_user' => $currentUser->getId(),
            'report_id' => $submittedReport->getId(),
            'date_unsubmitted' => $submittedReport->getUnSubmitDate(),
            'event' => AuditEvents::EVENT_REPORT_UNSUBMITTED,
            'type' => 'audit'
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logReportUnsubmittedEvent($reportUnsubmittedEvent);
    }
}
