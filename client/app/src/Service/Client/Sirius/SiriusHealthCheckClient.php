<?php

declare(strict_types=1);

namespace App\Service\Client\Sirius;

use App\Service\AWS\RequestSigner;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SiriusHealthCheckClient
{
    public const string SIRIUS_API_GATEWAY_VERSION = 'v2';

    public function __construct(
        private readonly Client $httpClient,
        private readonly RequestSigner $requestSigner,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $endpoint): ResponseInterface
    {
        $signedRequest = $this->buildSignedRequest($endpoint);

        return $this->httpClient->send($signedRequest, ['connect_timeout' => 1, 'timeout' => 1.5]);
    }

    private function buildSignedRequest(string $endpoint): RequestInterface
    {
        $url = new Uri(sprintf('%s/%s/%s', $this->baseUrl, self::SIRIUS_API_GATEWAY_VERSION, $endpoint));

        $request = new Request('GET', $url, [
            'Accept' => 'application/json',
            'Content-type' => 'application/json',
        ], '');

        // Sign the request with an AWS Authorization header.
        return $this->requestSigner->signRequest($request, 'execute-api');
    }
}
