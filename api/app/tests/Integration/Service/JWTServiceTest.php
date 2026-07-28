<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\InvalidKeyProvided;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Service\JWT\JWTService;
use OPG\Digideps\Backend\Service\SecretManagerService;
use OPG\Digideps\Backend\Service\Time\DateTimeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JWTServiceTest extends TestCase
{
    private SecretManagerService&MockObject $secretsManager;
    private LoggerInterface&MockObject $logger;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private string $publicKeyPem;
    private string $privateKeyPem;
    private JWTService $sut;

    public function setUp(): void
    {
        [$this->publicKeyPem, $this->privateKeyPem] = $this->createPemKeyPair();

        $this->secretsManager = self::createMock(SecretManagerService::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);

        $this->secretsManager->expects(self::atLeastOnce())
            ->method('getSecret')
            ->willReturnCallback(function (string $keyname) {
                return match ($keyname) {
                    SecretManagerService::PRIVATE_JWT_KEY_BASE64_SECRET_NAME => base64_encode($this->privateKeyPem),
                    SecretManagerService::PUBLIC_JWT_KEY_BASE64_SECRET_NAME => base64_encode($this->publicKeyPem),
                    default => throw new \InvalidArgumentException('Invalid key name')
                };
            });

        $this->sut = new JWTService(
            $this->secretsManager,
            $this->logger,
            'https://example.org',
            $this->dateTimeProvider
        );
    }

    /** @test */
    public function verifyWithAValidJWT(): void
    {
        $jwt = $this->createSignedJWTString();

        self::assertTrue($this->sut->verify($jwt));
    }

    /** @test */
    public function verifyWithMissingAlgorithmFails(): void
    {
        $jwt = $this->createUnsignedJWTString('https://example.org');

        self::assertFalse($this->sut->verify($jwt));
    }

    /** @test */
    public function verifyWithIncorrectAudienceFails(): void
    {
        $jwt = $this->createSignedJWTString('wrong_aud');

        self::assertFalse($this->sut->verify($jwt));
    }

    /** @test */
    public function verifyWithIncorrectIssuerFails(): void
    {
        $jwt = $this->createSignedJWTString('urn:opg:registration_service', 'wrong_iss');

        self::assertFalse($this->sut->verify($jwt));
    }

    /** @test */
    public function createNewJWT(): void
    {
        if ($this->privateKeyPem === '' || $this->publicKeyPem === '') {
            throw new \InvalidArgumentException('One or both keys are empty');
        }

        $now = new \DateTimeImmutable();
        $plus1Hour = new \DateTimeImmutable('+1 hour');
        $sub10Seconds = new \DateTimeImmutable('-10 seconds');

        $this->dateTimeProvider->method('getDateTimeImmutable')
            ->willReturnCallback(function (string $delta) use ($now, $plus1Hour, $sub10Seconds) {
                return match ($delta) {
                    'now' => $now,
                    '+1 hour' => $plus1Hour,
                    '-10 seconds' => $sub10Seconds,
                    default => throw new \InvalidArgumentException('Invalid time delta supplied')
                };
            });

        $user = new User()
            ->setId(22)
            ->setRoleName('A_ROLE');

        $actualJwt = $this->sut->createNewJWT($user);

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->privateKeyPem),
            InMemory::plainText($this->publicKeyPem),
        );

        /** @var UnencryptedToken $token */
        $token = $config->parser()->parse($actualJwt);

        self::assertTrue($token->hasBeenIssuedBy('urn:opg:digideps'));
        self::assertTrue($token->isPermittedFor('urn:opg:registration_service'));
        self::assertTrue($token->isRelatedTo('urn:opg:digideps:users:22'));
        self::assertEquals('urn:opg:digideps:A_ROLE', $token->claims()->get('role'));

        self::assertTrue($token->isMinimumTimeBefore($sub10Seconds));
        self::assertTrue($token->hasBeenIssuedBefore($now));
        self::assertFalse($token->isExpired(new \DateTime('+59 minutes')));
    }

    private function createSignedJWTString(
        string $aud = 'urn:opg:registration_service',
        string $iss = 'urn:opg:digideps',
        string $jkuAddress = 'https://example.org',
    ): string {
        if ($this->privateKeyPem === '' || $this->publicKeyPem === '') {
            throw new \InvalidArgumentException('One or both keys are empty');
        }

        $kid = openssl_digest($this->publicKeyPem, 'sha256');

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->privateKeyPem),
            InMemory::plainText($this->publicKeyPem)
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

    private function createUnsignedJWTString(string $jkuAddress): string
    {
        $kid = openssl_digest($this->publicKeyPem, 'sha256');

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

    /**
     * @return array<int, non-empty-string>
     */
    private function createPemKeyPair(): array
    {
        $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $keyPair = openssl_pkey_new($options);

        $publicKeyPem = '';
        $privateKeyPem = '';
        if ($keyPair instanceof \OpenSSLAsymmetricKey) {
            $keyDetails = openssl_pkey_get_details($keyPair);
            if (is_array($keyDetails) && is_string($keyDetails['key'])) {
                $publicKeyPem = $keyDetails['key'];
            }

            openssl_pkey_export($keyPair, $privateKeyPem);
        }

        if (!is_string($privateKeyPem) || $privateKeyPem === '' || $publicKeyPem === '') {
            throw new InvalidKeyProvided('Unable to get keys from key pair');
        }

        return [$publicKeyPem, $privateKeyPem];
    }
}
