<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Service\AWS\RequestSigner;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use DateTime;
use DigidepsTests\Helpers\DocumentHelpers;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusDocumentsContractTest extends KernelTestCase
{
    /** @var string */
    private $caseRef;

    /** @var RequestSigner&ObjectProphecy */
    private $signer;

    /**  @var SiriusApiGatewayClient */
    private $sut;

    /** @var string */
    private $reportPdfUuid;

    /** @var string */
    private $expectedSupportingDocumentUuid;

    /** @var InteractionBuilder */
    private $builder;

    public function setUp(): void
    {
        $client = new GuzzleClient();
        $baseUrl = getenv('PACT_MOCK_SERVER_HOST');
        $serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('serializer');

        // Create a configuration that reflects the server that was started. You can create a custom MockServerConfigInterface if needed.
        $config  = new MockServerEnvConfig();
        $this->builder = new InteractionBuilder($config);

        $this->caseRef = '1234567T';
        $this->reportPdfUuid = '33ea0382-cfc9-4776-9036-667eeb68fa4b';
        $this->expectedSupportingDocumentUuid = '9c0cb55e-718d-4ffb-9599-f3164e12dbdb';
        $this->signer = self::prophesize(RequestSigner::class);

        $this->sut = new SiriusApiGatewayClient(
            $client,
            $this->signer->reveal(),
            'http://' . $baseUrl,
            $serializer
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendReportPdfDocument()
    {
        $this->setUpReportPdfPactBuilder($this->caseRef);

        $this->signer->signRequest(Argument::type(Request::class), 'execute-api')->willReturnArgument(0);

        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $reportSubmissionId = 9876;

        $upload = $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
            $reportSubmissionId
        );

        $result = $this->sut->sendReportPdfDocument($upload, 'some_content', $this->caseRef);

        $this->builder->verify();

        self::assertStringContainsString(
            $this->reportPdfUuid,
            $result->getBody()->getContents()
        );
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendSupportingDocument()
    {
        $this->setUpSupportingDocumentPactBuilder($this->caseRef, $this->reportPdfUuid);

        $this->signer->signRequest(Argument::type(Request::class), 'execute-api')->willReturnArgument(0);

        $upload = $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(9876);

        $result = $this->sut->sendSupportingDocument($upload, 'some_content', $this->reportPdfUuid, $this->caseRef);

        $this->builder->verify();

        self::assertStringContainsString(
            $this->reportPdfUuid,
            $result->getBody()->getContents()
        );
    }

    private function setUpReportPdfPactBuilder(string $caseRef)
    {
        $matcher = new Matcher();
        // Create your expected request from the consumer.
        $request = (new ConsumerRequest())
            ->setMethod('POST')
            ->setPath(sprintf('/v1/clients/%s/reports', $caseRef))
            ->addHeader('Content-Type', 'application/json')
            ->setBody( [
                'report' => [
                    'data'=> [
                        'type' => 'reports',
                        'attributes' => [
                            'reporting_period_from' => $matcher->dateISO8601('2018-05-14'),
                            'reporting_period_to' => $matcher->dateISO8601('2019-05-13'),
                            'year' => $matcher->regex('2018', '[0-9]{4}'),
                            'date_submitted' => $matcher->dateTimeISO8601('2019-06-20T00:00:00+01:00'),
                            'type' => $matcher->regex('PF', 'PF|HW|NDR'),
                            'submission_id' => $matcher->integer(9876)
                        ],
                        'file' => [
                            'name' => 'Report_1234567T_2018_2019_11111.pdf',
                            'mimetype' => 'application/pdf',
                            'source' => $matcher->regex('dGVzdA==', '.+')
                        ]
                    ]
                ]
            ]);

        // Create your expected response from the provider.
        $response = new ProviderResponse();
        $response
            ->setStatus(201)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'uuid' => $matcher->uuid($this->reportPdfUuid)]);

        $this->builder
            ->uponReceiving('A submitted report')
            ->with($request)
            ->willRespondWith($response); // This has to be last. This is what makes an API request to the Mock Server to set the interaction.
    }

    private function setUpSupportingDocumentPactBuilder(string $caseRef, string $reportPdfDocumentUuid)
    {
        $matcher = new Matcher();

        // Create your expected request from the consumer.
        $request = (new ConsumerRequest())
            ->setMethod('POST')
            ->setPath(sprintf('/v1/clients/%s/reports/%s/supportingdocuments', $caseRef, $reportPdfDocumentUuid))
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'supporting_document' => [
                    'data' => [
                        'type' => 'supportingdocument',
                        'attributes' => [
                            'submission_id' => $matcher->integer(9876)
                        ],
                        'file' => [
                            'name' => 'bank-statement-March.pdf',
                            'mimetype' => 'application/pdf',
                            'source' => $matcher->regex('dGVzdA==', '.+')
                        ]
                    ]
                ]
            ]);

        // Create your expected response from the provider.
        $response = new ProviderResponse();
        $response
            ->setStatus(201)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
              'uuid' => $matcher->uuid($this->reportPdfUuid)]);

        $this->builder
            ->uponReceiving('A submitted supporting document')
            ->with($request)
            ->willRespondWith($response); // This has to be last. This is what makes an API request to the Mock Server to set the interaction.
    }
}
