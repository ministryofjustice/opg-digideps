<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PHPUnit\Framework\TestCase;

class SiriusDocumentsContractTest extends TestCase
{
    /**
     * Example PACT test.
     *
     * @throws \Exception
     */
    public function testSendReportDocument()
    {
        $matcher = new Matcher();

        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');

        // Create your expected request from the consumer.
        $request = new ConsumerRequest();
        $request
            ->setMethod('POST')
            ->setPath('/clients/27493727/reports');

        // Create your expected response from the provider.
        $response = new ProviderResponse();
        $response
            ->setStatus(201)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'data' => [
                    'id' => $matcher->uuid('33ea0382-cfc9-4776-9036-667eeb68fa4b'),
                    'attributes' => [
                        'reporting_period_from' => $matcher->dateISO8601(),
                        'reporting_period_to' => $matcher->dateISO8601(),
                        'year' => $matcher->regex('2019', '[0-9]{4}'),
                        'date_submitted' => $matcher->dateTimeWithMillisISO8601(),
                        'type' => $matcher->regex('PF', 'PF|HW|NDR')
                    ]
                ],
            ]);

        // Create a configuration that reflects the server that was started. You can create a custom MockServerConfigInterface if needed.
        $config  = new MockServerEnvConfig();
        $builder = new InteractionBuilder($config);
        $builder
            ->uponReceiving('A submitted report')
            ->with($request)
            ->willRespondWith($response); // This has to be last. This is what makes an API request to the Mock Server to set the interaction.

        // ------------------

        $s3Mock = self::prophesize(S3Storage::class);
        $s3Mock->retrieve('test')->willReturn('fake_contents');

        $sut = new DocumentSyncService($s3Mock->reveal());

        $client = new Client();
        $client->setCaseNumber(27493727);

        $report = new Report();
        $report->setType(Report::TYPE_102);
        $report->setClient($client);
        $report->setStartDate($reportStartDate);
        $report->setEndDate($reportEndDate);
        $report->setSubmitDate($reportSubmittedDate);

        $document = new Document();
        $document->setReport($report);
        $document->setStorageReference('test');

        $result = $sut->syncReportDocument($document);

        $builder->verify();

        self::assertEquals('33ea0382-cfc9-4776-9036-667eeb68fa4b', $result);
    }
}
