<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Sirius;

use AppBundle\Service\AWS\RequestSigner;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class SiriusApiGatewayClientTest extends TestCase
{
    /** @test */
    public function get()
    {
        $baseURL = 'test.com';
        $endpoint = 'an-endpoint';
        $expectedRequest = $this->buildRequest($baseURL, $endpoint, 'GET');

        /** @var Client&ObjectProphecy $httpClient */
        $httpClient = self::prophesize(Client::class);

        /** @var RequestSigner&ObjectProphecy $requestSigner */
        $requestSigner = self::prophesize(RequestSigner::class);

        $signedRequest = $this->buildRequest($baseURL, $endpoint, 'GET' ,['A-Header' => 'value']);
        $requestSigner->signRequest($expectedRequest, 'execute-api')->shouldBeCalled()->willReturn($signedRequest);

        $httpClient->send($signedRequest)->shouldBeCalled()->willReturn(new Response());

        $sut = new SiriusApiGatewayClient($httpClient->reveal(), $requestSigner->reveal(), $baseURL);
        $sut->get($endpoint);
    }

    /** @test */
    public function post()
    {
        $baseURL = 'test.com';
        $endpoint = 'an-endpoint';
        $expectedRequest = $this->buildRequest($baseURL, $endpoint, 'POST');

        /** @var Client&ObjectProphecy $httpClient */
        $httpClient = self::prophesize(Client::class);

        /** @var RequestSigner&ObjectProphecy $requestSigner */
        $requestSigner = self::prophesize(RequestSigner::class);

        $signedRequest = $this->buildRequest($baseURL, $endpoint, 'POST', ['A-Header' => 'value']);
        $requestSigner->signRequest($expectedRequest, 'execute-api')->shouldBeCalled()->willReturn($signedRequest);

        $httpClient->send($signedRequest)->shouldBeCalled()->willReturn(new Response());

        $sut = new SiriusApiGatewayClient($httpClient->reveal(), $requestSigner->reveal(), $baseURL);
        $sut->post($endpoint);
    }

    private function buildRequest(string $baseURL, string $endpoint, string $method, array $additionalHeaders=[])
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ];

        if (count($additionalHeaders) > 0) {
            $headers = array_merge($headers, $additionalHeaders);
        }

        $url = new Uri(sprintf("%s/%s", $baseURL, $endpoint));

        return new Request($method, $url, $headers);
    }
}
