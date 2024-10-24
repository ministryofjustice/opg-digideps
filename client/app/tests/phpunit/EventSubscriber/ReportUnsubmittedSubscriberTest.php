<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\ReportUnsubmittedEvent;
use App\EventSubscriber\ReportUnsubmittedSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class ReportUnsubmittedSubscriberTest extends TestCase
{
    use ProphecyTrait;

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
            'type' => 'audit',
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->logReportUnsubmittedEvent($reportUnsubmittedEvent);
    }
}
