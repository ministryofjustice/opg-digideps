<?php declare(strict_types=1);

namespace AppBundle\Service\Client\AWS;

use AppBundle\Service\AWS\RequestSigner;
use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class RequestSignerTest extends TestCase
{

    public function testSignRequest()
    {
        $headers['X-Amz-Content-Sha256'] = 'A payload';
        $headers['Authorization'] = [
            "AWS4-HMAC-SHA256 "
            . "Credential=abc123/some/scope, "
            . "SignedHeaders={['header' => 'signed']}, Signature={aSignature}"
        ];

        $originalRequest = new Request('GET', 'some.url');
        $signedRequest = new Request('GET', 'some.url', $headers);

        $signer = self::prophesize(SignatureV4::class);
        $signer->signRequest($originalRequest)->shouldBeCalled()->willReturn($signedRequest);

        $sut = new RequestSigner($signer->reveal());
        $sut->signRequest($originalRequest, 'some-service');
    }
}
