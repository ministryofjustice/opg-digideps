<?php

declare(strict_types=1);

namespace Tests\App\Service\Client\Internal;

use App\Event\ReportSubmittedEvent;
use App\Event\ReportUnsubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ReportApiTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $restClient;
    private ObjectProphecy $eventDispatcher;
    private ReportApi $sut;

    public function setUp(): void
    {
        $this->restClient = self::prophesize(RestClient::class);
        $this->eventDispatcher = self::prophesize(ObservableEventDispatcher::class);

        $this->sut = new ReportApi(
            $this->restClient->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    /**
     * @dataProvider reportIdProvider
     * @test
     */
    public function submit(?string $reportId)
    {
        $reportToBeSubmitted = ReportHelpers::createReport();
        $submittedBy = UserHelpers::createUser();
        $event = new ReportSubmittedEvent($reportToBeSubmitted, $submittedBy, $reportId);

        $this->restClient
            ->put(sprintf('report/%s/submit', $reportToBeSubmitted->getId()), $reportToBeSubmitted, ['submit'])
            ->shouldBeCalled()
            ->willReturn($reportId);

        $this->eventDispatcher
            ->dispatch($event, 'report.submitted')
            ->shouldBeCalled();

        $this->sut->submit($reportToBeSubmitted, $submittedBy);
    }

    /** @test */
    public function unsubmit()
    {
        $trigger = 'A_TRIGGER';
        $currentUser = UserHelpers::createUser();
        $submittedReport = ReportHelpers::createSubmittedReport();

        $this->restClient
            ->put('report/'.$submittedReport->getId().'/unsubmit', $submittedReport, ['submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'startEndDates', 'report_due_date'])
            ->shouldBeCalled();

        $reportUnsubmittedEvent = new ReportUnsubmittedEvent(
            $submittedReport,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher
            ->dispatch($reportUnsubmittedEvent, 'report.unsubmitted')
            ->shouldBeCalled();

        $this->sut->unsubmit($submittedReport, $currentUser, $trigger);
    }

    public function reportIdProvider()
    {
        return [
            'Id returned' => ['1'],
            'Id not returned' => [null],
        ];
    }
}
