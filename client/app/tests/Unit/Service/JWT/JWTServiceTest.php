<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\JWT;

use PHPUnit\Framework\TestCase;

class JWTServiceTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $jwtHeaders;

    /**
     * @var string[]
     */
    private array $jwtClaims;

    /**
     * @var string[]
     */
    private array $jwtSignature;

    private string $jwtHeadersClaim;
    private string $jwtHeadersClaimSignature;

    public function setUp(): void
    {
        // The props below are all valid values based on a JWT (not used in prod)
        $this->jwtHeaders = [
            'jku' => 'https://digideps.local/v2/.well-known/jwks.json',
            'typ' => 'JWT',
            'alg' => 'RS256',
            'kid' => 'cc57f4dd3bea080baf65e78883ad4874d22d182822350242c3b7a3dd051bf18c',
        ];

        $this->jwtClaims = [
            'aud' => 'urn:opg:registration_service',
            'iat' => '1656359966.779836',
            'exp' => '1656363566.779841',
            'nbf' => '1656359956.779853',
            'iss' => 'urn:opg:digideps',
            'sub' => 'urn:opg:digideps:users:3',
            'role' => 'urn:opg:digideps:ROLE_SUPER_ADMIN',
        ];

        $this->jwtSignature = ['a signature'];

        // Not used in prod
        $this->jwtHeadersClaim = <<<JWT
eyJqa3UiOiJodHRwczpcL1wvZGlnaWRlcHMubG9jYWxcL3YyXC8ud2VsbC1rbm93blwvandrcy5qc29uIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYiLCJraWQiOiJjYzU3ZjRkZDNiZWEwODBiYWY2NWU3ODg4M2FkNDg3NGQyMmQxODI4MjIzNTAyNDJjM2I3YTNkZDA1MWJmMThjIn0.eyJhdWQiOiJ1cm46b3BnOnJlZ2lzdHJhdGlvbl9zZXJ2aWNlIiwiaWF0IjoiMTY1NjM1OTk2Ni43Nzk4MzYiLCJleHAiOiIxNjU2MzYzNTY2Ljc3OTg0MSIsIm5iZiI6IjE2NTYzNTk5NTYuNzc5ODUzIiwiaXNzIjoidXJuOm9wZzpkaWdpZGVwcyIsInN1YiI6InVybjpvcGc6ZGlnaWRlcHM6dXNlcnM6MyIsInJvbGUiOiJ1cm46b3BnOmRpZ2lkZXBzOlJPTEVfU1VQRVJfQURNSU4ifQ
JWT;

        // Not used in prod
        $this->jwtHeadersClaimSignature = <<<JWT
eyJqa3UiOiJodHRwczpcL1wvZGlnaWRlcHMubG9jYWxcL3YyXC8ud2VsbC1rbm93blwvandrcy5qc29uIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYiLCJraWQiOiJjYzU3ZjRkZDNiZWEwODBiYWY2NWU3ODg4M2FkNDg3NGQyMmQxODI4MjIzNTAyNDJjM2I3YTNkZDA1MWJmMThjIn0.eyJhdWQiOiJ1cm46b3BnOnJlZ2lzdHJhdGlvbl9zZXJ2aWNlIiwiaWF0IjoiMTY1NjM1OTk2Ni43Nzk4MzYiLCJleHAiOiIxNjU2MzYzNTY2Ljc3OTg0MSIsIm5iZiI6IjE2NTYzNTk5NTYuNzc5ODUzIiwiaXNzIjoidXJuOm9wZzpkaWdpZGVwcyIsInN1YiI6InVybjpvcGc6ZGlnaWRlcHM6dXNlcnM6MyIsInJvbGUiOiJ1cm46b3BnOmRpZ2lkZXBzOlJPTEVfU1VQRVJfQURNSU4ifQ.WyJhIHNpZ25hdHVyZSJd
JWT;
    }

    public function testBase64EncodeJWTMissingSignature()
    {
        self::assertSame($this->jwtHeadersClaim, self::base64EncodeJWT($this->jwtHeaders, $this->jwtClaims));
    }

    public function testBase64EncodeJWTWithSignature()
    {
        self::assertSame($this->jwtHeadersClaimSignature, self::base64EncodeJWT($this->jwtHeaders, $this->jwtClaims, $this->jwtSignature));
    }

    private static function base64EncodeJWT(array $headers, array $claims, ?array $signature = null)
    {
        $headers = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($headers)));
        $claims = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($claims)));

        $jwt = sprintf('%s.%s', $headers, $claims);

        if (isset($signature)) {
            $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($signature)));
            $jwt = sprintf('%s.%s', $jwt, $signature);
        }

        return $jwt;
    }
}
