<?php declare(strict_types=1);

namespace Tests\App\Service\Client\Internal;

use App\Event\ReportSubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class ReportApiTest extends TestCase
{
    /**
     * @dataProvider reportIdProvider
     * @test
     */
    public function submit(?string $reportId)
    {
        $restClient = self::prophesize(RestClient::class);
        $eventDispatcher = self::prophesize(ObservableEventDispatcher::class);

        $reportToBeSubmitted = ReportHelpers::createReport();
        $submittedBy = UserHelpers::createUser();
        $event = new ReportSubmittedEvent($reportToBeSubmitted, $submittedBy, $reportId);

        $restClient
            ->put(sprintf('report/%s/submit', $reportToBeSubmitted->getId()), $reportToBeSubmitted, ['submit'])
            ->shouldBeCalled()
            ->willReturn($reportId);

        $eventDispatcher
            ->dispatch('report.submitted', $event)
            ->shouldBeCalled();

        $sut = new ReportApi($restClient->reveal(), $eventDispatcher->reveal());
        $sut->submit($reportToBeSubmitted, $submittedBy);
    }

    public function reportIdProvider()
    {
        return [
            'Id returned' => ['1'],
            'Id not returned' => [null]
        ];
    }
}
