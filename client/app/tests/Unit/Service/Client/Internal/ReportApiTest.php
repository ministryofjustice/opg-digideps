<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client\Internal;

use OPG\Digideps\Frontend\Event\ReportSubmittedEvent;
use OPG\Digideps\Frontend\Event\ReportUnsubmittedEvent;
use OPG\Digideps\Frontend\EventDispatcher\ObservableEventDispatcher;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\TestHelpers\ReportHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportApiTest extends TestCase
{
    private RestClient&MockObject $restClient;
    private ObservableEventDispatcher&MockObject $eventDispatcher;
    private ReportApi $sut;

    public function setUp(): void
    {
        $this->restClient = self::createMock(RestClient::class);
        $this->eventDispatcher = self::createMock(ObservableEventDispatcher::class);

        $this->sut = new ReportApi(
            $this->restClient,
            $this->eventDispatcher
        );
    }

    /**
     * @dataProvider reportIdProvider
     */
    public function testSubmit(?string $reportId): void
    {
        $reportToBeSubmitted = ReportHelpers::createReport();
        $submittedBy = UserHelpers::createUser();
        $event = new ReportSubmittedEvent($reportToBeSubmitted, $submittedBy, $reportId);

        $this->restClient->expects(self::once())
            ->method('put')
            ->with(
                'report/' . $reportToBeSubmitted->getId() . '/submit',
                $reportToBeSubmitted,
                ['submit']
            )
            ->willReturn($reportId);

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with($event, 'report.submitted');

        $this->sut->submit($reportToBeSubmitted, $submittedBy);
    }

    public function testUnsubmit(): void
    {
        $trigger = 'A_TRIGGER';
        $currentUser = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createSubmittedReport();

        $this->restClient->expects(self::once())
            ->method('put')
            ->with(
                'report/' . $submittedReport->getId() . '/unsubmit',
                $submittedReport,
                ['submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'startEndDates', 'report_due_date']
            );

        $reportUnsubmittedEvent = new ReportUnsubmittedEvent(
            $submittedReport,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with($reportUnsubmittedEvent, 'report.unsubmitted');

        $this->sut->unsubmit($submittedReport, $currentUser, $trigger);
    }

    public static function reportIdProvider(): array
    {
        return [
            'Id returned' => ['1'],
            'Id not returned' => [null],
        ];
    }
}
