<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Entity\Report\Report;
use App\Event\ChecklistsSynchronisedEvent;
use App\EventSubscriber\ChecklistsSynchronisedSubscriber;
use App\Service\ChecklistSyncService;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class ChecklistsSynchronisedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [ChecklistsSynchronisedEvent::NAME => 'synchroniseChecklists'],
            ChecklistsSynchronisedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider checklistProvider
     *
     * @test
     */
    public function synchroniseChecklists(array $reports, int $failCount)
    {
        $verboseLogger = self::prophesize(LoggerInterface::class);
        $checklistSyncService = self::prophesize(ChecklistSyncService::class);

        $checklistSyncService->syncChecklistsByReports($reports)->willReturn($failCount);

        if ($failCount > 0) {
            $verboseLogger->notice(sprintf('%d checklists failed to sync', $failCount))->shouldBeCalled();
        } else {
            $verboseLogger->notice(sprintf('%d checklists failed to sync', $failCount))->shouldNotBeCalled();
        }

        $sut = new ChecklistsSynchronisedSubscriber($verboseLogger->reveal(), $checklistSyncService->reveal());
        $event = new ChecklistsSynchronisedEvent($reports);

        $sut->synchroniseChecklists($event);
    }

    public function checklistProvider()
    {
        $queuedReportData = (new Report())
            ->setStartDate(new DateTime('2018-05-14'))
            ->setEndDate(new DateTime('2019-05-13'));
        $arrayOfReports = [
            $queuedReportData,
        ];

        return [
            'All Sync' => [$arrayOfReports, 0],
            'Sync Errors' => [$arrayOfReports, 1],
        ];
    }
}
