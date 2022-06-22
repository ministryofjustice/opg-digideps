<?php

declare(strict_types=1);

namespace App\Service\JWT;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

class JWTService
{
    public function getJWTHeaders(string $jwt): array|null
    {
        return self::base64DecodeJWT($jwt)['headers'];
    }

    public function getJWTClaims(string $jwt): array|null
    {
        return self::base64DecodeJWT($jwt)['claims'];
    }

    public function getJWTSignature(string $jwt): array|null
    {
        return self::base64DecodeJWT($jwt)['signature'];
    }

    public function decodeAndVerifyWithKey(string $jwt, array $jwks): array
    {
        $keys = JWK::parseKeySet($jwks);

        return (array) JWT::decode($jwt, $keys);
    }

    public static function base64EncodeJWT(array $headers, array $claims, ?array $signature = null)
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

    public static function base64DecodeJWT(string $jwt)
    {
        1 === substr_count($jwt, '.') ?
            [$headers, $claims] = explode('.', $jwt) :
            [$headers, $claims, $signature] = explode('.', $jwt);

        $decoded = [
            'headers' => json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $headers)), true),
            'claims' => json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $claims)), true),
        ];

        if (isset($signature)) {
            $decoded['signature'] = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $signature)), true);
        }

        return $decoded;
    }
}
