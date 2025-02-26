<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\User;
use App\Service\JWT\JWTService;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class JWTServiceTest extends TestCase
{
    use ProphecyTrait;

    private SecretManagerService|ObjectProphecy $secretsManager;
    private LoggerInterface|ObjectProphecy $logger;
    private DateTimeProvider|ObjectProphecy $dateTimeProvider;
    private string $publicKeyPem;
    private string $privateKeyPem;

    public function setUp(): void
    {
        [$this->publicKeyPem, $this->privateKeyPem] = $this->createPemKeyPair();

        $this->secretsManager = self::prophesize(SecretManagerService::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $this->secretsManager->getSecret('public-jwt-key-base64')->willReturn(base64_encode($this->publicKeyPem));
        $this->secretsManager->getSecret('private-jwt-key-base64')->willReturn(base64_encode($this->privateKeyPem));

        $this->sut = new JWTService(
            $this->secretsManager->reveal(),
            $this->logger->reveal(),
            'https://example.org',
            $this->dateTimeProvider->reveal()
        );
    }

    /** @test */
    public function verifyWithAValidJWT()
    {
        $jwt = $this->createSignedJWTString($this->publicKeyPem, $this->privateKeyPem);

        self::assertTrue($this->sut->verify($jwt));
    }

    /** @test */
    public function verifyWithMissingAlgorithmFails()
    {
        $jwt = $this->createUnsignedJWTString($this->publicKeyPem, 'https://example.org');

        self::assertFalse($this->sut->verify($jwt));
    }

    /** @test */
    public function verifyWithIncorrectAudienceFails()
    {
        $jwt = $this->createSignedJWTString($this->publicKeyPem, $this->privateKeyPem, 'wrong_aud');

        self::assertFalse($this->sut->verify($jwt));
    }

    /** @test */
    public function verifyWithIncorrectIssuerFails()
    {
        $jwt = $this->createSignedJWTString($this->publicKeyPem, $this->privateKeyPem, 'urn:opg:registration_service', 'wrong_iss');

        self::assertFalse($this->sut->verify($jwt));
    }

    /** @test */
    public function createNewJWT()
    {
        $now = new \DateTimeImmutable();
        $plus1Hour = new \DateTimeImmutable('+1 hour');
        $sub10Seconds = new \DateTimeImmutable('-10 seconds');

        $this->dateTimeProvider->getDateTimeImmutable('now')->willReturn($now);
        $this->dateTimeProvider->getDateTimeImmutable('+1 hour')->willReturn($plus1Hour);
        $this->dateTimeProvider->getDateTimeImmutable('-10 seconds')->willReturn($sub10Seconds);

        $user = (new User())
            ->setId(22)
            ->setRoleName('A_ROLE');

        $actuaklJwt = $this->sut->createNewJWT($user);

        $config = Configuration::forAsymmetricSigner(
            new Signer\Rsa\Sha256(),
            InMemory::plainText($this->privateKeyPem),
            InMemory::plainText($this->publicKeyPem),
        );

        $token = $config->parser()->parse($actuaklJwt);

        self::assertTrue($token->hasBeenIssuedBy('urn:opg:digideps'));
        self::assertTrue($token->isPermittedFor('urn:opg:registration_service'));
        self::assertTrue($token->isRelatedTo('urn:opg:digideps:users:22'));
        self::assertSame('urn:opg:digideps:A_ROLE', $token->claims()->get('role'));

        self::assertTrue($token->isMinimumTimeBefore($sub10Seconds));
        self::assertTrue($token->hasBeenIssuedBefore($now));
        self::assertFalse($token->isExpired(new \DateTime('+59 minutes')));
    }

    private function createSignedJWTString(
        string $publicKey,
        string $privateKey,
        string $aud = 'urn:opg:registration_service',
        string $iss = 'urn:opg:digideps',
        string $jkuAddress = 'https://example.org',
    ) {
        $kid = openssl_digest($publicKey, 'sha256');

        $config = Configuration::forAsymmetricSigner(
            new Signer\Rsa\Sha256(),
            InMemory::plainText($privateKey),
            InMemory::plainText($publicKey)
        );

        $plainToken = $config->builder()
            ->withHeader('jku', $jkuAddress)
            ->withHeader('kid', $kid)
            ->permittedFor($aud)
            ->issuedAt(new \DateTimeImmutable())
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->canOnlyBeUsedAfter(new \DateTimeImmutable('-10 seconds'))
            ->issuedBy($iss)
            ->relatedTo('user-id-1')
            ->withClaim('role', 'admin')
            ->getToken($config->signer(), $config->signingKey());

        return $plainToken->toString();
    }

    private function createUnsignedJWTString(string $publicKey, string $jkuAddress)
    {
        $kid = openssl_digest($publicKey, 'sha256');

        $config = Configuration::forUnsecuredSigner();

        $plainToken = $config->builder()
            ->withHeader('jku', $jkuAddress)
            ->withHeader('kid', $kid)
            ->permittedFor('urn:opg:registration_service')
            ->issuedAt(new \DateTimeImmutable())
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->canOnlyBeUsedAfter(new \DateTimeImmutable('-10 seconds'))
            ->issuedBy('urn:opg:digideps')
            ->relatedTo('user-id-1')
            ->withClaim('role', 'admin')
            ->getToken($config->signer(), $config->signingKey());

        return $plainToken->toString();
    }

    private function createPemKeyPair()
    {
        $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $keyPair = openssl_pkey_new($options);
        $publicKeyPem = openssl_pkey_get_details($keyPair)['key'];
        openssl_pkey_export($keyPair, $privateKeyPem);

        return [$publicKeyPem, $privateKeyPem];
    }
}
