<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Sirius;


use AppBundle\Service\AWS\RequestSigner;
use DateTime;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;

class SiriusApiGatewayClientTest extends KernelTestCase
{
    /** @var string */
    private $baseURL;

    /** @var string */
    private $endpoint;

    /** @var Client&ObjectProphecy */
    private $httpClient;

    /** @var RequestSigner&ObjectProphecy */
    private $requestSigner;

    /** @var Serializer */
    private $serializer;

    public function setUp(): void
    {
        $this->baseURL = 'test.com';
        $this->endpoint = 'an-endpoint';
        $this->httpClient = self::prophesize(Client::class);
        $this->requestSigner = self::prophesize(RequestSigner::class);
        $this->serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('serializer');

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
