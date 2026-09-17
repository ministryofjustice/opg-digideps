<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\ReportUnsubmittedEvent;
use OPG\Digideps\Frontend\EventSubscriber\ReportUnsubmittedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReportUnsubmittedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                ReportUnsubmittedEvent::NAME => 'logReportUnsubmittedEvent',
            ],
            ReportUnsubmittedSubscriber::getSubscribedEvents()
        );
    }

    public function logReportUnsubmittedEvent(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);

        $now = new \DateTime();
        $dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);
        $currentUser = UserHelpers::createUser();
        $trigger = 'UNSUBMIT_REPORT';

        $submittedReport = ReportHelpers::createSubmittedReport();

        $reportUnsubmittedEvent = new ReportUnsubmittedEvent($submittedReport, $currentUser, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'deputy_user' => $currentUser->getId(),
            'report_id' => $submittedReport->getId(),
            'date_unsubmitted' => $submittedReport->getUnSubmitDate(),
            'event' => AuditEvents::EVENT_REPORT_UNSUBMITTED,
            'type' => 'audit',
        ];

        $logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        new ReportUnsubmittedSubscriber($logger, $dateTimeProvider)->logReportUnsubmittedEvent($reportUnsubmittedEvent);
    }
}
