<?php

declare(strict_types=1);

namespace App\Service\JWT;

use App\Entity\User;
use Firebase\JWT\JWT;

class JWTService
{
    public function __construct(private string $rootDir)
    {
    }

    public function createNewJWT(User $user)
    {
        $privateKey = file_get_contents(sprintf('%s/config/jwt/private.pem', $this->rootDir));
        $publicKey = file_get_contents(sprintf('%s/config/jwt/public.pem', $this->rootDir));

        $kid = openssl_digest($publicKey, 'sha256');
        $payload = [
            'username' => $user->getEmail(),
            'userId' => $user->getId(),
            'aud' => 'registration_service',
            'iat' => strtotime('now'),
            'exp' => strtotime('+1 hour'),
            'nbf' => strtotime('-10 seconds'),
            'iss' => 'digideps',
            'sub' => $user->getEmail(),
        ];

        return JWT::encode($payload, $privateKey, 'RS256', $kid, ['jku' => 'https://frontend/v2/.well-known/jwks.json']);
    }

    public function generateJWK()
    {
        $publicKey = file_get_contents(sprintf('%s/config/jwt/public.pem', $this->rootDir));
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
}
