<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\Client\Sirius\SiriusDocumentFile;
use AppBundle\Service\Client\Sirius\SiriusDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\DocumentSyncService;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use GuzzleHttp\Psr7\Response;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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

        $exampleBody = '--boundary\r\nContent-Disposition: form-data; name="report"\r\nContent-Length: 185\r\n\r\n{"data":{"type":"reports","attributes":{"reporting_period_from":"2018-05-14","reporting_period_to":"2019-05-13","year":"2018","date_submitted":"2019-06-20T00:00:00+00:00","type":"PF"}}}\r\n--boundary\r\nContent-Disposition: form-data; name="report_file"\r\nContent-Length: 13\r\n\r\nuploaded_file_contents\r\n--boundary--\r\n';

        $requestRegexObj = [
            'data' => [
                'type' => 'reports',
                'attributes' => [
                    'reporting_period_from' => '\d{4}-\d{2}-\d{2}',
                    'reporting_period_to' => '\d{4}-\d{2}-\d{2}',
                    'year' => '\d{4}',
                    'date_submitted' => '\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}',
                    'type' => '(PF|HW|NDR)',
                ]
            ]
        ];

        $requestRegex = str_replace(['{"','"}', '\\\\'], ['\{"', '"\}', '\\'], json_encode($requestRegexObj));

        // Create your expected request from the consumer.
        $request = new ConsumerRequest();
        $request
            ->setMethod('POST')
            ->setPath('/clients/27493727/reports')
            ->setHeaders([
                'Content-Type' => $matcher->regex('multipart/form-data; boundary=5872fc54a8fa5f5be65ee0af590d1ae813a1b091', 'multipart\/form-data; boundary=[0-9a-f]{32}')
            ])
            ->setBody($matcher->regex($exampleBody, $requestRegex));

        // Create your expected response from the provider.
        $response = new ProviderResponse();
        $response
            ->setStatus(201)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'data' => [
                    'type' => 'reports',
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

    /** @test */
    public function sendReportDocument()
    {
        /** @var S3Storage&ObjectProphecy $s3Storage */
        $s3Storage = self::prophesize(S3Storage::class);

        /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
        $siriusApiGatewayClient = self::prophesize(SiriusApiGatewayClient::class);

        /** @var RestClient|ObjectProphecy $restClient */
        $restClient = self::prophesize(RestClient::class);

        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');

        $client = new Client();
        $client->setCaseNumber('1234567T');

        $report = new Report();
        $report->setType(Report::TYPE_102);
        $report->setClient($client);
        $report->setStartDate($reportStartDate);
        $report->setEndDate($reportEndDate);
        $report->setSubmitDate($reportSubmittedDate);
        $report->setId('9876');

        $document = new Document();
        $document->setReport($report);
        $document->setStorageReference('test');
        $document->setFileName('Report_1234567T_2018_2019_11111.pdf');

        $s3Storage->retrieve('test')->willReturn('fake_contents');

        $siriusDocumentMetadata = (new SiriusDocumentMetadata())
            ->setReportingPeriodFrom($reportStartDate)
            ->setReportingPeriodTo($reportEndDate)
            ->setYear('2019')
            ->setDateSubmitted($reportSubmittedDate)
            ->setOrderType('PF');

        $siriusDocumentFile = (new SiriusDocumentFile())
            ->setFileName('Report_1234567T_2018_2019_11111.pdf')
            ->setMimeType('application/pdf')
            ->setSource('JVBERi0xLjMKJcT...etc==');

        $siriusDocumentUpload = (new SiriusDocumentUpload())
            ->setCaseRef('1234567T')
            ->setDocumentType('Report')
            ->setDocumentSubType('Report')
            ->setDirection('DIRECTION_INCOMING')
            ->setMetadata($siriusDocumentMetadata)
            ->setFile($siriusDocumentFile);

        $uuid = '5a8b1a26-8296-4373-ae61-f8d0b250e773';
        $successResponseBody = json_encode(['data' => ['uuid' => $uuid]]);
        $successResponse = new Response('200', [], $successResponseBody);

        $siriusApiGatewayClient->sendDocument($siriusDocumentUpload)->shouldBeCalled()->willReturn($successResponse);
        $restClient->put('report-submission/9876', json_encode(['uuid' => $uuid]))
          ->shouldBeCalled()
          ->willReturn(new SymfonyResponse('9876'));

        $sut = new DocumentSyncService($s3Storage->reveal(), $siriusApiGatewayClient->reveal(), $restClient->reveal());
    }
}
