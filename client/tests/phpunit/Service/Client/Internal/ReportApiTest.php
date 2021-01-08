<?php declare(strict_types=1);

namespace Tests\AppBundle\Service\Client\Internal;

use AppBundle\Event\ReportSubmittedEvent;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\ReportHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use AppBundle\Event\ReportUnsubmittedEvent;

class ReportApiTest extends TestCase
{
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
            ->dispatch('report.submitted', $event)
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
            ->put('report/' . $submittedReport->getId() . '/unsubmit', $submittedReport, ['submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'startEndDates', 'report_due_date'])
            ->shouldBeCalled();

        $reportUnsubmittedEvent = new ReportUnsubmittedEvent(
            $submittedReport,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher
            ->dispatch('report.unsubmitted', $reportUnsubmittedEvent)
            ->shouldBeCalled();

        $this->sut->unsubmit($submittedReport, $currentUser, $trigger);
    }

    public function reportIdProvider()
    {
        return [
            'Id returned' => ['1'],
            'Id not returned' => [null]
        ];
    }
}
