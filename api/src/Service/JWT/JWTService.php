<?php

declare(strict_types=1);

namespace App\Service\JWT;

use App\Entity\User;
use Firebase\JWT\JWT;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class JWTService
{
    private JWTEncoderInterface $JWTEncoder;
    private string $rootDir;

    public function __construct(
        JWTEncoderInterface $JWTEncoder,
        string $rootDir
    ) {
        $this->JWTEncoder = $JWTEncoder;
        $this->rootDir = $rootDir;
    }

    public function createNewJWT(User $user)
    {
        $privateKey = file_get_contents(sprintf('%s/config/jwt/private.pem', $this->rootDir));
        $publicKey = file_get_contents(sprintf('%s/config/jwt/public.pem', $this->rootDir));

        $kid = openssl_digest($publicKey, 'sha256');
        $payload = [
            'username' => $user->getEmail(),
            'userId' => $user->getId(),
        ];

        return JWT::encode($payload, $privateKey, 'RS256', $kid, ['jku' => 'https://frontend/v2/.well-known/jwks.json']);
    }

    public function generateJWK()
    {
        $publicKey = file_get_contents(sprintf('%s/config/jwt/public.pem', $this->rootDir));
        // This should be base64 encoded
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
}
