<?php

declare(strict_types=1);

namespace App\Service\Client\AWS;

use App\Service\AWS\DefaultCredentialProvider;
use App\Service\AWS\RequestSigner;
use App\Service\AWS\SignatureV4Signer;
use Aws\Credentials\Credentials;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class RequestSignerTest extends TestCase
{
    public function testSignRequest(): void
    {
        $headers = [
            'X-Amz-Content-Sha256' => 'A payload',
            'Authorization' => [
                'AWS4-HMAC-SHA256 '
                .'Credential=abc123/some/scope, '
                ."SignedHeaders={['header' => 'signed']}, Signature={aSignature}",
            ],
        ];

        $originalRequest = new Request('GET', 'some.url');
        $signedRequest = new Request('GET', 'some.url', $headers);

        $expectedCredentials = new Credentials('aFakeSecretAccessKeyId', 'aFakeSecretAccessKey', 'fakeValue');
        $service = 'some-service';
        $provider = $this->getMockBuilder(DefaultCredentialProvider::class)
            ->onlyMethods(['getCredentials'])
            ->getMock();

        $provider->expects($this->once())
            ->method('getCredentials')
            ->willReturn($expectedCredentials);

        $signer = $this->createMock(SignatureV4Signer::class);
        $signer->expects($this->once())
            ->method('signRequest')
            ->with(
                $this->equalTo($originalRequest),
                $this->callback(function ($actualCredentials) use ($expectedCredentials) {
                    return $actualCredentials instanceof Credentials
                        && $actualCredentials->getAccessKeyId() === $expectedCredentials->getAccessKeyId()
                        && $actualCredentials->getSecretKey() === $expectedCredentials->getSecretKey()
                        && $actualCredentials->getSecurityToken() === $expectedCredentials->getSecurityToken();
                }),
                $this->equalTo($service)
            )
            ->willReturn($signedRequest);

        $sut = new RequestSigner($provider, $signer);
        $result = $sut->signRequest($originalRequest, $service);

        $this->assertEquals($signedRequest, $result);
    }
}
