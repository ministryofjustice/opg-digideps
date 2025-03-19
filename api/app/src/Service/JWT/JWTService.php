<?php

declare(strict_types=1);

namespace App\Service\JWT;

use App\Entity\User;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Psr\Log\LoggerInterface;

class JWTService
{
    public const JKU_URL_TEMPLATE = '%s/v2/.well-known/jwks.json';

    public function __construct(
        private readonly SecretManagerService $secretManager,
        private readonly LoggerInterface $logger,
        private readonly string $frontendHost,
        private readonly DateTimeProvider $dateTimeProvider
    ) {
    }

    public function createNewJWT(User $user = null)
    {
        $config = $this->initJWTConfig();
        $publicKey = $config->verificationKey()->contents();
        $kid = openssl_digest($publicKey, 'sha256');

        $token = $config
            ->builder()
            ->withHeader('jku', sprintf(self::JKU_URL_TEMPLATE, $this->frontendHost))
            ->withHeader('kid', $kid)
            ->permittedFor(self::generateAud('registration_service'))
            ->issuedAt($this->dateTimeProvider->getDateTimeImmutable('now'))
            ->expiresAt($this->dateTimeProvider->getDateTimeImmutable('+1 hour'))
            ->canOnlyBeUsedAfter($this->dateTimeProvider->getDateTimeImmutable('-10 seconds'))
            ->issuedBy(self::generateIss());

        if ($user) {
            $token
                    ->relatedTo(self::generateSub((string) $user->getId()))
                    ->withClaim('role', self::generateRole($user->getRoleName()));
        }

        return $token
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }

    public function generateJWK()
    {
        $publicKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PUBLIC_JWT_KEY_BASE64_SECRET_NAME));

        // Should this be base64 encoded?
        $kid = openssl_digest($publicKey, 'sha256');

        $keyInfo = openssl_pkey_get_details(openssl_pkey_get_public($publicKey));

        return [
            'keys' => [
                $kid => [
                    'kty' => 'RSA',
                    'n' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyInfo['rsa']['n'])), '='),
                    'e' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyInfo['rsa']['e'])), '='),
                    'kid' => $kid,
                    'alg' => 'RS256',
                    'use' => 'sig',
                ],
            ],
        ];
    }

    public function verify(string $jwt)
    {
        try {
            $config = $this->initJWTConfig();
            $token = $config->parser()->parse($jwt);
            $constraints = $config->validationConstraints();
            $config->validator()->assert($token, ...$constraints);
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('JWT verification failed: %s', $e->getMessage()));

            return false;
        }

        return true;
    }

    public static function generateIss(): string
    {
        return 'urn:opg:digideps';
    }

    public static function generateSub(string $userIdentifier)
    {
        return sprintf('%s:users:%s', self::generateIss(), $userIdentifier);
    }

    public static function generateAud(string $serviceName)
    {
        return sprintf('urn:opg:%s', $serviceName);
    }

    public static function generateRole(string $role)
    {
        return sprintf('%s:%s', self::generateIss(), $role);
    }

    private function initJWTConfig(): Configuration
    {
        $publicKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PUBLIC_JWT_KEY_BASE64_SECRET_NAME));
        $privateKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PRIVATE_JWT_KEY_BASE64_SECRET_NAME));

        $config = Configuration::forAsymmetricSigner(
            new Signer\Rsa\Sha256(),
            Signer\Key\InMemory::plainText($privateKey),
            Signer\Key\InMemory::plainText($publicKey),
        );

        $config->setValidationConstraints(
            new IssuedBy(self::generateIss()),
            new PermittedFor(self::generateAud('registration_service')),
            new SignedWith(new Signer\Rsa\Sha256(), Signer\Key\InMemory::plainText($publicKey))
        );

        return $config;
    }
}
