<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


use AppBundle\Service\AWS\RequestSigner;
use DateTime;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SiriusApiGatewayClientTest extends TestCase
{
    /** @var string */
    private $baseURL;

    /** @var string */
    private $endpoint;

    /** @var Client&ObjectProphecy */
    private $httpClient;

    /** @var RequestSigner&ObjectProphecy */
    private $requestSigner;

    /** @var string */
    private $uploadReportPdfJSONBlob;

    /** @var string */
    private $reportPdfSuccessResponseBody;

    /** @var string */
    private $supportingDocumentSuccessResponseBody;

    /** @var Serializer&ObjectProphecy */
    private $serializer;

    /** @var string */
    private $uploadSupportingDocumentJSONBlob;

    public function setUp(): void
    {
        $this->baseURL = 'test.com';
        $this->endpoint = 'an-endpoint';

        $this->uploadReportPdfJSONBlob = json_encode(
            [
                'report' => [
                    'type' => 'reports',
                    'attributes' => [
                        'reportingPeriodFrom' => '2019-01-01T00:00:00+00:00',
                        'orderType' => 'PF',
                        'reportingPeriodTo' => '2019-12-31T00:00:00+00:00',
                        'year' => '2019',
                        'dateSubmitted' => '2020-01-03T09:30:00+00:00'
                    ]
                ],
                'report_file' => [ 'JVBERi0xLjMKJcT...etc==' ]
            ]
        );

        /** @TODO check final swagger doc for format once ready */
        $this->uploadSupportingDocumentJSONBlob = json_encode(
            [
                'report' => [
                    'type' => 'supportingdocuments',
                    'attributes' => [
                        'submissionId' => '5a8b1a26-8296-4373-ae61-f8d0b250e773',
                    ]
                ],
                'supporting_document_file' => [ 'JVBERi0xLjMKJcT...etc==' ]
            ]
        );

        $this->reportPdfSuccessResponseBody = json_encode(['data' => ['type' => 'reports', 'id' => '5a8b1a26-8296-4373-ae61-f8d0b250e773']]);
        $this->supportingDocumentSuccessResponseBody = json_encode(['data' => ['type' => 'supportingdocuments', 'id' => '5a8b1a26-8296-4373-ae61-f8d0b250e773']]);
        $this->httpClient = self::prophesize(Client::class);
        $this->requestSigner = self::prophesize(RequestSigner::class);
        $this->serializer = new Serializer([new DateTimeNormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /** @test */
    public function get()
    {
        $expectedRequest = $this->buildRequest($this->baseURL, $this->endpoint, 'GET');
        $signedRequest = $this->buildRequest($this->baseURL, $this->endpoint, 'GET' ,['A-Header' => 'value']);

        $this->requestSigner->signRequest($expectedRequest, 'execute-api')->shouldBeCalled()->willReturn($signedRequest);
        $this->httpClient->send($signedRequest)->shouldBeCalled()->willReturn(new Response());

        $sut = new SiriusApiGatewayClient($this->httpClient->reveal(), $this->requestSigner->reveal(), $this->baseURL, $this->serializer);
        $sut->get($this->endpoint);
    }

    /** @test */
    public function sendReportPdfDocument()
    {
        $signedRequest = $this->buildRequest(
            $this->baseURL,
            'clients/1/reports',
            'POST',
            ['A-Header' => 'value'],
            $this->uploadReportPdfJSONBlob
        );

        $this->requestSigner->signRequest(Argument::type(Request::class), 'execute-api')->shouldBeCalled()->willReturn($signedRequest);
        $this->httpClient->send($signedRequest)->shouldBeCalled()->willReturn(new Response('200', [], $this->reportPdfSuccessResponseBody));

        $sut = new SiriusApiGatewayClient($this->httpClient->reveal(), $this->requestSigner->reveal(), $this->baseURL, $this->serializer);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            new DateTime('2019-01-01'),
            new DateTime('2019-12-31'),
            new DateTime('2020-01-03T09:30:00.001Z'),
            'PF'
        );

        $sut->sendReportPdfDocument($siriusDocumentUpload, 'JVBERi0xLjMKJcT...etc==', '1234567T');
    }

    /** @test */
    public function sendSupportingDocument()
    {
        $signedRequest = $this->buildRequest(
            $this->baseURL,
            'reports/uuid-goes-123-here/supportingdocuments',
            'POST' ,
            ['A-Header' => 'value'],
            $this->uploadSupportingDocumentJSONBlob
        );

        $this->requestSigner->signRequest(Argument::type(Request::class), 'execute-api')->shouldBeCalled()->willReturn($signedRequest);
        $this->httpClient->send($signedRequest)->shouldBeCalled()->willReturn(new Response('200', [], $this->supportingDocumentSuccessResponseBody));

        $sut = new SiriusApiGatewayClient($this->httpClient->reveal(), $this->requestSigner->reveal(), $this->baseURL, $this->serializer);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(123);

        $sut->sendSupportingDocument($siriusDocumentUpload, 'JVBERi0xLjMKJcT...etc==', '1234567T');
    }

    private function buildRequest(string $baseURL, string $endpoint, string $method, array $additionalHeaders=[], string $body="")
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ];

        if (count($additionalHeaders) > 0) {
            $headers = array_merge($headers, $additionalHeaders);
        }

        $url = sprintf("%s/%s", $baseURL, $endpoint);

        return new Request($method, $url, $headers, $body);
    }
}
