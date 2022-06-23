<?php

declare(strict_types=1);

namespace App\Service\JWT;

use App\Entity\User;
use App\Service\SecretManagerService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;

class JWTService
{
    public const JKU_URL_TEMPLATE = '%s/v2/.well-known/jwks.json';
    public const JWT_ISS = 'digideps';

    public function __construct(
        private SecretManagerService $secretManager,
        private LoggerInterface $logger,
        private string $frontendHost
    ) {
    }

    public function createNewJWT(?User $user = null)
    {
        $privateKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PRIVATE_JWT_KEY_BASE64_SECRET_NAME));
        $publicKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PUBLIC_JWT_KEY_BASE64_SECRET_NAME));

        $kid = openssl_digest($publicKey, 'sha256');
        $payload = [
            'aud' => 'registration_service',
            'iat' => strtotime('now'),
            'exp' => strtotime('+1 hour'),
            'nbf' => strtotime('-10 seconds'),
            'iss' => self::JWT_ISS,
        ];

        if ($user) {
            $payload['sub'] = $user->getId();
            $payload['role'] = $user->getRoleName();
        }

        return JWT::encode($payload, $privateKey, 'RS256', $kid, ['jku' => sprintf(self::JKU_URL_TEMPLATE, $this->frontendHost)]);
    }

    public function generateJWK()
    {
        $publicKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PUBLIC_JWT_KEY_BASE64_SECRET_NAME));

        // Should this be base64 encoded?
        $kid = openssl_digest($publicKey, 'sha256');

        $keyInfo = openssl_pkey_get_details(openssl_pkey_get_public($publicKey));

        return [
            'keys' => [
                [
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
            $publicKey = base64_decode($this->secretManager->getSecret(SecretManagerService::PUBLIC_JWT_KEY_BASE64_SECRET_NAME));
            // Asserts on nbd, iat, exp and signature
            $decoded = (array) JWT::decode($jwt, new Key($publicKey, 'RS256'));
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('JWT verification failed: %s', $e->getMessage()));

            return false;
        }

        return self::JWT_ISS === $decoded['iss'];
    }
}
