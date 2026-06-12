<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\JWT;

use Jose\Component\Core\JWK;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use OPG\Digideps\Frontend\Service\JWT\JWTService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class JWTServiceTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private HttpClientInterface $httpClient;

    private string $privateKey;
    private string $publicKeyPem;

    public function setUp(): void
    {
        // Generate an RSA key pair for signing test JWTs
        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

        $privateKey = '';
        openssl_pkey_export($res, $privateKey);
        $this->privateKey = $privateKey;

        $details = openssl_pkey_get_details($res);
        $this->publicKeyPem = $details['key'];

        $this->httpClient = self::createMock(HttpClientInterface::class);
    }

    public function testGetUrnReturnsSubjectFromValidJwt(): void
    {
        $jkuUrl = 'https://digideps.local/user.json';
        $expectedSub = 'urn:opg:digideps:users:42';
        $kid = 'test-kid-' . bin2hex(random_bytes(4));

        // make a real RSA-signed JWT whose kid and jku headers match the JWKs we serve
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->privateKey),
            InMemory::plainText($this->publicKeyPem)
        );

        $jwtString = $config->builder()
            ->withHeader('kid', $kid)
            ->withHeader('jku', $jkuUrl)
            ->relatedTo($expectedSub)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();

        $details = openssl_pkey_get_details(openssl_pkey_get_public($this->publicKeyPem));
        $jwk = new JWK([
            'kty' => 'RSA',
            'kid' => $kid,
            'use' => 'sig',
            'n' => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
            'e' => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
        ]);
        $jwksJson = json_encode(['keys' => [$jwk->all()]]);

        // Mock the HTTP client to return the JWK set when the jku URL is fetched
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn($jwksJson);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', $jkuUrl)
            ->willReturn($mockResponse);

        $service = new JWTService($this->httpClient);

        self::assertSame($expectedSub, $service->getUrn($jwtString));
    }
}
