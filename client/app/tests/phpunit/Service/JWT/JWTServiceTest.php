<?php

declare(strict_types=1);

use App\Service\JWT\JWTService;
use PHPUnit\Framework\TestCase;

/**
 * @property string[]     $jwtHeaders
 * @property array        $jwtClaims
 * @property string[]     $jwtSignature
 * @property string       $jwtHeadersClaim
 * @property string       $jwtHeadersClaimSignature
 * @property string[][][] $jwks
 */
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

    private array $jwks;

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

        $this->jwks = [
            'keys' => [
                'cc57f4dd3bea080baf65e78883ad4874d22d182822350242c3b7a3dd051bf18c' => [
                    'kty' => 'RSA',
                    'n' => '10dGTg473Av9lRp_jhvWIo7oG8qm_FTOj-YpieNScOkCZsgWuSLuYzElBRpDAAq6zMr1SwXYSGSbPzAoYd0U9rWLO3AKuVHoZbwd5RjKen-l5lVOWmF2da6vnPyOxwKowA3dPhGsSPOXCU7TitKHGz7fJCDMMdbxxZMdX2qfIpWN9n90gyjOQYqilQtJLnBDNYtYNhEU6o_fsVkOdspP_gJIQE--NpXW9udaQ8mjIhuFfa_b8ucp_puJXtgeNNGiJ4ebwE0hNLBDhCLeXGSlvGvjf9P3c1oIR9z5i_12h8X7pQ2nxBT1d4shzWaFc07OIzssAwYDu4c5M41ilcAiCQ',
                    'e' => 'AQAB',
                    'kid' => 'cc57f4dd3bea080baf65e78883ad4874d22d182822350242c3b7a3dd051bf18c',
                    'alg' => 'RS256',
                    'use' => 'sig',
                ],
            ],
        ];
    }

    /** @test */
    public function base64EncodeJWTMissingSignature()
    {
        self::assertSame($this->jwtHeadersClaim, JWTService::base64EncodeJWT($this->jwtHeaders, $this->jwtClaims));
    }

    /** @test */
    public function base64EncodeJWTWithSignature()
    {
        self::assertSame($this->jwtHeadersClaimSignature, JWTService::base64EncodeJWT($this->jwtHeaders, $this->jwtClaims, $this->jwtSignature));
    }

    /** @test */
    public function getJWTHeaders()
    {
        $sut = new JWTService();
        $actualHeaders = $sut->getJWTHeaders($this->jwtHeadersClaimSignature);

        self::assertSame($this->jwtHeaders, $actualHeaders);
    }

    /** @test */
    public function getJWTClaims()
    {
        $sut = new JWTService();
        $actualHeaders = $sut->getJWTClaims($this->jwtHeadersClaimSignature);

        $this->jwtClaims['aud'] = [$this->jwtClaims['aud']];
        $this->jwtClaims['iat'] = DateTimeImmutable::createFromFormat('U.u', $this->jwtClaims['iat']);
        $this->jwtClaims['exp'] = DateTimeImmutable::createFromFormat('U.u', $this->jwtClaims['exp']);
        $this->jwtClaims['nbf'] = DateTimeImmutable::createFromFormat('U.u', $this->jwtClaims['nbf']);

        self::assertEquals($this->jwtClaims, $actualHeaders);
    }

    /** @test */
    public function getJWTSignature()
    {
        $sut = new JWTService();
        $signature = $sut->getJWTSignature($this->jwtHeadersClaimSignature);

        self::assertSame(json_encode($this->jwtSignature), $signature);
    }

    /** @test */
    public function getPublicKeyByJWK()
    {
        $sut = new JWTService();
        $actualPublicKey = $sut->getPublicKeyByJWK($this->jwtHeadersClaimSignature, $this->jwks);

        $expectedPublicKey = <<<KEY
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA10dGTg473Av9lRp/jhvW
Io7oG8qm/FTOj+YpieNScOkCZsgWuSLuYzElBRpDAAq6zMr1SwXYSGSbPzAoYd0U
9rWLO3AKuVHoZbwd5RjKen+l5lVOWmF2da6vnPyOxwKowA3dPhGsSPOXCU7TitKH
Gz7fJCDMMdbxxZMdX2qfIpWN9n90gyjOQYqilQtJLnBDNYtYNhEU6o/fsVkOdspP
/gJIQE++NpXW9udaQ8mjIhuFfa/b8ucp/puJXtgeNNGiJ4ebwE0hNLBDhCLeXGSl
vGvjf9P3c1oIR9z5i/12h8X7pQ2nxBT1d4shzWaFc07OIzssAwYDu4c5M41ilcAi
CQIDAQAB
-----END PUBLIC KEY-----

KEY;

        self::assertSame($expectedPublicKey, $actualPublicKey);
    }
}
