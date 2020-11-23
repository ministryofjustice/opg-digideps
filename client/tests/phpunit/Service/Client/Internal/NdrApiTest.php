<?php declare(strict_types=1);


namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Event\NdrSubmittedEvent;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\Internal\NdrApi;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\ClientHelpers;
use AppBundle\TestHelpers\DocumentHelpers;
use AppBundle\TestHelpers\NdrHelpers;
use AppBundle\TestHelpers\ReportHelpers;
use AppBundle\TestHelpers\UserHelpers;
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
            ->dispatch('ndr.submitted', $event)
            ->shouldBeCalled();

        $sut->submit($ndr, $document);
    }
}
