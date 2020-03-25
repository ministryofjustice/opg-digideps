<?php declare(strict_types=1);

namespace AppBundle\Service\Client\AWS;


use AppBundle\Service\AWS\DefaultCredentialProvider;
use AppBundle\Service\AWS\RequestSigner;
use AppBundle\Service\AWS\SignatureV4Signer;
use Aws\Credentials\Credentials;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class RequestSignerTest extends TestCase
{
    /** @test */
    public function signRequest()
    {
        $headers['X-Amz-Content-Sha256'] = 'A payload';
        $headers['Authorization'] = [
            "AWS4-HMAC-SHA256 "
            . "Credential=abc123/some/scope, "
            . "SignedHeaders={['header' => 'signed']}, Signature={aSignature}"
        ];

        $originalRequest = new Request('GET', 'some.url');
        $signedRequest = new Request('GET', 'some.url', $headers);
        $credentials = new Credentials('aKey', 'aSecret', NULL);
        $service = 'some-service';

        /** @var DefaultCredentialProvider&ObjectProphecy $provider */
        $provider = self::prophesize(DefaultCredentialProvider::class);
        $credentialsPromise = function() use ($credentials) {
            return Promise\promise_for($credentials);
        };

        /** @var SignatureV4Signer&ObjectProphecy $signer */
        $signer = self::prophesize(SignatureV4Signer::class);
        $signer->signRequest($originalRequest, $credentials, $service)->shouldBeCalled()->willReturn($signedRequest);

        $provider->getProvider()->shouldBeCalled()->willReturn($credentialsPromise);

        $sut = new RequestSigner($provider->reveal(), $signer->reveal());
        $sut->signRequest($originalRequest, $service);
    }
}
