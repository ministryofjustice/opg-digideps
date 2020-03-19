<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Service\AWS\RequestSigner;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use DateTime;
use DigidepsTests\Helpers\DocumentHelpers;
use DigidepsTests\Helpers\MultipartPactRequest;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusDocumentsContractTest extends KernelTestCase
{
    /**
     * As we have multiple callouts for the main syncDocument function to Digideps API and Sirius this test
     * focuses purely on the Sirius callout to minimise complexity.
     *
     * @test
     *
     * @throws \Exception
     */
    public function sendDocuments()
    {
        $matcher = new Matcher();

        $multipartRequest = new MultipartPactRequest();
        $multipartRequest->addPart('report_file', 'c29tZV9jb250ZW50');
        $multipartRequest->addPart('report', [
            'data' => [
                'type' => 'reports',
                'attributes' => [
                    'reporting_period_from' => $matcher->dateISO8601('2018-05-14'),
                    'reporting_period_to' => $matcher->dateISO8601('2019-05-13'),
                    'year' => $matcher->regex('2018', '[0-9]{4}'),
                    'date_submitted' => $matcher->dateTimeISO8601('2019-06-20T00:00:00+01:00'),
                    'type' => $matcher->regex('PF', 'PF|HW|NDR'),
                    'submission_id' => $matcher->integer(9876)
                ]
            ]
        ]);

        $caseRef = '1234567T';

        // Create your expected request from the consumer.
        $request = new ConsumerRequest();
        $request
            ->setMethod('POST')
            ->setPath(sprintf('/clients/%s/reports', $caseRef))
            ->setHeaders(
                [
                    'Content-Type' =>
                        $matcher->regex(
                            'multipart/form-data; boundary=5872fc54a8fa5f5be65ee0af590d1ae813a1b091',
                            'multipart\/form-data.*'
                        )
                ]
            )
            ->setBody($matcher->regex($multipartRequest->getExampleBody(), $multipartRequest->getRegex('report')));

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
                        'year' => $matcher->regex('2018', '[0-9]{4}'),
                        'date_submitted' => $matcher->dateTimeWithMillisISO8601(),
                        'type' => $matcher->regex('PF', 'PF|HW|NDR'),
                        'submission_id' => $matcher->integer()
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

        $client = new GuzzleClient();
        $signer = self::prophesize(RequestSigner::class);
        $baseUrl = 'http://pact-mock';
        $serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('serializer');

        $signer->signRequest(Argument::type(Request::class), 'execute-api')->willReturnArgument(0);

        $sut = new SiriusApiGatewayClient($client, $signer->reveal(), $baseUrl, $serializer);

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

        $result = $sut->sendReportPdfDocument($upload, 'some_content', $caseRef);

        $builder->verify();

        self::assertStringContainsString(
            '33ea0382-cfc9-4776-9036-667eeb68fa4b',
            $result->getBody()->getContents()
        );
    }

    // Write provider for the different docs and responses we expect from Sirius
        // Create another regex for supporting docs
        // Create a failure response
        // Go trough all variations that are defined in swagger doc
    public function uploadProvider()
    {
        return [

        ];
    }
}
