<?php

declare(strict_types=1);

use App\Service\JWT\JWTService;
use PHPUnit\Framework\TestCase;

/**
 * @property string[] $jwtHeaders
 * @property array    $jwtClaims
 * @property string[] $jwtSignature
 * @property string   $jwtHeadersClaim
 * @property string   $jwtHeadersClaimSignature
 */
class JWTServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->jwtHeaders = [
            'jku' => 'https://digideps.local/v2/.well-known/jwks.json',
            'typ' => 'JWT',
            'alg' => 'RS256',
            'kid' => '45ed51b79f00b11d47100b9cc7092ef2819da72df0fc0be8f89824a779973bc0',
        ];

        $this->jwtClaims = [
            'aud' => 'registration_service',
            'iat' => 1655914720,
            'exp' => 1655918320,
            'nbf' => 1655914710,
            'iss' => 'digideps',
            'sub' => 1,
            'role' => 'ROLE_SUPER_ADMIN',
        ];

        $this->jwtSignature = ['a signature'];

        $this->jwtHeadersClaim = <<<JWT
eyJqa3UiOiJodHRwczpcL1wvZGlnaWRlcHMubG9jYWxcL3YyXC8ud2VsbC1rbm93blwvandrcy5qc29uIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYiLCJraWQiOiI0NWVkNTFiNzlmMDBiMTFkNDcxMDBiOWNjNzA5MmVmMjgxOWRhNzJkZjBmYzBiZThmODk4MjRhNzc5OTczYmMwIn0.eyJhdWQiOiJyZWdpc3RyYXRpb25fc2VydmljZSIsImlhdCI6MTY1NTkxNDcyMCwiZXhwIjoxNjU1OTE4MzIwLCJuYmYiOjE2NTU5MTQ3MTAsImlzcyI6ImRpZ2lkZXBzIiwic3ViIjoxLCJyb2xlIjoiUk9MRV9TVVBFUl9BRE1JTiJ9
JWT;

        $this->jwtHeadersClaimSignature = <<<JWT
eyJqa3UiOiJodHRwczpcL1wvZGlnaWRlcHMubG9jYWxcL3YyXC8ud2VsbC1rbm93blwvandrcy5qc29uIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYiLCJraWQiOiI0NWVkNTFiNzlmMDBiMTFkNDcxMDBiOWNjNzA5MmVmMjgxOWRhNzJkZjBmYzBiZThmODk4MjRhNzc5OTczYmMwIn0.eyJhdWQiOiJyZWdpc3RyYXRpb25fc2VydmljZSIsImlhdCI6MTY1NTkxNDcyMCwiZXhwIjoxNjU1OTE4MzIwLCJuYmYiOjE2NTU5MTQ3MTAsImlzcyI6ImRpZ2lkZXBzIiwic3ViIjoxLCJyb2xlIjoiUk9MRV9TVVBFUl9BRE1JTiJ9.WyJhIHNpZ25hdHVyZSJd
JWT;
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
    public function base64DecodeJWTMissingSignature()
    {
        $expectedDecodedJWT = ['headers' => $this->jwtHeaders, 'claims' => $this->jwtClaims];
        self::assertSame($expectedDecodedJWT, JWTService::base64DecodeJWT($this->jwtHeadersClaim));
    }

    /** @test */
    public function base64DecodeJWTWithSignature()
    {
        $expectedDecodedJWT = ['headers' => $this->jwtHeaders, 'claims' => $this->jwtClaims, 'signature' => $this->jwtSignature];
        self::assertSame($expectedDecodedJWT, JWTService::base64DecodeJWT($this->jwtHeadersClaimSignature));
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

        self::assertSame($this->jwtClaims, $actualHeaders);
    }

    /** @test */
    public function getJWTSignature()
    {
        $sut = new JWTService();
        $actualHeaders = $sut->getJWTSignature($this->jwtHeadersClaimSignature);

        self::assertSame($this->jwtSignature, $actualHeaders);
    }
}
