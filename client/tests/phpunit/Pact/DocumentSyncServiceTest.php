<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\AWS\RequestSigner;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\Client\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use DigidepsTests\Helpers\DocumentHelpers;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use JMS\Serializer\Serializer;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
    public function handleSiriusSync()
    {
        $matcher = new Matcher();

        $exampleBody = "--aed7f18b4198ed16e793694efb19852a47488dfe\r\nContent-Disposition: form-data; name=\"report\"\r\nContent-Length: 233\r\n\r\n{\"data\":{\"type\":\"reports\",\"attributes\":{\"reporting_period_from\":\"2018-05-14\",\"reporting_period_to\":\"2019-05-13\",\"year\":\"2018\",\"date_submitted\":\"2019-06-20T00:00:00+01:00\",\"type\":\"PF\",\"submission_id\":9876}}}\r\n--aed7f18b4198ed16e793694efb19852a47488dfe\r\nContent-Disposition: form-data; name=\"report_file\"\r\nContent-Length: 16\r\n\r\nc29tZV9jb250ZW50\r\n--aed7f18b4198ed16e793694efb19852a47488dfe--\r\n";

        $requestRegexObj = [
            'data' => [
                'type' => 'reports',
                'attributes' => [
                    'reporting_period_from' => '\d{4}-\d{2}-\d{2}',
                    'reporting_period_to' => '\d{4}-\d{2}-\d{2}',
                    'year' => '\d{4}',
                    'date_submitted' => '\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}',
                    'type' => '(PF|HW|NDR)',
                    'submission_id' => '\d+'
                ]
            ]
        ];

        $requestRegex = str_replace(['{"','"}', '\\\\', '"\d+"', '}}}'], ['\{"', '"\}', '\\', '\d+', '}\}\}'], json_encode($requestRegexObj));

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
        $serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('jms_serializer');

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
}
