<?php declare(strict_types=1);


namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Event\NdrSubmittedEvent;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\Internal\NdrApi;
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

        $ndr = NdrHelpers::createNdr();
        $document = DocumentHelpers::generateReportPdfDocument();
        $submittedBy = UserHelpers::createUser();
        $activeReport = ReportHelpers::createReport();
        $client = (ClientHelpers::createClient($activeReport));

        $sut = new NdrApi($restClient->reveal(), $eventDispatcher->reveal());

        $restClient
            ->put(sprintf('ndr/%s/submit?documentId=%s', $ndr->getId(), $document->getId()), $ndr, ['submit'])
            ->shouldBeCalled();

        $event = new NdrSubmittedEvent($submittedBy, $ndr, $activeReport);
        $eventDispatcher
            ->dispatch('ndr.submitted', $event)
            ->shouldBeCalled();

        $sut->submit($ndr, $document, $submittedBy, $client);
    }
}
