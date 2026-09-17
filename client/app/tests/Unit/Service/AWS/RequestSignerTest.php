<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\AWS;

use Aws\Credentials\Credentials;
use GuzzleHttp\Psr7\Request;
use OPG\Digideps\Frontend\Service\AWS\DefaultCredentialProvider;
use OPG\Digideps\Frontend\Service\AWS\RequestSigner;
use OPG\Digideps\Frontend\Service\AWS\SignatureV4Signer;
use PHPUnit\Framework\TestCase;

class RequestSignerTest extends TestCase
{
    public function testSignRequest(): void
    {
        $headers = [
            'X-Amz-Content-Sha256' => 'A payload',
            'Authorization' => [
                'AWS4-HMAC-SHA256 '
                . 'Credential=abc123/some/scope, '
                . "SignedHeaders={['header' => 'signed']}, Signature={aSignature}",
            ],
        ];

        $originalRequest = new Request('GET', 'some.url');
        $signedRequest = new Request('GET', 'some.url', $headers);

        $expectedCredentials = new Credentials('aFakeSecretAccessKeyId', 'aFakeSecretAccessKey', 'fakeValue');
        $service = 'some-service';
        $provider = $this->getMockBuilder(DefaultCredentialProvider::class)
            ->onlyMethods(['getCredentials'])
            ->getMock();

        $provider->expects(self::once())
            ->method('getCredentials')
            ->willReturn($expectedCredentials);

        $signer = self::createMock(SignatureV4Signer::class);
        $signer->expects(self::once())
            ->method('signRequest')
            ->with(
                self::equalTo($originalRequest),
                self::callback(function ($actualCredentials) use ($expectedCredentials) {
                    return $actualCredentials instanceof Credentials
                        && $actualCredentials->getAccessKeyId() === $expectedCredentials->getAccessKeyId()
                        && $actualCredentials->getSecretKey() === $expectedCredentials->getSecretKey()
                        && $actualCredentials->getSecurityToken() === $expectedCredentials->getSecurityToken();
                }),
                self::equalTo($service)
            )
            ->willReturn($signedRequest);

        $result = new RequestSigner($provider, $signer)->signRequest($originalRequest, $service);

        self::assertEquals($signedRequest, $result);
    }
}
