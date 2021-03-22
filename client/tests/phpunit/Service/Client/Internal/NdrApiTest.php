<?php declare(strict_types=1);


namespace DigidepsTests\Service\Client\Internal;

use App\Event\NdrSubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\Internal\NdrApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\DocumentHelpers;
use App\TestHelpers\NdrHelpers;
use App\TestHelpers\ReportHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class NdrApiTest extends TestCase
{
    /** @test */
    public function submit()
    {
        $restClient = self::prophesize(RestClient::class);
        $eventDispatcher = self::prophesize(ObservableEventDispatcher::class);
        $userApi = self::prophesize(UserApi::class);

        $ndr = NdrHelpers::createNdr();
        $document = DocumentHelpers::generateReportPdfDocument();
        $activeReport = ReportHelpers::createReport();
        $client = (ClientHelpers::createClient($activeReport));
        $submittedByWithClientsAndReports = (UserHelpers::createUser())->setClients([$client]);

        $sut = new NdrApi($restClient->reveal(), $eventDispatcher->reveal(), $userApi->reveal());

        $restClient
            ->put(sprintf('ndr/%s/submit?documentId=%s', $ndr->getId(), $document->getId()), $ndr, ['submit'])
            ->shouldBeCalled();

        $userApi
            ->getUserWithData(['user-clients', 'client', 'client-reports', 'report'])
            ->shouldBeCalled()
            ->willReturn($submittedByWithClientsAndReports);

        $event = new NdrSubmittedEvent($submittedByWithClientsAndReports, $ndr, $activeReport);
        $eventDispatcher
            ->dispatch($event, 'ndr.submitted')
            ->shouldBeCalled();

        $sut->submit($ndr, $document);
    }
}
